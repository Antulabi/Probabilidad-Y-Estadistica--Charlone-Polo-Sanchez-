<?php
// Se usa namespace para organizar el código dentro del proyecto
namespace App;
// usamos PhpOffice\PhpSpreadsheet\IOFactory para leer archivos Excel
use PhpOffice\PhpSpreadsheet\IOFactory;
// Se usa Exception para manejar errores
use Exception;

/**
 * Clase encargada de leer archivos Excel utilizando PhpSpreadsheet.
 */
class LectorExcel
{
    // Ruta del archivo de Excel
    private $rutaArchivo;
    // Hoja de calculo
    private $hojaDeCalculo;
    // Constructor
    public function __construct(string $rutaArchivo)
    {
        // Verifica si el archivo de Excel existe
        if (!file_exists($rutaArchivo)) {
            // Lanza una excepcion si el archivo de Excel no existe
            throw new Exception("El archivo de Excel especificado no existe.");
        }
        // Asigna la ruta del archivo de Excel
        $this->rutaArchivo = $rutaArchivo;
    }

    /**
     * Carga el archivo de Excel en memoria.
     */
    public function cargar(): void
    {
        // Crea un lector de archivos Excel
        $lector = IOFactory::createReaderForFile($this->rutaArchivo);
        // Lee solo valores, ignorando formatos
        $lector->setReadDataOnly(true);
        // Carga el archivo de Excel
        $this->hojaDeCalculo = $lector->load($this->rutaArchivo);
    }

    /**
     * Retorna una lista con los nombres de todas las pestañas (hojas) del Excel.
     */
    public function obtenerPestanas(): array
    {
        // Si no se cargó el archivo de Excel, se carga
        if (!$this->hojaDeCalculo) {
            $this->cargar();
        }
        // Retorna los nombres de las pestañas
        return $this->hojaDeCalculo->getSheetNames();
    }

    /**
     * Retorna los datos de una pestaña específica como una matriz de filas y columnas.
     */
    public function obtenerDatosPestana(string $nombrePestana): array
    {
        // Si no se cargó el archivo de Excel, se carga
        if (!$this->hojaDeCalculo) {
            $this->cargar();
        }
        // Obtiene la pestaña especificada
        $hoja = $this->hojaDeCalculo->getSheetByName($nombrePestana);
        // Si no existe la pestaña, lanza una excepcion
        if (!$hoja) {
            throw new Exception("La pestaña '{$nombrePestana}' no existe en el archivo.");
        }

        // Convierte la hoja a una matriz bidimensional asociando celdas vacías como null.
        return $hoja->toArray(null, true, true, true);
    }
}
