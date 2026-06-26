<?php
// Error reporting
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
// Muestra los errores
ini_set('display_errors', 1);
// Se incluye el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Se incluyen las clases necesarias
use App\LectorExcel;
use App\CalculadoraEstadistica;
// Se define el header como JSON
header('Content-Type: application/json; charset=utf-8');

// Se inicia el bloque try para manejar posibles excepciones
try {
    // Se verifica que el método de la solicitud sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        // Si no es POST, se lanza una excepción
        throw new Exception("Método no permitido.");
    }
    // Se verifica que se haya subido un archivo
    if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
        // Si no se subió el archivo, se lanza una excepción
        throw new Exception("Error al subir el archivo o no se seleccionó ningún archivo.");
    }
    // Se obtiene la ruta temporal del archivo
    $rutaTemporal = $_FILES['archivo_excel']['tmp_name'];
    // Se obtiene el nombre del archivo
    $nombreArchivo = $_FILES['archivo_excel']['name'];

    // Instanciar lector
    $lector = new LectorExcel($rutaTemporal);
    // Carga el archivo de Excel
    $lector->cargar();
    // Obtiene las pestañas del archivo
    $pestanas = $lector->obtenerPestanas();
    // Determinar la pestaña seleccionada (por defecto la primera)
    $pestanaSeleccionada = $_POST['pestana'] ?? $pestanas[0];
    // Obtiene los datos de la pestaña seleccionada
    $datosMatriz = $lector->obtenerDatosPestana($pestanaSeleccionada);
    // Extrae TODOS los datos numéricos de todas las celdas de la pestaña
    $datosSerie = [];
    // Recorre todas las filas de la matriz
    foreach ($datosMatriz as $fila) {
        // Recorre todas las columnas de la matriz
        foreach ($fila as $val) {
            // Si el valor no es nulo y es numérico, se agrega a la serie
            if ($val !== null && is_numeric($val)) {
                // Convierte el valor a número flotante
                $datosSerie[] = floatval($val);
            }
        }
    }
    // Se verifica que la serie contenga datos numéricos
    if (count($datosSerie) === 0) {
        // Si no contiene datos numéricos, se lanza una excepción
        throw new Exception("La pestaña seleccionada no contiene datos numéricos válidos.");
    }

    // Instanciar calculadora
    $calculadora = new CalculadoraEstadistica($datosSerie);

    // Calcular tendencia central no agrupada
    $media = $calculadora->media();
    $mediana = $calculadora->mediana();
    $moda = $calculadora->moda();
    $tablaFrecuencias = $calculadora->tablaFrecuencias();
    $varianzaInfo = $calculadora->varianza();
    $desviacion = $calculadora->desviacionEstandar();
    $cv = $calculadora->coeficienteVariacion();
    $cuartilesInfo = $calculadora->cuartiles();
    
    // Obtiene los parámetros opcionales forzados
    $kForzado = isset($_POST['k_intervalos']) && is_numeric($_POST['k_intervalos']) ? (int)$_POST['k_intervalos'] : null;
    $amplitudForzada = isset($_POST['amplitud_clase']) && is_numeric($_POST['amplitud_clase']) ? (float)$_POST['amplitud_clase'] : null;

    // Si el número de intervalos es menor a 1, se anula
    if ($kForzado !== null && $kForzado < 1) {
        $kForzado = null;
    }
    // Si la amplitud es menor o igual a 0, se anula
    if ($amplitudForzada !== null && $amplitudForzada <= 0) {
        $amplitudForzada = null;
    }

    // Calcula el rango
    $min = $calculadora->obtenerDatosOrdenados()[0];
    $max = $calculadora->obtenerDatosOrdenados()[count($datosSerie) - 1];
    $rango = $max - $min;

    // Calcula el número de intervalos
    $k = $kForzado ?? $calculadora->calcularIntervalosSturges();

    if ($amplitudForzada !== null && $kForzado === null) {
        $k = (int) ceil($rango / $amplitudForzada);
        if ($k < 1) $k = 1;
        // Si el máximo coincide con el límite superior de k clases, asegurar cobertura
        if ($min + $k * $amplitudForzada < $max) {
            $k++;
        }
    }
    // Calcula la tabla de frecuencias agrupada
    $tablaAgrupada = $calculadora->tablaFrecuenciasAgrupada($k, $amplitudForzada);
    // Calcula la media agrupada
    $mediaAgrupada = $calculadora->mediaAgrupada($tablaAgrupada);
    // Calcula la mediana agrupada
    $medianaAgrupadaInfo = $calculadora->medianaAgrupada($tablaAgrupada, $k, $amplitudForzada);
    // Calcula el intervalo modal
    $intervaloModalInfo = $calculadora->intervaloModal($tablaAgrupada);
    // Calcula la varianza agrupada
    $varianzaAgrupadaInfo = $calculadora->varianzaAgrupada($tablaAgrupada);
    // Calcula la desviación estándar agrupada
    $desviacionAgrupada = $calculadora->desviacionEstandarAgrupada($tablaAgrupada);
    // Calcula el coeficiente de variación agrupada
    $cvAgrupada = $calculadora->coeficienteVariacionAgrupada($tablaAgrupada);
    // Calcula los cuartiles agrupados
    $cuartilesAgrupadosInfo = $calculadora->cuartilesAgrupados($tablaAgrupada, $k, $amplitudForzada);

    // Toda la informacion estructurada en un gran array asociativo en PHP mediante json_encode() retorna el array a formato JSON 
    echo json_encode([
        'exito' => true,
        'nombre_archivo' => $nombreArchivo,
        'pestanas' => $pestanas,
        'pestana_seleccionada' => $pestanaSeleccionada,
        'planilla' => $datosMatriz,
        'no_agrupados' => [
            'cantidad' => $calculadora->obtenerCantidad(),
            'media' => $media,
            'mediana' => $mediana,
            'moda' => $moda,
            'tabla' => $tablaFrecuencias,
            'varianza' => $varianzaInfo['resultado'],
            'varianza_detalle' => $varianzaInfo,
            'desviacion' => $desviacion,
            'cv' => $cv,
            'cuartiles' => $cuartilesInfo
        ],
        'agrupados' => [
            'k' => count($tablaAgrupada),
            'amplitud' => $medianaAgrupadaInfo['amplitud'],
            'tabla' => $tablaAgrupada,
            'media' => $mediaAgrupada,
            'mediana' => $medianaAgrupadaInfo['valor'],
            'mediana_detalle' => $medianaAgrupadaInfo,
            'intervalo_modal' => $intervaloModalInfo,
            'varianza' => $varianzaAgrupadaInfo['resultado'],
            'varianza_detalle' => $varianzaAgrupadaInfo,
            'desviacion' => $desviacionAgrupada,
            'cv' => $cvAgrupada,
            'cuartiles' => $cuartilesAgrupadosInfo
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    // Si ocurre una excepción, se captura y se muestra un mensaje de error
} catch (Exception $e) {
    echo json_encode([
        'exito' => false,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
