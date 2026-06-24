# Analizador Estadístico de Excel — TECDA 2026

Este proyecto es una aplicación web interactiva diseñada para la cátedra de **Probabilidad y Estadística** (Prof. José Meana, TECDA 2026). Permite cargar archivos Excel (`.xlsx`, `.xls` o `.csv`), extraer series de datos numéricos y realizar un análisis estadístico completo para **datos no agrupados** y **datos agrupados** por intervalos de clase (utilizando la regla de Sturges).

---

## 🚀 Arquitectura del Proyecto

El sistema está desarrollado con **PHP** en el backend (organizado bajo el estándar PSR-4) y una interfaz web moderna y dinámica basada en **HTML, CSS y JavaScript** nativo en el frontend.

La estructura general de archivos y directorios del proyecto es la siguiente:

```
├── .gitignore          # Archivo de exclusiones de Git (IDE, vendor, temporales)
├── composer.json       # Configuración de dependencias PHP (autoloader y librerías)
├── composer.lock       # Registro de versiones fijadas de dependencias
├── index.php           # Interfaz de usuario (HTML + JS dinámico + integraciones gráficas)
├── index.css           # Hoja de estilos de la interfaz (diseño premium y responsive)
├── vercel.json         # Configuración de despliegue para Vercel Serverless
├── api/
│   ├── index.php       # Puente de enrutamiento para desplegar en Vercel
│   └── procesar.php    # Endpoint de la API que recibe el archivo y calcula la estadística
├── src/
│   ├── LectorExcel.php # Clase auxiliar para parsear y extraer datos de Excel
│   └── CalculadoraEstadistica.php # Núcleo matemático de cálculos y fórmulas estadísticas
└── scratch/            # Directorio local temporal para pruebas (ignorado en Git)
```

---

## 🛠️ ¿Qué hace cada archivo y componente?

### 📦 Configuración y Dependencias

*   **[`composer.json`](./composer.json) & `composer.lock`**:
    Define las dependencias requeridas en el backend:
    *   `phpoffice/phpspreadsheet`: Biblioteca utilizada para la lectura del contenido de hojas de cálculo de Excel.
    *   `amenadiel/jpgraph`: Para la generación de diagramas en servidor (aunque los gráficos del frontend están optimizados con Chart.js).
    *   Configura el autoloader PSR-4 para asociar el namespace `App\` a la carpeta `src/`.

*   **[`vercel.json`](./vercel.json)**:
    Configura las reglas de reescritura de rutas y el runtime `vercel-php@0.9.0` para que el proyecto pueda desplegarse sin problemas y funcionar como Serverless Functions en la plataforma de Vercel.

*   **[`.gitignore`](./.gitignore)**:
    Evita subir archivos innecesarios al control de versiones de Git, como dependencias locales (`vendor/`), carpetas de borrador (`scratch/`), ejecutables locales (`composer.phar`), configuraciones de IDEs (`.vscode/`, `.idea/`) y archivos temporales del sistema operativo (`.DS_Store`, `Thumbs.db`).

---

### 🖥️ Frontend (Interfaz de Usuario)

*   **[`index.php`](./index.php)**:
    Es la página de entrada de la aplicación. Contiene:
    *   **Estructura HTML5**: Formulario interactivo con área de arrastrar y soltar (Drag & Drop) para la carga del Excel.
    *   **Controles de Entrada**: Selectores para alternar dinámicamente entre pestañas del Excel, configurar manualmente el número de intervalos ($k$) o forzar la amplitud de clase ($a$).
    *   **Paneles de Explicaciones Paso a Paso**: Espacio interactivo que desglosa cómo se calculan la mediana, varianza, desvíos y cuartiles aplicando fórmulas paso a paso.
    *   **Librerías del Frontend**:
        *   **Chart.js**: Renderiza gráficos estadísticos (diagramas de barra para valores individuales e histogramas para datos agrupados).
        *   **KaTeX**: Permite visualizar de forma limpia e impecable todas las ecuaciones y fórmulas estadísticas en formato matemático (LaTeX).
    *   **Lógica en JavaScript (AJAX)**: Envía el archivo Excel al endpoint del backend, parsea la respuesta JSON y actualiza la interfaz y los gráficos de forma asíncrona sin recargar la página.

*   **[`index.css`](./index.css)**:
    Controla todo el apartado estético. Utiliza variables CSS para implementar una paleta de colores moderna en tonos oscuros/cristal (glassmorphism), tarjetas con sombras suaves, bordes redondeados y micro-animaciones en los elementos interactivos.

---

### ⚙️ Backend (API y Procesamiento)

*   **[`api/procesar.php`](./api/procesar.php)**:
    El controlador del backend. Recibe el archivo Excel cargado mediante una solicitud HTTP POST:
    1.  Usa `LectorExcel` para cargar y parsear la hoja.
    2.  Filtra y extrae solo las celdas con valores numéricos válidos en la pestaña seleccionada.
    3.  Instancia la `CalculadoraEstadistica` pasando los datos.
    4.  Ejecuta todos los métodos estadísticos y formatea una respuesta JSON completa con el desglose del paso a paso matemático para enviarla de vuelta al frontend.

*   **[`api/index.php`](./api/index.php)**:
    Un script puente muy simple que importa el index de la raíz (`index.php`) para que Vercel pueda rutear y servir la interfaz principal desde la carpeta pública `api/`.

---

### 🧮 Lógica de Negocio y Cálculo (Carpeta `src/`)

*   **[`src/LectorExcel.php`](./src/LectorExcel.php)**:
    Clase que encapsula el uso de `PhpSpreadsheet`. Se encarga de validar la existencia del archivo temporal, cargarlo solo como lectura de datos (omitiendo formatos pesados para mejorar la velocidad), obtener el nombre de las pestañas internas y convertir las celdas de una pestaña en una matriz de PHP.

*   **[`src/CalculadoraEstadistica.php`](./src/CalculadoraEstadistica.php)**:
    Es el núcleo de la aplicación. Contiene toda la lógica matemática y algoritmos estadísticos:
    *   **Datos No Agrupados**:
        *   `media()`: Suma de la muestra sobre la cantidad total de datos ($n$).
        *   `mediana()`: Ordenamiento y obtención del dato central (o promedio de los dos datos centrales si la muestra es par).
        *   `moda()`: Obtención de los valores con mayor frecuencia absoluta (soporta series unimodales, bimodales, multimodales o sin moda).
        *   `varianza()`, `desviacionEstandar()` y `coeficienteVariacion()`: Fórmulas muestrales (división por $n-1$).
        *   `cuartiles()`: Cálculo de $Q_1$ y $Q_3$ por el método de las mitades (división del conjunto ordenado en dos subconjuntos y obtención de sus medianas, excluyendo el elemento central si la muestra es impar).
        *   `tablaFrecuencias()`: Generación de la tabla de frecuencias individuales (frecuencia absoluta, relativa, acumuladas y porcentajes).
    *   **Datos Agrupados en Intervalos**:
        *   `calcularIntervalosSturges()`: Determina la cantidad óptima de intervalos con la regla de Sturges: $k = 1 + 3.322 \cdot \log_{10}(n)$.
        *   `tablaFrecuenciasAgrupada()`: Divide el rango de la serie en $k$ intervalos continuos del tipo $[L_{inf}, L_{sup})$ y calcula la marca de clase ($x_{ic}$), frecuencias y porcentajes.
        *   `mediaAgrupada()`, `medianaAgrupada()` y `cuartilesAgrupados()`: Resuelve las interpolaciones lineales correspondientes usando las frecuencias acumuladas anteriores y límites reales.
        *   `varianzaAgrupada()`, `desviacionEstandarAgrupada()` y `coeficienteVariacionAgrupada()`: Pondera las desviaciones de las marcas de clase respecto a la media por su frecuencia absoluta de clase.

---

## 💻 Requisitos y Configuración Local

Si otro programador clona este repositorio, puede levantarlo localmente siguiendo estos pasos:

1.  **Clonar el repositorio**:
    ```bash
    git clone https://github.com/Antulabi/Probabilidad-Y-Estadistica--Charlone-Polo-Sanchez-.git
    cd Probabilidad-Y-Estadistica--Charlone-Polo-Sanchez-
    ```

2.  **Instalar dependencias**:
    Asegúrate de tener [Composer](https://getcomposer.org/) instalado globalmente y ejecuta:
    ```bash
    composer install
    ```
    *Esto creará la carpeta local `vendor/` e instalará `PhpSpreadsheet` y las librerías necesarias de forma automática.*

3.  **Iniciar el servidor web local**:
    Puedes usar el servidor embebido de PHP en la terminal corriendo:
    ```bash
    php -S localhost:8000
    ```

4.  **Acceder a la aplicación**:
    Abre tu navegador e ingresa a `http://localhost:8000`. ¡Listo para usar!
