<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

require_once __DIR__ . '/../vendor/autoload.php';

use App\LectorExcel;
use App\CalculadoraEstadistica;

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido.");
    }

    if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error al subir el archivo o no se seleccionó ningún archivo.");
    }

    $rutaTemporal = $_FILES['archivo_excel']['tmp_name'];
    $nombreArchivo = $_FILES['archivo_excel']['name'];

    // Instanciar lector
    $lector = new LectorExcel($rutaTemporal);
    $lector->cargar();

    $pestanas = $lector->obtenerPestanas();
    
    // Determinar la pestaña seleccionada (por defecto la primera)
    $pestanaSeleccionada = $_POST['pestana'] ?? $pestanas[0];
    
    // Obtener los datos de la pestaña
    $datosMatriz = $lector->obtenerDatosPestana($pestanaSeleccionada);
    
    // Extraer TODOS los datos numéricos de todas las celdas de la pestaña
    $datosSerie = [];
    foreach ($datosMatriz as $fila) {
        foreach ($fila as $val) {
            if ($val !== null && is_numeric($val)) {
                $datosSerie[] = floatval($val);
            }
        }
    }

    if (count($datosSerie) === 0) {
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

    // Calcular tendencia central agrupada
    $k = $calculadora->calcularIntervalosSturges();
    $tablaAgrupada = $calculadora->tablaFrecuenciasAgrupada($k);
    $mediaAgrupada = $calculadora->mediaAgrupada($tablaAgrupada);
    $medianaAgrupadaInfo = $calculadora->medianaAgrupada($tablaAgrupada, $k);
    $intervaloModalInfo = $calculadora->intervaloModal($tablaAgrupada);
    $varianzaAgrupadaInfo = $calculadora->varianzaAgrupada($tablaAgrupada);
    $desviacionAgrupada = $calculadora->desviacionEstandarAgrupada($tablaAgrupada);
    $cvAgrupada = $calculadora->coeficienteVariacionAgrupada($tablaAgrupada);
    $cuartilesAgrupadosInfo = $calculadora->cuartilesAgrupados($tablaAgrupada, $k);

    // Estructurar respuesta JSON
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

} catch (Exception $e) {
    echo json_encode([
        'exito' => false,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
