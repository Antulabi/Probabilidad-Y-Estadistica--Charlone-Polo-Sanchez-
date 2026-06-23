<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\CalculadoraEstadistica;

// Datos de nicotina del Ejemplo 1.1.6 del PDF del Profesor Meana
// n = 40 datos
$datosNicotina = [
    1.09, 1.92, 2.31, 1.79, 2.28, 1.74, 1.47, 1.97,
    0.85, 1.24, 1.58, 2.03, 1.70, 2.17, 2.55, 2.11,
    1.86, 1.90, 1.68, 1.51, 1.64, 0.72, 1.69, 1.85,
    1.82, 1.79, 2.46, 1.88, 2.08, 1.67, 1.37, 1.93,
    1.40, 1.64, 2.09, 1.75, 1.63, 2.37, 1.75, 1.69
];

try {
    $calculadora = new CalculadoraEstadistica($datosNicotina);
    $cuartiles = $calculadora->cuartiles();

    echo "=== VERIFICACIÓN DE CUARTILES NO AGRUPADOS ===\n";
    echo "Total Datos: " . $cuartiles['cantidad'] . "\n";
    echo "Q1 (Mediana mitad inferior): " . $cuartiles['q1'] . "\n";
    echo "Q3 (Mediana mitad superior): " . $cuartiles['q3'] . "\n";
    echo "Rango Intercuartil (RIC): " . $cuartiles['ric'] . "\n";
    
    echo "\nDetalles Q1:\n";
    echo " - Posición descriptiva: " . $cuartiles['q1_details']['pos'] . "\n";
    echo " - Valores de la mitad inferior: [" . implode(", ", $cuartiles['q1_details']['valores']) . "]\n";
    
    echo "\nDetalles Q3:\n";
    echo " - Posición descriptiva: " . $cuartiles['q3_details']['pos'] . "\n";
    echo " - Valores de la mitad superior: [" . implode(", ", $cuartiles['q3_details']['valores']) . "]\n";

    // Validar el RIC esperado de 0.365
    if (abs($cuartiles['ric'] - 0.365) < 0.0001) {
        echo "\n[OK] ¡El RIC coincide perfectamente con el 0.365 del apunte del Profesor Meana!\n";
    } else {
        echo "\n[ERROR] El RIC calculado (" . $cuartiles['ric'] . ") difiere de 0.365.\n";
    }

    echo "\n=== PRUEBA DE TAMAÑO IMPAR (n = 7) ===\n";
    // Muestra impar ordinaria: [4, 5, 6, 7, 7, 8, 9] (mediana es 7 en posicion 4)
    // Mitad inferior: [4, 5, 6] -> mediana es 5
    // Mitad superior: [7, 8, 9] -> mediana es 8
    $datosImpar = [4, 5, 6, 7, 7, 8, 9];
    $calcImpar = new CalculadoraEstadistica($datosImpar);
    $qImp = $calcImpar->cuartiles();
    echo "Q1 (esperado: 5.0): " . $qImp['q1'] . "\n";
    echo "Q3 (esperado: 8.0): " . $qImp['q3'] . "\n";
    echo "RIC (esperado: 3.0): " . $qImp['ric'] . "\n";
    echo "Mitad Inferior: [" . implode(", ", $qImp['q1_details']['valores']) . "]\n";
    echo "Mitad Superior: [" . implode(", ", $qImp['q3_details']['valores']) . "]\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
