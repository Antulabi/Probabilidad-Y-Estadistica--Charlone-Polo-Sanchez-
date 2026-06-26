<?php

namespace App;
// se incluye Exception para manejar errores
use Exception;

/**
 * Clase encargada de realizar todos los cálculos estadísticos requeridos
 * según el apunte del profe Meana (TECDA 2026).
 */
class CalculadoraEstadistica
{
    private $datos;
    private $datosOrdenados;
    private $cantidad;
    private $min;
    private $max;
    private $rango;
    private $soloEnteros;

    public function __construct(array $datos)
    {
        // Filtrar solo los datos numéricos y limpiar el array
        $this->datos = array_values(array_filter($datos, 'is_numeric'));
        $this->cantidad = count($this->datos);

        if ($this->cantidad === 0) {
            throw new Exception("El conjunto de datos no contiene valores numéricos válidos.");
        }

        // Ordenar datos de menor a mayor
        $this->datosOrdenados = $this->datos;
        sort($this->datosOrdenados);
        // Minimo es el primer elemento del array ordenado
        $this->min = $this->datosOrdenados[0];
        // Maximo es el ultimo elemento del array ordenado
        $this->max = $this->datosOrdenados[$this->cantidad - 1];
        // Rango es la diferencia entre el maximo y el minimo
        $this->rango = $this->max - $this->min;

        // Determinar si todos los datos son enteros
        $this->soloEnteros = true;
        // Recorremos el array ordenado para verificar si todos los datos son enteros
        foreach ($this->datosOrdenados as $dato) {
            // Si floor($dato) es diferente a $dato, entonces es un decimal
            if (floor($dato) != $dato) {
                $this->soloEnteros = false;
                break;
            }
        }
    }
    // Getter para obtener los datos
    public function obtenerDatos(): array
    {
        // Retorna los datos
        return $this->datos;
    }
    // Getter para obtener los datos ordenados
    public function obtenerDatosOrdenados(): array
    {
        // Retorna los datos ordenados
        return $this->datosOrdenados;
    }
    // Getter para obtener la cantidad de datos
    public function obtenerCantidad(): int
    {
        // Retorna la cantidad de datos
        return $this->cantidad;
    }
    // Getter para obtener el minimo
    public function obtenerMinimo(): float
    {
        return $this->min;
    }
    // Getter para obtener el maximo
    public function obtenerMaximo(): float
    {
        return $this->max;
    }
    // Getter para obtener el rango
    public function obtenerRango(): float
    {
        return $this->rango;
    }
    // Getter para obtener si los datos son enteros
    public function obtenerSoloEnteros(): bool
    {
        return $this->soloEnteros;
    }

    /* =========================================================================
       SECCIÓN 1: DATOS NO AGRUPADOS (Punto A)
       ========================================================================= */

    /**
     * Calcula la media aritmética para datos no agrupados.
     * Fórmula: \bar{x} = \frac{\sum x_i}{n}
     */
    public function media(): float
    {
        // Media aritmética: suma de todas las observaciones dividida por el tamaño de la muestra (n)
        return array_sum($this->datos) / $this->cantidad;
    }

    /**
     * Calcula la mediana para datos no agrupados.
     * Fórmula:
     * - Impar: Me = X_{(n+1)/2}
     * - Par: Me = (X_{n/2} + X_{n/2 + 1}) / 2
     */
    public function mediana(): float
    {
        if ($this->cantidad % 2 !== 0) {
            // Si la muestra es impar, la mediana es el dato central: posición (n-1)/2
            $posicion = ($this->cantidad - 1) / 2;
            return $this->datosOrdenados[$posicion];
        } else {
            // Si es par, es el promedio de las dos posiciones centrales: n/2 y (n/2)-1
            $posCentral1 = ($this->cantidad / 2) - 1;
            $posCentral2 = $this->cantidad / 2;
            return ($this->datosOrdenados[$posCentral1] + $this->datosOrdenados[$posCentral2]) / 2;
        }
    }

    /**
     * Calcula la moda para datos no agrupados.
     * Retorna un array con el valor o valores modales (M0).
     */
    public function moda(): array
    {
        // Cuenta la frecuencia de cada valor en el array de datos
        $frecuencias = array_count_values(array_map('strval', $this->datos));
        // Obtiene la frecuencia maxima
        $maxFrecuencia = max($frecuencias);

        // Si todos los datos se repiten el mismo número de veces (ej: frecuencia = 1), no hay moda
        if ($maxFrecuencia === 1 && count($frecuencias) > 1) {
            return [];
        }
        // Recorre las frecuencias y obtiene los valores con la frecuencia maxima
        $modas = [];
        foreach ($frecuencias as $valor => $frecuencia) {
            if ($frecuencia === $maxFrecuencia) {
                $modas[] = floatval($valor);
            }
        }
        // Ordena los modas de menor a mayor
        sort($modas);
        return $modas;
    }

    /**
     * Genera la Tabla de Distribución de Frecuencias para datos no agrupados.
     */
    public function tablaFrecuencias(): array
    {
        // Cuenta la frecuencia de cada valor en el array de datos
        $frecuencias = array_count_values(array_map('strval', $this->datosOrdenados));
        // Ordenar por el valor del dato
        ksort($frecuencias);
        
        $tabla = [];
        // Inicializa la acumulada absoluta
        $acumuladaAbsoluta = 0;
        // Obtiene la cantidad de datos
        $n = $this->cantidad;
        // Recorre las frecuencias y crea la tabla de distribución de frecuencias
        foreach ($frecuencias as $dato => $f_i) {
            // Suma la frecuencia absoluta
            $acumuladaAbsoluta += $f_i;
            // Calcula la frecuencia relativa
            $f_r = $f_i / $n;
            // Calcula la frecuencia acumulada relativa
            $F_r = $acumuladaAbsoluta / $n;
            // Agrega la fila a la tabla
            $tabla[] = [
                'dato' => floatval($dato),
                'f_i'  => $f_i,                       // Frecuencia absoluta
                'f_r'  => $f_r,                       // Frecuencia relativa
                'F_i'  => $acumuladaAbsoluta,          // Frecuencia acumulada absoluta
                'F_r'  => $F_r,                       // Frecuencia acumulada relativa
                'porcentaje' => $f_r * 100            // Porcentaje
            ];
        }

        return $tabla;
    }

    /* =========================================================================
       SECCIÓN 2: DATOS AGRUPADOS (Punto A - Sturges e Intervalos)
       ========================================================================= */

    /**
     * Calcula el número de intervalos (k) usando la regla de Sturges.
     * Fórmula: k = 1 + 3.322 * log10(n)
     */
    public function calcularIntervalosSturges(): int
    {
        // Fórmula de Sturges: k = 1 + 3.322 * log10(n) para aproximar el número de intervalos de clase
        return (int) round(1 + 3.322 * log10($this->cantidad));
    }

    /**
     * Genera los intervalos de clase y la tabla de frecuencias agrupada.
     */
    public function tablaFrecuenciasAgrupada(?int $k = null, ?float $amplitudForzada = null): array
    {
        if ($k === null) {
            $k = $this->calcularIntervalosSturges();
        }

        // Amplitud de clase (a)
        // Si se proporciona una amplitud de clase, se usa esa
        if ($amplitudForzada !== null) {
            $a = $amplitudForzada;
        // Si los datos son enteros, se usa la regla de Sturges para calcular la amplitud de clase
        } elseif ($this->soloEnteros) {
            // Amplitud de clase redondeada al entero más cercano
            $a = (int) round($this->rango / $k);
            // Si la amplitud de clase es menor a 1, se usa 1
            if ($a < 1) $a = 1;

            // Ajustar k dinámicamente si es necesario para cubrir el máximo
            while ($this->min + $k * $a < $this->max) {
                $k++;
            }
        } else {
            // Para datos no enteros, la amplitud se calcula como rango/k
            $a = $this->rango / $k;
        }
        // Inicializa la tabla de frecuencias
        $tabla = [];
        // Inicializa la acumulada absoluta
        $acumuladaAbsoluta = 0;
        // Obtiene la cantidad de datos
        $n = $this->cantidad;
        // Recorre los intervalos y crea la tabla de frecuencias
        for ($i = 0; $i < $k; $i++) {
            $limiteNominalInferior = $this->min + ($i * $a);
            $limiteNominalSuperior = $this->min + (($i + 1) * $a);

            // En el esquema continuo/abierto, los límites reales coinciden con los nominales
            $limiteRealInferior = $limiteNominalInferior;
            $limiteRealSuperior = $limiteNominalSuperior;

            // Marca de clase (x_ic): Punto medio del intervalo
            $x_ic = ($limiteNominalInferior + $limiteNominalSuperior) / 2;

            // Contar frecuencia absoluta (f_i) en el intervalo [L_inf, L_sup)
            // Para el último intervalo, se incluye el límite superior [L_inf, L_sup]
            $f_i = 0;
            // Recorre los datos y cuenta la frecuencia absoluta
            foreach ($this->datosOrdenados as $dato) {
                // Si es el último intervalo, se incluye el límite superior
                if ($i === $k - 1) {
                    // Si el dato está en el intervalo, se incrementa la frecuencia absoluta
                    if ($dato >= $limiteNominalInferior && $dato <= $limiteNominalSuperior) {
                        $f_i++;
                    }
                } else {
                    // Si el dato está en el intervalo, se incrementa la frecuencia absoluta
                    if ($dato >= $limiteNominalInferior && $dato < $limiteNominalSuperior) {
                        $f_i++;
                    }
                }
            }
            // Suma la frecuencia absoluta
            $acumuladaAbsoluta += $f_i;
            // Calcula la frecuencia relativa
            $f_r = $f_i / $n;
            // Calcula la frecuencia acumulada relativa
            $F_r = $acumuladaAbsoluta / $n;
            // Agrega la fila a la tabla
            $tabla[] = [
                // Intervalo
                'intervalo' => $i + 1,
                // Límites nominales
                'lim_inf'   => $limiteNominalInferior,
                'lim_sup'   => $limiteNominalSuperior,
                // Límites reales
                'lim_real_inf' => $limiteRealInferior,
                'lim_real_sup' => $limiteRealSuperior,
                // Marca de clase
                'x_ic'      => $x_ic,
                'f_i'       => $f_i,
                'f_r'       => $f_r,
                'F_i'       => $acumuladaAbsoluta,
                'F_r'       => $F_r,
                'porcentaje'=> $f_r * 100,
                'amplitud'  => $a
            ];
        }
        // Retorna la tabla de frecuencias agrupada
        return $tabla;
    }

    /**
     * Calcula la media agrupada.
     * Fórmula: \bar{x} = \frac{\sum x_{ic} \cdot f_i}{n}
     */
    public function mediaAgrupada(?array $tabla = null): float
    {
        // Si no se proporciona una tabla, se genera una
        $tabla = $tabla ?? $this->tablaFrecuenciasAgrupada();
        $sumaProductos = 0;
        // Recorre la tabla y suma los productos de la marca de clase y su frecuencia absoluta
        foreach ($tabla as $fila) {
            // Producto de la marca de clase (x_ic) y su frecuencia absoluta (f_i): x_ic * f_i
            $sumaProductos += $fila['x_ic'] * $fila['f_i'];
        }
        // Media agrupada = Sumatoria de (x_ic * f_i) / n
        return $sumaProductos / $this->cantidad;
    }

    /**
     * Calcula la mediana agrupada por interpolación lineal.
     * Fórmula: Me = L_i + ( (n/2 - F_{i-1}) / f_i ) * a
     */
    public function medianaAgrupada(?array $tabla = null, ?int $k = null, ?float $amplitudForzada = null): array
    {
        // Si no se proporciona una tabla, se genera una
        $tabla = $tabla ?? $this->tablaFrecuenciasAgrupada($k, $amplitudForzada);
        // Si se proporciona una amplitud de clase, se usa esa
        if ($amplitudForzada !== null) {
            $a = $amplitudForzada;
        } else {
            // Amplitud de clase (a): diferencia entre el límite superior y el inferior del intervalo
            $a = $tabla[0]['lim_sup'] - $tabla[0]['lim_inf'];
        }
        
        // n / 2: mitad de los datos para ubicar el intervalo de la mediana
        $n_medios = $this->cantidad / 2;
        
        // Buscar el primer intervalo donde la frecuencia acumulada F_i >= n/2
        $indiceIntervaloMediana = -1;
        // Recorre la tabla y busca el primer intervalo donde la frecuencia acumulada F_i >= n/2
        foreach ($tabla as $index => $fila) {
            // Si la frecuencia acumulada es mayor o igual a la mitad de los datos
            if ($fila['F_i'] >= $n_medios) {
                // Se guarda el índice del intervalo de la mediana
                $indiceIntervaloMediana = $index;
                // Se rompe el ciclo
                break;
            }
        }
        // Si no se encuentra (caso de error en frecuencias), usar el último intervalo
        if ($indiceIntervaloMediana === -1) {
            // Se asigna el índice del último intervalo
            $indiceIntervaloMediana = count($tabla) - 1;
        }
        // Se obtiene la fila de la clase mediana
        $filaMediana = $tabla[$indiceIntervaloMediana];
        // Límite real inferior de la clase mediana
        $L_i = $filaMediana['lim_real_inf']; 
        // Frecuencia acumulada anterior F_{i-1}
        $F_anterior = 0;
        // Si el índice del intervalo de la mediana es mayor a 0
        if ($indiceIntervaloMediana > 0) {
            $F_anterior = $tabla[$indiceIntervaloMediana - 1]['F_i'];
        }

        $f_i = $filaMediana['f_i']; // Frecuencia absoluta de la clase mediana

        // Evitar división por cero si la frecuencia del intervalo es 0
        if ($f_i === 0) {
            // Si la frecuencia del intervalo es 0, la mediana es el límite inferior
            $mediana = $L_i;
        } else {
            // Fórmula: Me = L_i + (((n/2 - F_{i-1}) / f_i) * a)
            $mediana = $L_i + (($n_medios - $F_anterior) / $f_i) * $a;
        }
        // Retorna la mediana agrupada
        return [
            'valor' => $mediana,
            'intervalo' => $indiceIntervaloMediana + 1,
            'lim_inf' => $L_i,
            'F_anterior' => $F_anterior,
            'f_i' => $f_i,
            'amplitud' => $a
        ];
    }

    /**
     * Identifica el intervalo o intervalos modales (con mayor frecuencia f_i).
     */
    public function intervaloModal(?array $tabla = null): array
    {
        // Si no se proporciona una tabla, se genera una
        $tabla = $tabla ?? $this->tablaFrecuenciasAgrupada();
        
        // Buscar la máxima frecuencia absoluta
        $maxFrecuencia = 0;
        // Recorre la tabla y busca la máxima frecuencia absoluta
        foreach ($tabla as $fila) {
            // Si la frecuencia absoluta es mayor a la máxima frecuencia
            if ($fila['f_i'] > $maxFrecuencia) {
                // Se actualiza la máxima frecuencia
                $maxFrecuencia = $fila['f_i'];
            }
        }
        // Inicializa el array de intervalos modales
        $intervalosModales = [];
        // Recorre la tabla y busca los intervalos con la máxima frecuencia
        foreach ($tabla as $fila) {
            // Si la frecuencia absoluta es igual a la máxima frecuencia
            if ($fila['f_i'] === $maxFrecuencia) {
                // Se agrega el intervalo al array de intervalos modales
                $intervalosModales[] = $fila;
            }
        }
        // Retorna la frecuencia máxima y los intervalos modales
        return [
            'frecuencia_maxima' => $maxFrecuencia,
            'intervalos' => $intervalosModales
        ];
    }

    /* =========================================================================
       SECCIÓN 3: ESTRUCTURACIÓN PARA ESCALABILIDAD (Punto B)
       ========================================================================= */

    /**
     * Calcula la varianza muestral de datos no agrupados.
     * Fórmula: S^2 = \frac{\sum (x_i - \bar{x})^2}{n - 1}
     */
    public function varianza(): array
    {
        // Obtiene la cantidad de datos
        $n = $this->cantidad;
        // Calcula la media
        $media = $this->media();
        // Si la cantidad de datos es menor o igual a 1, retorna 0
        if ($n <= 1) {
            return [
                'resultado' => 0.0,
                'media' => $media,
                'cantidad' => $n,
                'suma_desviaciones' => 0.0,
                'desviaciones' => []
            ];
        }
        // Inicializa la suma de desviaciones
        $sumaDesviaciones = 0.0;
        // Inicializa el array de desviaciones
        $desviaciones = [];
        // Recorre los datos y calcula las desviaciones
        foreach ($this->datos as $x) {
            // Desviación del dato respecto a la media: (x_i - media)
            $diff = $x - $media;
            // Desviación al cuadrado: (x_i - media)^2
            $diffSq = $diff * $diff;
            // Se agrega el dato al array de desviaciones
            $desviaciones[] = [
                'valor' => $x,
                'diferencia' => $diff,
                'diferencia_cuadrado' => $diffSq
            ];
            // Sumatoria de desviaciones cuadráticas
            $sumaDesviaciones += $diffSq;
        }
        // Varianza muestral (S^2) = Sumatoria de (x_i - media)^2 / (n - 1)
        $varianza = $sumaDesviaciones / ($n - 1);
        // Retorna la varianza muestral
        return [
            'resultado' => $varianza,
            'media' => $media,
            'cantidad' => $n,
            'suma_desviaciones' => $sumaDesviaciones,
            'desviaciones' => $desviaciones
        ];
    }

    /**
     * Calcula la desviación estándar muestral de datos no agrupados.
     */
    public function desviacionEstandar(): float
    {
        // Desviación estándar muestral (S) = Raíz cuadrada de la varianza muestral (S^2)
        return sqrt($this->varianza()['resultado']);
    }

    /**
     * Calcula el coeficiente de variación de datos no agrupados.
     * CV = (S / \bar{x}) * 100
     */
    public function coeficienteVariacion(): float
    {
        $media = $this->media();
        if ($media == 0.0) {
            return 0.0;
        }
        // Coeficiente de variación (CV) = (Desviación Estándar / Media Aritmética) * 100
        return ($this->desviacionEstandar() / $media) * 100;
    }

    /**
     * Calcula la mediana de un conjunto de datos plano.
     */
    private function calcularMedianaArray(array $valores): float
    {
        // Cuenta la cantidad de datos
        $cant = count($valores);
        // Si la cantidad de datos es 0, retorna 0
        if ($cant === 0) {
            return 0.0;
        }
        // Si la cantidad de datos es impar
        if ($cant % 2 !== 0) {
            // Posición de la mediana
            $pos = ($cant - 1) / 2;
            // Retorna la mediana
            return $valores[$pos];
        } else {
            // Si la cantidad de datos es par
            $pos1 = ($cant / 2) - 1;
            $pos2 = $cant / 2;
            // Retorna la mediana
            return ($valores[$pos1] + $valores[$pos2]) / 2.0;
        }
    }

    /**
     * Calcula los cuartiles Q1 y Q3 para datos no agrupados usando el método de las mitades (Prof. Meana).
     * Q1 es la mediana de la mitad inferior de los datos o 25% , y Q3 es la mediana de la mitad superior o 75%
     * (excluyendo la mediana si n es impar).
     */
    public function cuartiles(): array
    {
        // Obtiene la cantidad de datos
        $n = $this->cantidad;
        
        // Si la cantidad de datos es par
        if ($n % 2 === 0) {
            // Caso N par: se divide la serie ordenada a la mitad exacta
            $mitadInferior = array_slice($this->datosOrdenados, 0, $n / 2);
            // Se divide la serie ordenada a la mitad exacta
            $mitadSuperior = array_slice($this->datosOrdenados, $n / 2);
            // Posición de Q1: mediana de la mitad inferior (primeros " . ($n / 2) . " datos)
            $q1_pos = "mediana de la mitad inferior (primeros " . ($n / 2) . " datos)";
            // Posición de Q3: mediana de la mitad superior (últimos " . ($n / 2) . " datos)
            $q3_pos = "mediana de la mitad superior (últimos " . ($n / 2) . " datos)";
        } else {
            // Caso N impar: se excluye el dato central de la mediana
            // Se divide la serie ordenada a la mitad exacta
            $mitadInferior = array_slice($this->datosOrdenados, 0, ($n - 1) / 2);
            // Se divide la serie ordenada a la mitad exacta
            $mitadSuperior = array_slice($this->datosOrdenados, ($n + 1) / 2);
            // Posición de Q1: mediana de la mitad inferior (primeros " . (($n - 1) / 2) . " datos, excluyendo el elemento central)
            $q1_pos = "mediana de la mitad inferior (primeros " . (($n - 1) / 2) . " datos, excluyendo el elemento central)";
            // Posición de Q3: mediana de la mitad superior (últimos " . (($n - 1) / 2) . " datos, excluyendo el elemento central)
            $q3_pos = "mediana de la mitad superior (últimos " . (($n - 1) / 2) . " datos, excluyendo el elemento central)";
        }

        // Calcula Q1
        $q1 = $this->calcularMedianaArray($mitadInferior);
        // Calcula Q3
        $q3 = $this->calcularMedianaArray($mitadSuperior);
        // Calcula RIC
        $ric = $q3 - $q1;

        // Retorna Q1, Q3, RIC, datos ordenados, cantidad, detalles de Q1 y detalles de Q3
        return [
            'q1' => $q1,
            'q3' => $q3,
            'ric' => $ric,
            'ordenados' => $this->datosOrdenados,
            'cantidad' => $n,
            'q1_details' => [
                'pos' => $q1_pos,
                'valores' => $mitadInferior,
                'resultado' => $q1
            ],
            'q3_details' => [
                'pos' => $q3_pos,
                'valores' => $mitadSuperior,
                'resultado' => $q3
            ]
        ];
    }

    /**
     * Calcula la varianza muestral agrupada en intervalos.
     * Fórmula: S^2 = \frac{\sum f_i \cdot (x_{ic} - \bar{x})^2}{n - 1}
     */
    public function varianzaAgrupada(?array $tabla = null): array
    {
        // Si no se proporciona tabla, usa la tabla de frecuencias agrupada
        $tabla = $tabla ?? $this->tablaFrecuenciasAgrupada();
        // Obtiene la cantidad de datos
        $n = $this->cantidad;
        // Obtiene la media agrupada
        $mediaAgrupada = $this->mediaAgrupada($tabla);
        // Si la cantidad de datos es menor o igual a 1
        if ($n <= 1) {
            // Retorna la varianza agrupada
            return [
                'resultado' => 0.0,
                'media' => $mediaAgrupada,
                'cantidad' => $n,
                'suma_desviaciones' => 0.0,
                'productos' => []
            ];
        }
        // Inicializa la suma de desviaciones
        $sumaDesviaciones = 0.0;
        // Inicializa el array de productos
        $productos = [];
        // Recorre la tabla de frecuencias agrupada
        foreach ($tabla as $fila) {
            // Obtiene la marca de clase
            $mc = $fila['x_ic'];
            // Obtiene la frecuencia absoluta
            $fi = $fila['f_i'];
            // Desviación de la marca de clase respecto a la media: (x_ic - mediaAgrupada)
            $diff = $mc - $mediaAgrupada;
            // Desviación al cuadrado: (x_ic - mediaAgrupada)^2
            $diffSq = $diff * $diff;
            // Desviación cuadrática ponderada por la frecuencia de la clase: f_i * (x_ic - mediaAgrupada)^2
            $prod = $fi * $diffSq;
            // Agrega la desviación cuadrática ponderada al array
            $productos[] = [
                'marca_clase' => $mc,
                'frecuencia' => $fi,
                'diferencia' => $diff,
                'diferencia_cuadrado' => $diffSq,
                'producto' => $prod
            ];
            // Sumatoria de desviaciones cuadráticas ponderadas
            $sumaDesviaciones += $prod;
        }

        // Varianza muestral agrupada (S^2) = Sumatoria de (f_i * (x_ic - mediaAgrupada)^2) / (n - 1)
        $varianza = $sumaDesviaciones / ($n - 1);

        // Retorna la varianza agrupada 
        return [
            'resultado' => $varianza,
            'media' => $mediaAgrupada,
            'cantidad' => $n,
            'suma_desviaciones' => $sumaDesviaciones,
            'productos' => $productos
        ];
    }

    /**
     * Calcula la desviación estándar muestral agrupada.
     */
    public function desviacionEstandarAgrupada(?array $tabla = null): float
    {
        // Desviación estándar agrupada (S) = Raíz cuadrada de la varianza muestral agrupada (S^2)
        return sqrt($this->varianzaAgrupada($tabla)['resultado']);
    }

    /**
     * Calcula el coeficiente de variación de datos agrupados.
     */
    public function coeficienteVariacionAgrupada(?array $tabla = null): float
    {
        // Calcula la media agrupada
        $media = $this->mediaAgrupada($tabla);
        // Si la media es 0
        if ($media == 0.0) {
            // Retorna 0
            return 0.0;
        }
        // Coeficiente de variación agrupado (CV) = (Desviación Estándar Agrupada / Media Agrupada) * 100
        return ($this->desviacionEstandarAgrupada($tabla) / $media) * 100;
    }

    /**
     * Calcula los cuartiles Q1 y Q3 para datos agrupados mediante interpolación lineal.
     */
    public function cuartilesAgrupados(?array $tabla = null, ?int $k = null, ?float $amplitudForzada = null): array
    {
        // Si no se proporciona tabla, usa la tabla de frecuencias agrupada
        $tabla = $tabla ?? $this->tablaFrecuenciasAgrupada($k, $amplitudForzada);
        // Obtiene la cantidad de datos
        $n = $this->cantidad;

        // Si no se proporciona amplitud, usa la amplitud de la tabla
        if ($amplitudForzada !== null) {
            // Si se proporciona amplitud, usarla
            $a = $amplitudForzada;
        } else {
            // Amplitud de clase (a): Límite superior - Límite inferior
            $a = $tabla[0]['lim_sup'] - $tabla[0]['lim_inf'];
        }

        // --- CÁLCULO DE Q1 ---
        // Posición de Q1 (25% de los datos) = n / 4
        $target1 = $n / 4.0;
        // Inicializa la clase de Q1
        $clase1 = null;
        // Inicializa la frecuencia acumulada del intervalo anterior
        $prevF1 = 0;

        // Buscar la clase que contiene a Q1 (primera clase con frecuencia acumulada F_i >= n/4)
        foreach ($tabla as $idx => $fila) {
            if ($fila['F_i'] >= $target1) {
                $clase1 = $fila;
                // Si hay clase anterior, obtener frecuencia acumulada
                if ($idx > 0) {
                    $prevF1 = $tabla[$idx - 1]['F_i']; // Frecuencia acumulada del intervalo anterior
                }
                break;
            }
        }
        if (!$clase1) {
            // Si no se encontró clase, usar la última
            $clase1 = end($tabla);
            // Obtiene la cantidad de clases
            $countInt = count($tabla);
            // Si hay más de una clase, obtener frecuencia acumulada anterior
            if ($countInt > 1) {
                $prevF1 = $tabla[$countInt - 2]['F_i'];
            }
        }

        $Li1 = $clase1['lim_real_inf']; // Límite real inferior de la clase de Q1
        $fi1 = $clase1['f_i'];           // Frecuencia absoluta de la clase de Q1
        // Interpolación para Q1 = L_i + (((n/4 - F_{i-1}) / f_i) * a)
        $q1_val = $fi1 === 0 ? $Li1 : $Li1 + (($target1 - $prevF1) / $fi1) * $a;

        // --- CÁLCULO DE Q3 ---
        // Posición de Q3 (75% de los datos) = 3n / 4
        $target3 = (3 * $n) / 4.0;
        // Inicializa la clase de Q3
        $clase3 = null;
        // Inicializa la frecuencia acumulada del intervalo anterior
        $prevF3 = 0;

        // Buscar la clase que contiene a Q3 (primera clase con frecuencia acumulada F_i >= 3n/4)
        foreach ($tabla as $idx => $fila) {
            if ($fila['F_i'] >= $target3) {
                // Si se encontró clase, establecerla
                $clase3 = $fila;
                // Si hay clase anterior, obtener frecuencia acumulada
                if ($idx > 0) {
                    $prevF3 = $tabla[$idx - 1]['F_i']; // Frecuencia acumulada del intervalo anterior
                }
                // Hacemos pedazo al bucle
                break;
            }
        }
        // Si no se encontró clase
        if (!$clase3) {
            // Si no se encontró clase, usar la última
            $clase3 = end($tabla);
            // Obtiene la cantidad de clases
            $countInt = count($tabla);
            // Si hay más de una clase, obtener frecuencia acumulada anterior
            if ($countInt > 1) {
                $prevF3 = $tabla[$countInt - 2]['F_i'];
            }
        }

        $Li3 = $clase3['lim_real_inf']; // Límite real inferior de la clase de Q3
        $fi3 = $clase3['f_i'];           // Frecuencia absoluta de la clase de Q3
        // Interpolación para Q3 = L_i + (((3n/4 - F_{i-1}) / f_i) * a)
        $q3_val = $fi3 === 0 ? $Li3 : $Li3 + (($target3 - $prevF3) / $fi3) * $a;

        // Rango intercuartílico (RIC) = Q3 - Q1
        $ric = $q3_val - $q1_val;
        // Retorna un array con los valores de Q1, Q3 y RIC
        return [
            // Retorna un array con los valores de Q1
            'q1' => [
                'resultado' => $q1_val,
                'limite_inferior' => $Li1,
                'frecuencia_acumulada_anterior' => $prevF1,
                'frecuencia_clase' => $fi1,
                'amplitud' => $a,
                'posicion' => $target1,
                'clase_indice' => $clase1['intervalo']
            ],
            // Retorna un array con los valores de Q3
            'q3' => [
                'resultado' => $q3_val,
                'limite_inferior' => $Li3,
                'frecuencia_acumulada_anterior' => $prevF3,
                'frecuencia_clase' => $fi3,
                'amplitud' => $a,
                'posicion' => $target3,
                'clase_indice' => $clase3['intervalo']
            ],
            // Retorna un array con el valor de RIC
            'ric' => $ric
        ];
    }
}
