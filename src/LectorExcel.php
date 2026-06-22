<?php

namespace App;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

/**
 * Clase encargada de leer archivos Excel utilizando PhpSpreadsheet.
 */
class LectorExcel
{
    private $rutaArchivo;
    private $hojaDeCalculo;

    public function __construct(string $rutaArchivo)
    {
        if (!file_exists($rutaArchivo)) {
            throw new Exception("El archivo de Excel especificado no existe.");
        }
        $this->rutaArchivo = $rutaArchivo;
    }

    /**
     * Carga el archivo de Excel en memoria.
     */
    public function cargar(): void
    {
        $lector = IOFactory::createReaderForFile($this->rutaArchivo);
        $lector->setReadDataOnly(true); // Lee solo valores, ignorando formatos
        $this->hojaDeCalculo = $lector->load($this->rutaArchivo);
    }

    /**
     * Retorna una lista con los nombres de todas las pestañas (hojas) del Excel.
     */
    public function obtenerPestanas(): array
    {
        if (!$this->hojaDeCalculo) {
            $this->cargar();
        }
        return $this->hojaDeCalculo->getSheetNames();
    }

    /**
     * Retorna los datos de una pestaña específica como una matriz de filas y columnas.
     */
    public function obtenerDatosPestana(string $nombrePestana): array
    {
        if (!$this->hojaDeCalculo) {
            $this->cargar();
        }
        
        $hoja = $this->hojaDeCalculo->getSheetByName($nombrePestana);
        if (!$hoja) {
            throw new Exception("La pestaña '{$nombrePestana}' no existe en el archivo.");
        }

        // Convierte la hoja a una matriz bidimensional asociando celdas vacías como null.
        return $hoja->toArray(null, true, true, true);
    }
}
