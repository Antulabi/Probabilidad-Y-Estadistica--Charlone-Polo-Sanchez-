<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\LectorExcel;
use App\CalculadoraEstadistica;

try {
    $archivo = __DIR__ . '/../DatosCatedra.xlsx';
    if (!file_exists($archivo)) {
        throw new Exception("El archivo de prueba no se encuentra en: $archivo");
    }

    echo "Cargando archivo: $archivo...\n";
    $lector = new LectorExcel($archivo);
    $lector->cargar();

    $pestanas = $lector->obtenerPestanas();
    echo "Pestañas encontradas: " . implode(", ", $pestanas) . "\n";

    $pestanaSeleccionada = $pestanas[0];
    echo "Procesando pestaña: $pestanaSeleccionada...\n";
    $matriz = $lector->obtenerDatosPestana($pestanaSeleccionada);
    echo "Total de filas leídas: " . count($matriz) . "\n";

    // Obtener la primera columna con números
    $columna = null;
    $columnas = array_keys(current($matriz));
    foreach ($columnas as $col) {
        $valores = array_column($matriz, $col);
        $numericos = array_filter($valores, 'is_numeric');
        if (count($numericos) > 0) {
            $columna = $col;
            break;
        }
    }

    if ($columna === null) {
        throw new Exception("No se encontró ninguna columna numérica.");
    }

    echo "Columna numérica identificada: $columna\n";

    // Extraer datos
    $datos = [];
    foreach ($matriz as $fila) {
        if (isset($fila[$columna]) && is_numeric($fila[$columna])) {
            $datos[] = floatval($fila[$columna]);
        }
    }

    echo "Total de datos numéricos extraídos: " . count($datos) . "\n";
    echo "Primeros 10 datos: " . implode(", ", array_slice($datos, 0, 10)) . "\n";

    // Iniciar calculadora
    $calculadora = new CalculadoraEstadistica($datos);

    echo "\n=== DATOS NO AGRUPADOS ===\n";
    echo "Media: " . $calculadora->media() . "\n";
    echo "Mediana: " . $calculadora->mediana() . "\n";
    echo "Moda: " . implode(", ", $calculadora->moda()) . "\n";

    echo "\n=== DATOS AGRUPADOS ===\n";
    $k = $calculadora->calcularIntervalosSturges();
    echo "Intervalos según Sturges (k): $k\n";
    $tabla = $calculadora->tablaFrecuenciasAgrupada($k);
    
    echo "Tabla de frecuencias agrupadas:\n";
    echo "Intervalo | Lim_Inf | Lim_Sup | Marca (x_ic) | Frec Abs (f_i) | Frec Acum (F_i)\n";
    foreach ($tabla as $fila) {
        printf("   %2d     | %7.2f | %7.2f | %12.2f | %13d | %14d\n", 
            $fila['intervalo'], 
            $fila['lim_inf'], 
            $fila['lim_sup'], 
            $fila['x_ic'], 
            $fila['f_i'], 
            $fila['F_i']
        );
    }

    echo "Media Agrupada: " . $calculadora->mediaAgrupada($tabla) . "\n";
    $medianaAgrupada = $calculadora->medianaAgrupada($tabla, $k);
    echo "Mediana Agrupada: " . $medianaAgrupada['valor'] . "\n";
    echo "Detalle Mediana Agrupada: L_i = " . $medianaAgrupada['lim_inf'] . ", F_anterior = " . $medianaAgrupada['F_anterior'] . ", f_i = " . $medianaAgrupada['f_i'] . ", a = " . $medianaAgrupada['amplitud'] . "\n";
    
    $modal = $calculadora->intervaloModal($tabla);
    echo "Frecuencia máxima del intervalo modal: " . $modal['frecuencia_maxima'] . "\n";
    echo "Intervalos modales: \n";
    foreach ($modal['intervalos'] as $int) {
        echo " - Intervalo " . $int['intervalo'] . ": [" . $int['lim_inf'] . " - " . $int['lim_sup'] . ") con f_i = " . $int['f_i'] . "\n";
    }

    echo "\n¡Prueba completada con éxito!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
