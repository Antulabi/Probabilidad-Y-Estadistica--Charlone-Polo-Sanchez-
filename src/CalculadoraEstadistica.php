<?php

namespace App;

use Exception;

/**
 * Clase encargada de realizar todos los cálculos estadísticos requeridos
 * según el apunte del profesor Meana (TECDA 2026).
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

        $this->min = $this->datosOrdenados[0];
        $this->max = $this->datosOrdenados[$this->cantidad - 1];
        $this->rango = $this->max - $this->min;

        // Determinar si todos los datos son enteros
        $this->soloEnteros = true;
        foreach ($this->datosOrdenados as $dato) {
            if (floor($dato) != $dato) {
                $this->soloEnteros = false;
                break;
            }
        }
    }

    public function obtenerDatos(): array
    {
        return $this->datos;
    }

    public function obtenerDatosOrdenados(): array
    {
        return $this->datosOrdenados;
    }

    public function obtenerCantidad(): int
    {
        return $this->cantidad;
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
            // Impar (1-based: (n+1)/2, 0-based: (n-1)/2)
            $posicion = ($this->cantidad - 1) / 2;
            return $this->datosOrdenados[$posicion];
        } else {
            // Par (promedio de los dos centrales)
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
        $frecuencias = array_count_values(array_map('strval', $this->datos));
        $maxFrecuencia = max($frecuencias);

        // Si todos los datos se repiten el mismo número de veces (ej: frecuencia = 1), no hay moda
        if ($maxFrecuencia === 1 && count($frecuencias) > 1) {
            return [];
        }

        $modas = [];
        foreach ($frecuencias as $valor => $frecuencia) {
            if ($frecuencia === $maxFrecuencia) {
                $modas[] = floatval($valor);
            }
        }
        sort($modas);
        return $modas;
    }

    /**
     * Genera la Tabla de Distribución de Frecuencias para datos no agrupados.
     */
    public function tablaFrecuencias(): array
    {
        $frecuencias = array_count_values(array_map('strval', $this->datosOrdenados));
        ksort($frecuencias); // Ordenar por el valor del dato

        $tabla = [];
        $acumuladaAbsoluta = 0;
        $n = $this->cantidad;

        foreach ($frecuencias as $dato => $f_i) {
            $acumuladaAbsoluta += $f_i;
            $f_r = $f_i / $n;
            $F_r = $acumuladaAbsoluta / $n;

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
        if ($amplitudForzada !== null) {
            $a = $amplitudForzada;
        } elseif ($this->soloEnteros) {
            $a = (int) round($this->rango / $k);
            if ($a < 1) $a = 1;
            
            // Ajustar k dinámicamente si es necesario para cubrir el máximo
            while ($this->min + $k * $a - 1 < $this->max) {
                $k++;
            }
        } else {
            $a = $this->rango / $k;
        }
        
        $tabla = [];
        $acumuladaAbsoluta = 0;
        $n = $this->cantidad;

        for ($i = 0; $i < $k; $i++) {
            if ($this->soloEnteros) {
                $limiteNominalInferior = $this->min + ($i * $a);
                $limiteNominalSuperior = $limiteNominalInferior + $a - 1;

                $limiteRealInferior = $limiteNominalInferior - 0.5;
                $limiteRealSuperior = $limiteNominalSuperior + 0.5;
            } else {
                $limiteNominalInferior = $this->min + ($i * $a);
                $limiteNominalSuperior = $this->min + (($i + 1) * $a);

                $limiteRealInferior = $limiteNominalInferior;
                $limiteRealSuperior = $limiteNominalSuperior;
            }

            // Marca de clase (x_ic): Punto medio del intervalo
            $x_ic = ($limiteNominalInferior + $limiteNominalSuperior) / 2;

            // Contar frecuencia absoluta (f_i) en el intervalo
            $f_i = 0;
            foreach ($this->datosOrdenados as $dato) {
                if ($this->soloEnteros) {
                    if ($dato >= $limiteNominalInferior && $dato <= $limiteNominalSuperior) {
                        $f_i++;
                    }
                } else {
                    if ($i === $k - 1) {
                        if ($dato >= $limiteNominalInferior && $dato <= $limiteNominalSuperior) {
                            $f_i++;
                        }
                    } else {
                        if ($dato >= $limiteNominalInferior && $dato < $limiteNominalSuperior) {
                            $f_i++;
                        }
                    }
                }
            }

            $acumuladaAbsoluta += $f_i;
            $f_r = $f_i / $n;
            $F_r = $acumuladaAbsoluta / $n;

            $tabla[] = [
                'intervalo' => $i + 1,
                'lim_inf'   => $limiteNominalInferior,
                'lim_sup'   => $limiteNominalSuperior,
                'lim_real_inf' => $limiteRealInferior,
                'lim_real_sup' => $limiteRealSuperior,
                'x_ic'      => $x_ic,
                'f_i'       => $f_i,
                'f_r'       => $f_r,
                'F_i'       => $acumuladaAbsoluta,
                'F_r'       => $F_r,
                'porcentaje'=> $f_r * 100
            ];
        }

        return $tabla;
    }

    /**
     * Calcula la media agrupada.
     * Fórmula: \bar{x} = \frac{\sum x_{ic} \cdot f_i}{n}
     */
    public function mediaAgrupada(?array $tabla = null): float
    {
        $tabla = $tabla ?? $this->tablaFrecuenciasAgrupada();
        $sumaProductos = 0;
        foreach ($tabla as $fila) {
            $sumaProductos += $fila['x_ic'] * $fila['f_i'];
        }
        return $sumaProductos / $this->cantidad;
    }

    /**
     * Calcula la mediana agrupada por interpolación lineal.
     * Fórmula: Me = L_i + ( (n/2 - F_{i-1}) / f_i ) * a
     */
    public function medianaAgrupada(?array $tabla = null, ?int $k = null, ?float $amplitudForzada = null): array
    {
        $tabla = $tabla ?? $this->tablaFrecuenciasAgrupada($k, $amplitudForzada);
        
        if ($amplitudForzada !== null) {
            $a = $amplitudForzada;
        } elseif ($this->soloEnteros) {
            $a = $tabla[0]['lim_sup'] - $tabla[0]['lim_inf'] + 1;
        } else {
            $a = $tabla[0]['lim_sup'] - $tabla[0]['lim_inf'];
        }
        
        $n_medios = $this->cantidad / 2;
        
        // Buscar el primer intervalo donde la frecuencia acumulada F_i >= n/2
        $indiceIntervaloMediana = -1;
        foreach ($tabla as $index => $fila) {
            if ($fila['F_i'] >= $n_medios) {
                $indiceIntervaloMediana = $index;
                break;
            }
        }

        // Si no se encuentra (caso de error en frecuencias), usar el último intervalo
        if ($indiceIntervaloMediana === -1) {
            $indiceIntervaloMediana = count($tabla) - 1;
        }

        $filaMediana = $tabla[$indiceIntervaloMediana];
        $L_i = $filaMediana['lim_real_inf']; // Límite real inferior
        
        // Frecuencia acumulada anterior F_{i-1}
        $F_anterior = 0;
        if ($indiceIntervaloMediana > 0) {
            $F_anterior = $tabla[$indiceIntervaloMediana - 1]['F_i'];
        }

        $f_i = $filaMediana['f_i']; // Frecuencia absoluta de la clase mediana

        // Evitar división por cero si la frecuencia del intervalo es 0
        if ($f_i === 0) {
            $mediana = $L_i;
        } else {
            $mediana = $L_i + (($n_medios - $F_anterior) / $f_i) * $a;
        }

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
        $tabla = $tabla ?? $this->tablaFrecuenciasAgrupada();
        
        // Buscar la máxima frecuencia absoluta
        $maxFrecuencia = 0;
        foreach ($tabla as $fila) {
            if ($fila['f_i'] > $maxFrecuencia) {
                $maxFrecuencia = $fila['f_i'];
            }
        }

        $intervalosModales = [];
        foreach ($tabla as $fila) {
            if ($fila['f_i'] === $maxFrecuencia) {
                $intervalosModales[] = $fila;
            }
        }

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
        $n = $this->cantidad;
        $media = $this->media();
        if ($n <= 1) {
            return [
                'resultado' => 0.0,
                'media' => $media,
                'cantidad' => $n,
                'suma_desviaciones' => 0.0,
                'desviaciones' => []
            ];
        }

        $sumaDesviaciones = 0.0;
        $desviaciones = [];
        foreach ($this->datos as $x) {
            $diff = $x - $media;
            $diffSq = $diff * $diff;
            $desviaciones[] = [
                'valor' => $x,
                'diferencia' => $diff,
                'diferencia_cuadrado' => $diffSq
            ];
            $sumaDesviaciones += $diffSq;
        }

        $varianza = $sumaDesviaciones / ($n - 1);

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
        return ($this->desviacionEstandar() / $media) * 100;
    }

    /**
     * Calcula la mediana de un conjunto de datos plano.
     */
    private function calcularMedianaArray(array $valores): float
    {
        $cant = count($valores);
        if ($cant === 0) {
            return 0.0;
        }
        if ($cant % 2 !== 0) {
            $pos = ($cant - 1) / 2;
            return $valores[$pos];
        } else {
            $pos1 = ($cant / 2) - 1;
            $pos2 = $cant / 2;
            return ($valores[$pos1] + $valores[$pos2]) / 2.0;
        }
    }

    /**
     * Calcula los cuartiles Q1 y Q3 para datos no agrupados usando el método de las mitades (Prof. Meana).
     * Q1 es la mediana de la mitad inferior de los datos, y Q3 es la mediana de la mitad superior
     * (excluyendo la mediana si n es impar).
     */
    public function cuartiles(): array
    {
        $n = $this->cantidad;
        
        if ($n % 2 === 0) {
            // Caso N par: se divide la serie ordenada a la mitad exacta
            $mitadInferior = array_slice($this->datosOrdenados, 0, $n / 2);
            $mitadSuperior = array_slice($this->datosOrdenados, $n / 2);
            $q1_pos = "mediana de la mitad inferior (primeros " . ($n / 2) . " datos)";
            $q3_pos = "mediana de la mitad superior (últimos " . ($n / 2) . " datos)";
        } else {
            // Caso N impar: se excluye el dato central de la mediana
            $mitadInferior = array_slice($this->datosOrdenados, 0, ($n - 1) / 2);
            $mitadSuperior = array_slice($this->datosOrdenados, ($n + 1) / 2);
            $q1_pos = "mediana de la mitad inferior (primeros " . (($n - 1) / 2) . " datos, excluyendo el elemento central)";
            $q3_pos = "mediana de la mitad superior (últimos " . (($n - 1) / 2) . " datos, excluyendo el elemento central)";
        }

        $q1 = $this->calcularMedianaArray($mitadInferior);
        $q3 = $this->calcularMedianaArray($mitadSuperior);
        $ric = $q3 - $q1;

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
        $tabla = $tabla ?? $this->tablaFrecuenciasAgrupada();
        $n = $this->cantidad;
        $mediaAgrupada = $this->mediaAgrupada($tabla);
        if ($n <= 1) {
            return [
                'resultado' => 0.0,
                'media' => $mediaAgrupada,
                'cantidad' => $n,
                'suma_desviaciones' => 0.0,
                'productos' => []
            ];
        }

        $sumaDesviaciones = 0.0;
        $productos = [];
        foreach ($tabla as $fila) {
            $mc = $fila['x_ic'];
            $fi = $fila['f_i'];
            $diff = $mc - $mediaAgrupada;
            $diffSq = $diff * $diff;
            $prod = $fi * $diffSq;

            $productos[] = [
                'marca_clase' => $mc,
                'frecuencia' => $fi,
                'diferencia' => $diff,
                'diferencia_cuadrado' => $diffSq,
                'producto' => $prod
            ];
            $sumaDesviaciones += $prod;
        }

        $varianza = $sumaDesviaciones / ($n - 1);

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
        return sqrt($this->varianzaAgrupada($tabla)['resultado']);
    }

    /**
     * Calcula el coeficiente de variación de datos agrupados.
     */
    public function coeficienteVariacionAgrupada(?array $tabla = null): float
    {
        $media = $this->mediaAgrupada($tabla);
        if ($media == 0.0) {
            return 0.0;
        }
        return ($this->desviacionEstandarAgrupada($tabla) / $media) * 100;
    }

    /**
     * Calcula los cuartiles Q1 y Q3 para datos agrupados mediante interpolación lineal.
     */
    public function cuartilesAgrupados(?array $tabla = null, ?int $k = null, ?float $amplitudForzada = null): array
    {
        $tabla = $tabla ?? $this->tablaFrecuenciasAgrupada($k, $amplitudForzada);
        $n = $this->cantidad;

        if ($amplitudForzada !== null) {
            $a = $amplitudForzada;
        } elseif ($this->soloEnteros) {
            $a = $tabla[0]['lim_sup'] - $tabla[0]['lim_inf'] + 1;
        } else {
            $a = $tabla[0]['lim_sup'] - $tabla[0]['lim_inf'];
        }

        // Q1 (k = 1) -> posicion = n / 4
        $target1 = $n / 4.0;
        $clase1 = null;
        $prevF1 = 0;

        foreach ($tabla as $idx => $fila) {
            if ($fila['F_i'] >= $target1) {
                $clase1 = $fila;
                if ($idx > 0) {
                    $prevF1 = $tabla[$idx - 1]['F_i'];
                }
                break;
            }
        }
        if (!$clase1) {
            $clase1 = end($tabla);
            $countInt = count($tabla);
            if ($countInt > 1) {
                $prevF1 = $tabla[$countInt - 2]['F_i'];
            }
        }

        $Li1 = $clase1['lim_real_inf'];
        $fi1 = $clase1['f_i'];
        $q1_val = $fi1 === 0 ? $Li1 : $Li1 + (($target1 - $prevF1) / $fi1) * $a;

        // Q3 (k = 3) -> posicion = 3n / 4
        $target3 = (3 * $n) / 4.0;
        $clase3 = null;
        $prevF3 = 0;

        foreach ($tabla as $idx => $fila) {
            if ($fila['F_i'] >= $target3) {
                $clase3 = $fila;
                if ($idx > 0) {
                    $prevF3 = $tabla[$idx - 1]['F_i'];
                }
                break;
            }
        }
        if (!$clase3) {
            $clase3 = end($tabla);
            $countInt = count($tabla);
            if ($countInt > 1) {
                $prevF3 = $tabla[$countInt - 2]['F_i'];
            }
        }

        $Li3 = $clase3['lim_real_inf'];
        $fi3 = $clase3['f_i'];
        $q3_val = $fi3 === 0 ? $Li3 : $Li3 + (($target3 - $prevF3) / $fi3) * $a;

        $ric = $q3_val - $q1_val;

        return [
            'q1' => [
                'resultado' => $q1_val,
                'limite_inferior' => $Li1,
                'frecuencia_acumulada_anterior' => $prevF1,
                'frecuencia_clase' => $fi1,
                'amplitud' => $a,
                'posicion' => $target1,
                'clase_indice' => $clase1['intervalo']
            ],
            'q3' => [
                'resultado' => $q3_val,
                'limite_inferior' => $Li3,
                'frecuencia_acumulada_anterior' => $prevF3,
                'frecuencia_clase' => $fi3,
                'amplitud' => $a,
                'posicion' => $target3,
                'clase_indice' => $clase3['intervalo']
            ],
            'ric' => $ric
        ];
    }
}
