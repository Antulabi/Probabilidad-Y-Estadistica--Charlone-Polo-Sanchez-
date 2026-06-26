# Recorrido del Archivo y Flujo de Datos

Este es el recorrido paso a paso que realiza un archivo de Excel desde el momento en que lo seleccionas en tu pantalla hasta que los resultados matemáticos y gráficos aparecen renderizados en el navegador:

---

### 📂 Paso 1: La Interfaz de Usuario (El Frontend)
Todo comienza en **[index.php]
1. **Interacción:** Arrastras tu archivo `.xlsx` (o `.csv` / `.xls`) al contenedor Drag & Drop de la interfaz o lo seleccionas desde el explorador.
2. **Preparación del Envío (JavaScript):** Un script de JavaScript integrado en la página intercepta la carga, encapsula el archivo en un objeto `FormData` (junto con parámetros opcionales como el número de intervalos $k$ o la amplitud $a$) y realiza una petición asíncrona (AJAX a través de la API `fetch()`) hacia el endpoint del servidor.

---

### 📥 Paso 2: La Recepción en la API
El archivo viaja por la red y es recibido por el archivo **[api/procesar.php]**
1. **Validación:** El script comprueba que la petición sea de tipo `POST` y que el archivo se haya transferido sin errores.
2. **Obtención del Archivo:** Se extrae el archivo y se almacena temporalmente en la carpeta del servidor (`tmp_name`).
3. **Instanciación:** Se crea un objeto de la clase **[LectorExcel]**, pasándole la ruta del archivo temporal.

---

### 🔍 Paso 3: El Parsea y Lectura del Excel
Ahora la ejecución pasa a **[LectorExcel.php]**
1. **Carga en memoria:** El método `cargar()` utiliza la librería externa `PhpSpreadsheet` (`IOFactory::createReaderForFile()`) para identificar el formato y cargar la estructura completa del documento.
2. **Obtención de datos:** Convierte los datos de la pestaña activa en una matriz tradicional de PHP (filas y columnas) usando `$hoja->toArray()`.
3. **Filtrado numérico:** El backend recorre esta matriz, descarta textos o celdas vacías, y se queda únicamente con los valores que sean números (`is_numeric()`), guardándolos en un array plano e individual (`$datosSerie`).

---

### 🧮 Paso 4: El Procesamiento y Cálculo Estadístico
Con el array numérico limpio, se instancia la clase **[CalculadoraEstadistica]** en **[api/procesar.php]**.
1. **El Constructor:** Limpia el array, obtiene el tamaño de la muestra ($n$), ordena los números de menor a mayor y calcula los valores básicos: mínimo, máximo, rango y si son números decimales o enteros.
2. **Cálculo de Datos No Agrupados:** Se ejecutan los métodos para obtener la media, la mediana, las modas, los cuartiles y la tabla de frecuencias individuales.
3. **Cálculo de Datos Agrupados (Sturges):**
   * Se determina el número de intervalos $k$ (usando Sturges o el valor configurado a mano).
   * Se calcula la amplitud de clase $a$ y se definen los límites superiores e inferiores de cada intervalo.
   * Se calcula la frecuencia absoluta ($f_i$), marca de clase ($x_{ic}$) e interpolaciones lineales para media, mediana y cuartiles agrupados.
4. **Retorno JSON:** Toda esta información estadística, junto con las explicaciones paso a paso de las ecuaciones, se estructuran en un gran array asociativo en PHP. El controlador formatea este array a formato **JSON** mediante `json_encode()` y lo retorna al cliente HTTP con el encabezado de tipo de contenido `application/json`.

---

### 📊 Paso 5: La Muestra en Pantalla (Frontend)
El script de JavaScript dentro de **[index.php]** recibe el JSON:
1. **Tablas y Tarjetas:** Recorre la estructura del JSON e inyecta dinámicamente el HTML con la tabla de distribución de frecuencias, la tabla de datos agrupados, la varianza, el coeficiente de variación y los desvíos.
2. **Renderizado de Ecuaciones (KaTeX):** La librería KaTeX lee las fórmulas que envió el backend y las dibuja en pantalla con formato de notación matemática perfecto (LaTeX).
3. **Gráficos Interactivos (Chart.js):** Se inicializan dos gráficos interactivos:
   * Un diagrama de barras que muestra la frecuencia de cada dato individual.
   * Un histograma de frecuencias con barras unidas para los datos agrupados por intervalos.
4. **Estilo y Visualización:** Mediante las reglas en **[index.css]**, la pantalla de carga se desvanece suavemente para revelar el panel de resultados estructurado en tarjetas de diseño tipo cristal (glassmorphism).
