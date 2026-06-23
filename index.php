<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analizador Estadístico - TECDA 2026</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- KaTeX para fórmulas matemáticas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js"></script>
    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Estilos locales -->
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Analizador Estadístico de Excel</h1>
            <p>Trabajo Evaluativo - Cátedra de Probabilidad y Estadística (Prof. Meana, José)</p>
        </header>

        <!-- Sección de Carga de Archivo -->
        <div class="card" id="upload-card">
            <h2>Carga del Dataset</h2>
            <form id="upload-form" enctype="multipart/form-data">
                <input type="file" name="archivo_excel" id="file-input" accept=".xlsx, .xls, .csv" class="hidden">
                <div class="upload-zone" id="drop-zone">
                    <i class="fa-solid fa-file-excel"></i>
                    <p id="upload-text">Arrastra tu archivo Excel aquí o haz clic para buscar</p>
                    <span>Soporta formatos .xlsx, .xls y .csv</span>
                </div>
            </form>
        </div>

        <div id="loader" class="card hidden" style="text-align: center;">
            <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 2.5rem; color: var(--accent); margin-bottom: 15px;"></i>
            <p>Procesando datos del Excel...</p>
        </div>

        <div id="error-message" class="alert hidden"></div>

        <!-- Sección de Resultados (inicialmente oculta) -->
        <div id="results-section" class="hidden">
            
            <!-- Configuraciones del Dataset cargado -->
            <div class="card">
                <h2>Configuración de Datos Cargados</h2>
                <div id="dataset-info" style="margin-bottom: 20px; color: var(--text-secondary);">
                    <!-- Nombre del archivo y detalles de carga -->
                </div>
                <div class="selectors-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div class="form-group">
                        <label for="select-pestana"><i class="fa-solid fa-table"></i> Pestaña (Hoja):</label>
                        <select id="select-pestana">
                            <!-- Opciones cargadas dinámicamente -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="input-k"><i class="fa-solid fa-list-ol"></i> Cant. Intervalos ($k$):</label>
                        <input type="number" id="input-k" min="1" placeholder="Ej: 7" style="background-color: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-color); padding: 10px; border-radius: var(--radius-md); font-size: 1rem;">
                    </div>
                    <div class="form-group">
                        <label for="input-a"><i class="fa-solid fa-arrows-left-right"></i> Amplitud ($a$):</label>
                        <input type="number" id="input-a" min="0.0001" step="any" placeholder="Ej: 10" style="background-color: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-color); padding: 10px; border-radius: var(--radius-md); font-size: 1rem;">
                    </div>
                </div>
            </div>

            <!-- Segmented Control de Modos -->
            <div class="segmented-control">
                <button class="segment-btn active" onclick="switchMode('no_agrupados')">Datos No Agrupados</button>
                <button class="segment-btn" onclick="switchMode('agrupados')">Datos Agrupados</button>
            </div>

            <!-- CONTENEDOR 1: DATOS NO AGRUPADOS -->
            <div id="ungrouped-container" class="mode-container">
                <!-- Tarjetas de tendencia central -->
                <div class="results-grid">
                    <div class="result-card">
                        <div class="label">Media Aritmética</div>
                        <div class="value" id="ungrouped-media">-</div>
                        <div class="formula" id="formula-media-no-agrupada">$$\bar{x} = \frac{\sum x_i}{n}$$</div>
                    </div>
                    <div class="result-card">
                        <div class="label">Mediana</div>
                        <div class="value" id="ungrouped-mediana">-</div>
                        <div class="formula" id="formula-mediana-no-agrupada">$$Me = X_{\frac{n+1}{2}}$$</div>
                    </div>
                    <div class="result-card">
                        <div class="label">Moda</div>
                        <div class="value" id="ungrouped-moda">-</div>
                        <div class="formula">$$M_0$$</div>
                    </div>
                </div>

                <!-- Tarjetas de dispersión no agrupadas -->
                <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 1.2rem; color: var(--text-primary); border-bottom: 1px solid var(--border-color); padding-bottom: 5px;"><i class="fa-solid fa-arrows-left-right"></i> Medidas de Dispersión</h3>
                <div class="results-grid">
                    <div class="result-card">
                        <div class="label">Varianza Muestral</div>
                        <div class="value" id="ungrouped-varianza">-</div>
                        <div class="formula">$$S^2 = \frac{\sum (x_i - \bar{x})^2}{n-1}$$</div>
                    </div>
                    <div class="result-card">
                        <div class="label">Desvío Estándar</div>
                        <div class="value" id="ungrouped-desviacion">-</div>
                        <div class="formula">$$S = \sqrt{S^2}$$</div>
                    </div>
                    <div class="result-card">
                        <div class="label">Coef. de Variación</div>
                        <div class="value" id="ungrouped-cv" style="font-size: 1.6rem; padding-top: 10px;">-</div>
                        <div class="formula">$$CV = \frac{S}{\bar{x}} \cdot 100\%$$</div>
                    </div>
                    <div class="result-card">
                        <div class="label">Rango Intercuartil</div>
                        <div class="value" id="ungrouped-ric">-</div>
                        <div class="formula">$$RIC = Q_3 - Q_1$$</div>
                    </div>
                </div>

                <!-- Panel de Explicaciones Académicas (Paso a Paso) -->
                <div class="card">
                    <h2>Desglose y Fórmulas Paso a Paso (Sin Agrupar)</h2>
                    <div class="segmented-control" style="margin-bottom: 20px;">
                        <button class="segment-btn active" id="tab-btn-median-ungrouped" onclick="switchExplanationTab('ungrouped', 'median')">1. Mediana ($Me$)</button>
                        <button class="segment-btn" id="tab-btn-dispersion-ungrouped" onclick="switchExplanationTab('ungrouped', 'dispersion')">2. Varianza y Desvío ($S^2, S$)</button>
                        <button class="segment-btn" id="tab-btn-quartiles-ungrouped" onclick="switchExplanationTab('ungrouped', 'quartiles')">3. Cuartiles y RIC ($Q_k, RIC$)</button>
                    </div>
                    
                    <div id="explanation-median-ungrouped" class="explanation">
                        <!-- Se inyecta dinámicamente -->
                    </div>
                    
                    <div id="explanation-dispersion-ungrouped" class="explanation hidden">
                        <!-- Se inyecta dinámicamente -->
                    </div>
                    
                    <div id="explanation-quartiles-ungrouped" class="explanation hidden">
                        <!-- Se inyecta dinámicamente -->
                    </div>
                </div>

                <!-- Interpretación de Resultados (Punto C) -->
                <div class="card" id="interpretation-card-ungrouped">
                    <h2>Interpretación de Resultados (Punto C)</h2>
                    <div id="interpretation-content-ungrouped" class="interpretation-container">
                        <!-- Se inyecta dinámicamente -->
                    </div>
                </div>

                <!-- Gráfico de Distribución (Punto D) -->
                <div class="card">
                    <h2>Distribución de Frecuencias (Valores Individuales)</h2>
                    <div style="position: relative; height: 320px; width: 100%;">
                        <canvas id="chart-no-agrupados"></canvas>
                    </div>
                </div>

                <!-- Tabla de frecuencias no agrupada -->
                <div class="card">
                    <h2>Tabla de Frecuencias (Datos Individuales)</h2>
                    <div class="table-container">
                        <table id="table-frecuencias-no-agrupada">
                            <thead>
                                <tr>
                                    <th>Dato ($x_i$)</th>
                                    <th>Frec. Absoluta ($f_i$)</th>
                                    <th>Frec. Relativa ($f_r$)</th>
                                    <th>Frec. Abs. Acumulada ($F_i$)</th>
                                    <th>Frec. Rel. Acumulada ($F_r$)</th>
                                    <th>Porcentaje (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Inyección dinámica -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- CONTENEDOR 2: DATOS AGRUPADOS -->
            <div id="grouped-container" class="mode-container hidden">
                <!-- Tarjetas de tendencia central -->
                <div class="results-grid">
                    <div class="result-card">
                        <div class="label">Media Agrupada</div>
                        <div class="value" id="grouped-media">-</div>
                        <div class="formula">$$\bar{x} = \frac{\sum x_{ic} \cdot f_i}{n}$$</div>
                    </div>
                    <div class="result-card">
                        <div class="label">Mediana Agrupada</div>
                        <div class="value" id="grouped-mediana">-</div>
                        <div class="formula">$$Me = L_i + \left( \frac{\frac{n}{2} - F_{i-1}}{f_i} \right) \cdot a$$</div>
                    </div>
                    <div class="result-card">
                        <div class="label">Intervalo Modal</div>
                        <div class="value" id="grouped-modal" style="font-size: 1.4rem; padding-top: 15px;">-</div>
                        <div class="label" style="font-size: 0.75rem; color: var(--success); font-weight: bold; margin-top: 5px;">Máxima Frecuencia</div>
                    </div>
                </div>

                <!-- Tarjetas de dispersión agrupadas -->
                <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 1.2rem; color: var(--text-primary); border-bottom: 1px solid var(--border-color); padding-bottom: 5px;"><i class="fa-solid fa-arrows-left-right"></i> Medidas de Dispersión Agrupada</h3>
                <div class="results-grid">
                    <div class="result-card">
                        <div class="label">Varianza Agrupada</div>
                        <div class="value" id="grouped-varianza">-</div>
                        <div class="formula">$$S^2 = \frac{\sum f_i \cdot (x_{ic} - \bar{x})^2}{n-1}$$</div>
                    </div>
                    <div class="result-card">
                        <div class="label">Desvío Estándar Agrupado</div>
                        <div class="value" id="grouped-desviacion">-</div>
                        <div class="formula">$$S = \sqrt{S^2}$$</div>
                    </div>
                    <div class="result-card">
                        <div class="label">Coef. de Variación Agrupado</div>
                        <div class="value" id="grouped-cv" style="font-size: 1.6rem; padding-top: 10px;">-</div>
                        <div class="formula">$$CV = \frac{S}{\bar{x}} \cdot 100\%$$</div>
                    </div>
                    <div class="result-card">
                        <div class="label">Rango Intercuartil Agrupado</div>
                        <div class="value" id="grouped-ric">-</div>
                        <div class="formula">$$RIC = Q_3 - Q_1$$</div>
                    </div>
                </div>

                <!-- Panel de Explicaciones Académicas (Paso a Paso) -->
                <div class="card">
                    <h2>Desglose y Fórmulas Paso a Paso (Datos Agrupados)</h2>
                    <div class="segmented-control" style="margin-bottom: 20px;">
                        <button class="segment-btn active" id="tab-btn-median-grouped" onclick="switchExplanationTab('grouped', 'median')">1. Mediana Agrupada ($Me$)</button>
                        <button class="segment-btn" id="tab-btn-dispersion-grouped" onclick="switchExplanationTab('grouped', 'dispersion')">2. Varianza y Desvío Agrupado ($S^2, S$)</button>
                        <button class="segment-btn" id="tab-btn-quartiles-grouped" onclick="switchExplanationTab('grouped', 'quartiles')">3. Cuartiles y RIC Agrupados ($Q_k, RIC$)</button>
                    </div>
                    
                    <div id="explanation-median-grouped" class="explanation">
                        <!-- Se inyecta dinámicamente -->
                    </div>
                    
                    <div id="explanation-dispersion-grouped" class="explanation hidden">
                        <!-- Se inyecta dinámicamente -->
                    </div>
                    
                    <div id="explanation-quartiles-grouped" class="explanation hidden">
                        <!-- Se inyecta dinámicamente -->
                    </div>
                </div>

                <!-- Interpretación de Resultados (Punto C) -->
                <div class="card" id="interpretation-card-grouped">
                    <h2>Interpretación de Resultados (Punto C)</h2>
                    <div id="interpretation-content-grouped" class="interpretation-container">
                        <!-- Se inyecta dinámicamente -->
                    </div>
                </div>

                <!-- Histograma de Frecuencias (Punto D) -->
                <div class="card">
                    <h2>Histograma de Frecuencias (Datos Agrupados)</h2>
                    <div style="position: relative; height: 320px; width: 100%;">
                        <canvas id="chart-agrupados"></canvas>
                    </div>
                </div>

                <!-- Tabla de frecuencias agrupada -->
                <div class="card">
                    <h2>Tabla de Frecuencias Agrupadas (Clases por Sturges)</h2>
                    <div style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 15px;" id="sturges-details">
                        <!-- Detalles de Sturges -->
                    </div>
                    <div class="table-container">
                        <table id="table-frecuencias-agrupada">
                            <thead>
                                <tr>
                                    <th>Intervalo Clase</th>
                                    <th>Amplitud ($a$)</th>
                                    <th>Marca Clase ($x_{ic}$)</th>
                                    <th>Frec. Absoluta ($f_i$)</th>
                                    <th>Frec. Relativa ($f_r$)</th>
                                    <th>Frec. Abs. Acumulada ($F_i$)</th>
                                    <th>Frec. Rel. Acumulada ($F_r$)</th>
                                    <th>Porcentaje (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Inyección dinámica -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cuadrícula original colapsable -->
            <div class="card">
                <button class="collapse-btn" onclick="togglePlanilla()">
                    <span><i class="fa-solid fa-table-cells"></i> Ver Planilla de Excel Original</span>
                    <i class="fa-solid fa-chevron-down collapse-icon" id="collapse-icon"></i>
                </button>
                <div class="collapse-content" id="planilla-container">
                    <div class="table-container">
                        <table id="table-planilla">
                            <!-- Se llena dinámicamente -->
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Script de control y procesamiento AJAX -->
    <script>
        let excelData = null; // Almacenará la respuesta del servidor
        let chartNoAgrupados = null;
        let chartAgrupados = null;
        
        function formatNumber(val, decimals = 4) {
            if (val === null || val === undefined) return '-';
            const num = parseFloat(val);
            if (isNaN(num)) return val;
            if (Number.isInteger(num)) return num.toString();
            return parseFloat(num.toFixed(decimals)).toString();
        }
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const uploadForm = document.getElementById('upload-form');
        const resultsSection = document.getElementById('results-section');
        const loader = document.getElementById('loader');
        const errorMessage = document.getElementById('error-message');

        // Manejadores del Drag & Drop
        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                subirArchivo();
            }
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                subirArchivo();
            }
        });

        document.getElementById('input-k').addEventListener('change', () => {
            subirArchivo(true);
        });

        document.getElementById('input-a').addEventListener('change', () => {
            subirArchivo(true);
        });

        // Envío AJAX del formulario
        function subirArchivo(cambioConfig = false) {
            errorMessage.classList.add('hidden');
            if (!cambioConfig) {
                resultsSection.classList.add('hidden');
                loader.classList.remove('hidden');
                
                // Limpiar inputs forzados al cargar un nuevo archivo
                document.getElementById('input-k').value = '';
                document.getElementById('input-a').value = '';
            }

            const formData = new FormData(uploadForm);
            
            // Si es un cambio de configuración, agregamos la pestaña actual
            if (cambioConfig) {
                const pestana = document.getElementById('select-pestana').value;
                formData.append('pestana', pestana);
                
                const inputK = document.getElementById('input-k').value;
                if (inputK && inputK > 0) {
                    formData.append('k_intervalos', inputK);
                }
                
                const inputA = document.getElementById('input-a').value;
                if (inputA && inputA > 0) {
                    formData.append('amplitud_clase', inputA);
                }
            }

            fetch('api/procesar.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                loader.classList.add('hidden');
                if (data.exito) {
                    excelData = data;
                    mostrarResultados();
                } else {
                    mostrarError(data.mensaje || 'Error al procesar el archivo Excel.');
                }
            })
            .catch(err => {
                loader.classList.add('hidden');
                mostrarError('Error de red al comunicarse con el servidor.');
                console.error(err);
            });
        }

        // Mostrar errores
        function mostrarError(msg) {
            errorMessage.textContent = msg;
            errorMessage.classList.remove('hidden');
        }

        // Renderizar los resultados en la página
        function mostrarResultados() {
            resultsSection.classList.remove('hidden');
            
            // Info general
            document.getElementById('dataset-info').innerHTML = `
                <i class="fa-solid fa-file-invoice"></i> Archivo: <strong>${excelData.nombre_archivo}</strong> | 
                Cantidad de datos numéricos analizados: <strong>${excelData.no_agrupados.cantidad}</strong>
            `;

            // Actualizar inputs de configuración con los valores calculados
            document.getElementById('input-k').value = excelData.agrupados.k;
            document.getElementById('input-a').value = excelData.agrupados.amplitud;

            // Llenar selectores
            actualizarSelectores();

            // Renderizar Datos No Agrupados
            document.getElementById('ungrouped-media').textContent = formatNumber(excelData.no_agrupados.media);
            document.getElementById('ungrouped-mediana').textContent = formatNumber(excelData.no_agrupados.mediana);
            
            const modas = excelData.no_agrupados.moda;
            document.getElementById('ungrouped-moda').textContent = modas.length > 0 ? modas.map(m => formatNumber(m)).join(', ') : 'No hay moda';

            document.getElementById('ungrouped-varianza').textContent = formatNumber(excelData.no_agrupados.varianza);
            document.getElementById('ungrouped-desviacion').textContent = formatNumber(excelData.no_agrupados.desviacion);
            
            const cvVal = excelData.no_agrupados.cv;
            const cvColor = cvVal < 30 ? 'var(--success)' : '#f59e0b';
            const cvText = cvVal < 30 ? ' (Homogéneo)' : ' (Heterogéneo)';
            document.getElementById('ungrouped-cv').innerHTML = `${formatNumber(cvVal, 2)}%<br><span style="font-size: 0.8rem; font-weight: bold; color: ${cvColor};">${cvText}</span>`;
            
            document.getElementById('ungrouped-ric').textContent = formatNumber(excelData.no_agrupados.cuartiles.ric);

            // Generar tabla no agrupada
            const tbodyNoAgrupada = document.querySelector('#table-frecuencias-no-agrupada tbody');
            tbodyNoAgrupada.innerHTML = '';
            excelData.no_agrupados.tabla.forEach(fila => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${fila.dato}</strong></td>
                    <td>${fila.f_i}</td>
                    <td>${fila.f_r.toFixed(4)}</td>
                    <td>${fila.F_i}</td>
                    <td>${fila.F_r.toFixed(4)}</td>
                    <td>${fila.porcentaje.toFixed(2)}%</td>
                `;
                tbodyNoAgrupada.appendChild(tr);
            });

            // Explicación de la Mediana No Agrupada
            generarExplicacionMedianaNoAgrupada();
            generarExplicacionVarianzaNoAgrupada();
            generarExplicacionCuartilesNoAgrupada();
            generarInterpretacionNoAgrupada();

            // Renderizar Datos Agrupados
            document.getElementById('grouped-media').textContent = formatNumber(excelData.agrupados.media);
            document.getElementById('grouped-mediana').textContent = formatNumber(excelData.agrupados.mediana);
  
            const intModal = excelData.agrupados.intervalo_modal.intervalos;
            if (intModal.length > 0) {
                const modalesTxt = intModal.map(i => `[${formatNumber(i.lim_inf)} - ${formatNumber(i.lim_sup)})`).join('<br>');
                document.getElementById('grouped-modal').innerHTML = modalesTxt;
            } else {
                document.getElementById('grouped-modal').textContent = '-';
            }

            document.getElementById('grouped-varianza').textContent = formatNumber(excelData.agrupados.varianza);
            document.getElementById('grouped-desviacion').textContent = formatNumber(excelData.agrupados.desviacion);
            
            const cvValGrouped = excelData.agrupados.cv;
            const cvColorGrouped = cvValGrouped < 30 ? 'var(--success)' : '#f59e0b';
            const cvTextGrouped = cvValGrouped < 30 ? ' (Homogéneo)' : ' (Heterogéneo)';
            document.getElementById('grouped-cv').innerHTML = `${formatNumber(cvValGrouped, 2)}%<br><span style="font-size: 0.8rem; font-weight: bold; color: ${cvColorGrouped};">${cvTextGrouped}</span>`;
            
            document.getElementById('grouped-ric').textContent = formatNumber(excelData.agrupados.cuartiles.ric);
            // Sturges detalles
            document.getElementById('sturges-details').innerHTML = `
                Regla de Sturges: $k = 1 + 3.322 \\cdot \\log_{10}(${excelData.no_agrupados.cantidad}) \\approx ${excelData.agrupados.k}$ clases |
                Amplitud calculada de clase ($a$): $${formatNumber(excelData.agrupados.amplitud)}$
            `;

            // Generar tabla agrupada
            const tbodyAgrupada = document.querySelector('#table-frecuencias-agrupada tbody');
            tbodyAgrupada.innerHTML = '';
            
            // Encontrar la máxima frecuencia absoluta
            const maxFi = Math.max(...excelData.agrupados.tabla.map(f => f.f_i));

            excelData.agrupados.tabla.forEach(fila => {
                const tr = document.createElement('tr');
                if (fila.f_i === maxFi && maxFi > 0) {
                    tr.classList.add('highlight-modal-row');
                }
                
                const limInfTxt = Number.isInteger(fila.lim_inf) ? fila.lim_inf.toString() : fila.lim_inf.toFixed(2);
                const limSupTxt = Number.isInteger(fila.lim_sup) ? fila.lim_sup.toString() : fila.lim_sup.toFixed(2);
                
                const xIcTxt = Number.isInteger(fila.x_ic) ? fila.x_ic.toString() : fila.x_ic.toFixed(2);
                const amplitudTxt = Number.isInteger(fila.amplitud) ? fila.amplitud.toString() : fila.amplitud.toFixed(2);
                
                tr.innerHTML = `
                    <td><strong>[${limInfTxt} - ${limSupTxt})</strong></td>
                    <td>${amplitudTxt}</td>
                    <td>${xIcTxt}</td>
                    <td>${fila.f_i}</td>
                    <td>${fila.f_r.toFixed(4)}</td>
                    <td>${fila.F_i}</td>
                    <td>${fila.F_r.toFixed(4)}</td>
                    <td>${fila.porcentaje.toFixed(2)}%</td>
                `;
                tbodyAgrupada.appendChild(tr);
            });

            // Explicación Mediana Agrupada
            generarExplicacionMedianaAgrupada();
            generarExplicacionVarianzaAgrupada();
            generarExplicacionCuartilesAgrupada();
            generarInterpretacionAgrupada();

            // Generar tabla planilla original
            generarPlanillaOriginal();

            // Renderizar gráficos estadísticos (Punto D)
            renderizarGraficos();

            // Renderizar LaTeX matemático
            if (typeof renderMathInElement === 'function') {
                renderMathInElement(document.body, {
                    delimiters: [
                        {left: '$$', right: '$$', display: true},
                        {left: '$', right: '$', display: false}
                    ]
                });
            }
        }

        // Llenar dinámicamente los selectores de Pestaña
        function actualizarSelectores() {
            const selectPestana = document.getElementById('select-pestana');

            // Guardar valor de pestaña activa
            const pestanaActiva = excelData.pestana_seleccionada;

            // Llenar pestañas
            selectPestana.innerHTML = '';
            excelData.pestanas.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p;
                opt.textContent = p;
                if (p === pestanaActiva) opt.selected = true;
                selectPestana.appendChild(opt);
            });

            // Agregar evento para recargar cuando cambie
            selectPestana.onchange = () => subirArchivo(true);
        }

        // Cambiar entre la pestaña No Agrupados y Agrupados
        function switchMode(modo) {
            const btns = document.querySelectorAll('.segment-btn');
            const containerNo = document.getElementById('ungrouped-container');
            const containerSi = document.getElementById('grouped-container');

            if (modo === 'no_agrupados') {
                btns[0].classList.add('active');
                btns[1].classList.remove('active');
                containerNo.classList.remove('hidden');
                containerSi.classList.add('hidden');
            } else {
                btns[0].classList.remove('active');
                btns[1].classList.add('active');
                containerNo.classList.add('hidden');
                containerSi.classList.remove('hidden');
            }
        }

        // Función para cambiar de pestaña en el desglose de explicaciones paso a paso
        function switchExplanationTab(mode, tab) {
            const tabs = ['median', 'dispersion', 'quartiles'];
            tabs.forEach(t => {
                const btn = document.getElementById(`tab-btn-${t}-${mode}`);
                const content = document.getElementById(`explanation-${t}-${mode}`);
                if (t === tab) {
                    btn.classList.add('active');
                    content.classList.remove('hidden');
                } else {
                    btn.classList.remove('active');
                    content.classList.add('hidden');
                }
            });

            // Forzar renderizado de MathJax si hay expresiones LaTeX recién inyectadas
            if (typeof renderMathInElement === 'function') {
                renderMathInElement(document.body, {
                    delimiters: [
                        {left: '$$', right: '$$', display: true},
                        {left: '$', right: '$', display: false}
                    ]
                });
            }
        }

        // Generar pasos de cálculo de Mediana No Agrupada
        function generarExplicacionMedianaNoAgrupada() {
            const exp = document.getElementById('explanation-median-ungrouped');
            const n = excelData.no_agrupados.cantidad;
            const mediana = excelData.no_agrupados.mediana;

            let html = `
                <h3>Explicación del cálculo de la Mediana (No Agrupada)</h3>
                <p>La mediana representa el valor del dato central de la muestra ordenada.</p>
                <ol>
                    <li>Ordenar los datos de menor a mayor (total de datos $n = ${n}$).</li>
            `;

            if (n % 2 !== 0) {
                const index1Based = (n + 1) / 2;
                html += `
                    <li>Dado que el número de datos es <strong>impar</strong>, la mediana se ubica exactamente en la posición central:
                        $$Me = X_{\\frac{n+1}{2}} = X_{${index1Based}}$$
                    </li>
                    <li>Buscamos el valor en la posición ${index1Based} de la serie ordenada, lo que resulta en:
                        <strong>$Me = ${formatNumber(mediana)}$</strong>.
                    </li>
                `;
            } else {
                const pos1 = n / 2;
                const pos2 = pos1 + 1;
                html += `
                    <li>Dado que el número de datos es <strong>par</strong>, la mediana es el promedio de los dos datos centrales en las posiciones $X_{${pos1}}$ y $X_{${pos2}}$:
                        $$Me = \\frac{X_{\\frac{n}{2}} + X_{\\frac{n}{2}+1}}{2} = \\frac{X_{${pos1}} + X_{${pos2}}}{2}$$
                    </li>
                    <li>Sustituyendo los valores de las posiciones centrales:
                        $$Me = \\frac{${formatNumber(excelData.no_agrupados.tabla[0]?.dato || '')} + ...}{2} = ${formatNumber(mediana)}$$
                    </li>
                `;
            }

            html += `</ol>`;
            exp.innerHTML = html;
        }

        // Generar pasos de cálculo de Varianza y Desvío No Agrupado
        function generarExplicacionVarianzaNoAgrupada() {
            const exp = document.getElementById('explanation-dispersion-ungrouped');
            const vDet = excelData.no_agrupados.varianza_detalle;
            const n = vDet.cantidad;
            const mean = vDet.media;
            const variance = vDet.resultado;
            const stdDev = excelData.no_agrupados.desviacion;
            const cv = excelData.no_agrupados.cv;

            let html = `
                <h3>Explicación de la Varianza y Desvío Estándar (No Agrupada)</h3>
                <p>La varianza muestral mide el promedio de las desviaciones al cuadrado respecto a la media aritmética:</p>
                $$S^2 = \\frac{\\sum_{i=1}^n (x_i - \\bar{x})^2}{n - 1}$$
                <ol>
                    <li>Calculamos la diferencia de cada dato con la media y sumamos sus cuadrados:
                        <ul>
                            <li>Suma de desvíos al cuadrado: $\\sum (x_i - \\bar{x})^2 = ${formatNumber(vDet.suma_desviaciones)}$</li>
                        </ul>
                    </li>
                    <li>Dividimos por la muestra menos uno ($n - 1 = ${n - 1}$):
                        $$S^2 = \\frac{${formatNumber(vDet.suma_desviaciones)}}{${n - 1}} = ${formatNumber(variance)}$$
                    </li>
                    <li>La <strong>Desviación Estándar ($S$)</strong> es la raíz cuadrada de la varianza:
                        $$S = \\sqrt{S^2} = \\sqrt{${formatNumber(variance)}} = ${formatNumber(stdDev)}$$
                    </li>
                    <li>El <strong>Coeficiente de Variación ($CV$)</strong> expresa la desviación estándar como porcentaje de la media:
                        $$CV = \\frac{S}{\\bar{x}} \\cdot 100\\% = \\frac{${formatNumber(stdDev)}}{${formatNumber(mean)}} \\cdot 100\\% = ${formatNumber(cv, 2)}\\%$$
                    </li>
                </ol>
            `;
            exp.innerHTML = html;
        }

        // Generar pasos de cálculo de Cuartiles No Agrupados
        function generarExplicacionCuartilesNoAgrupada() {
            const exp = document.getElementById('explanation-quartiles-ungrouped');
            const qDet = excelData.no_agrupados.cuartiles;
            const n = qDet.cantidad;
            const q1 = qDet.q1;
            const q3 = qDet.q3;
            const ric = qDet.ric;

            // Listar valores de cada mitad de forma limpia
            const infValores = qDet.q1_details.valores.map(v => formatNumber(v)).join(', ');
            const supValores = qDet.q3_details.valores.map(v => formatNumber(v)).join(', ');

            let html = `
                <h3>Explicación del cálculo de Cuartiles (No Agrupados)</h3>
                <p>Siguiendo el apunte de cátedra del Prof. Meana, los cuartiles se obtienen dividiendo el conjunto de datos ordenados en dos mitades (inferior y superior) y calculando la mediana de cada una:</p>
                <ul>
                    <li>Si el número de datos $n$ es <strong>par</strong>, la muestra se divide en dos partes iguales de tamaño $n/2$.</li>
                    <li>Si el número de datos $n$ es <strong>impar</strong>, excluimos el dato central (mediana) y dividimos los datos restantes en dos mitades de tamaño $(n-1)/2$.</li>
                </ul>
                <ol>
                    <li><strong>Primer Cuartil ($Q_1$)</strong>:
                        <ul>
                            <li>Es la mediana de la mitad inferior de los datos ordenados.</li>
                            <li>Mitad inferior (${qDet.q1_details.valores.length} datos): <code>[${infValores}]</code></li>
                            <li><strong>$Q_1 = ${formatNumber(q1)}$</strong> (${qDet.q1_details.pos})</li>
                        </ul>
                    </li>
                    <li><strong>Tercer Cuartil ($Q_3$)</strong>:
                        <ul>
                            <li>Es la mediana de la mitad superior de los datos ordenados.</li>
                            <li>Mitad superior (${qDet.q3_details.valores.length} datos): <code>[${supValores}]</code></li>
                            <li><strong>$Q_3 = ${formatNumber(q3)}$</strong> (${qDet.q3_details.pos})</li>
                        </ul>
                    </li>
                    <li><strong>Rango Intercuartil ($RIC$)</strong>:
                        <p>Mide el rango de dispersión del 50% central de los datos:</p>
                        $$RIC = Q_3 - Q_1 = ${formatNumber(q3)} - ${formatNumber(q1)} = ${formatNumber(ric)}$$
                    </li>
                </ol>
            `;
            exp.innerHTML = html;
        }

        // Generar pasos de cálculo de Mediana Agrupada
        function generarExplicacionMedianaAgrupada() {
            const exp = document.getElementById('explanation-median-grouped');
            const det = excelData.agrupados.mediana_detalle;
            const n = excelData.no_agrupados.cantidad;

            exp.innerHTML = `
                <h3>Explicación del cálculo de la Mediana (Datos Agrupados)</h3>
                <p>Para datos agrupados, la mediana se localiza identificando primero el intervalo donde se encuentra el valor central $\\frac{n}{2}$ y aplicando la fórmula de interpolación del apunte:</p>
                $$Me = L_i + \\left( \\frac{\\frac{n}{2} - F_{i-1}}{f_i} \\right) \\cdot a$$
                <ol>
                    <li>Calculamos el localizador central: $\\frac{n}{2} = \\frac{${n}}{2} = ${n/2}$.</li>
                    <li>Buscamos en la tabla de frecuencias el primer intervalo con frecuencia absoluta acumulada $F_i \\geq ${n/2}$. Esto localiza el <strong>Intervalo N° ${det.intervalo}</strong>.</li>
                    <li>Identificamos los parámetros del intervalo seleccionado:
                        <ul>
                            <li>Límite real inferior ($L_i$): <strong>${formatNumber(det.lim_inf)}</strong></li>
                            <li>Frecuencia acumulada del intervalo anterior ($F_{i-1}$): <strong>${det.F_anterior}</strong></li>
                            <li>Frecuencia absoluta del intervalo ($f_i$): <strong>${det.f_i}</strong></li>
                            <li>Amplitud de la clase ($a$): <strong>${formatNumber(det.amplitud)}</strong></li>
                        </ul>
                    </li>
                    <li>Sustituyendo en la ecuación:
                        $$Me = ${formatNumber(det.lim_inf)} + \\left( \\frac{${n/2} - ${det.F_anterior}}{${det.f_i}} \\right) \\cdot ${formatNumber(det.amplitud)}$$
                        $$Me = ${formatNumber(excelData.agrupados.mediana)}$$
                    </li>
                </ol>
            `;
        }

        // Generar pasos de cálculo de Varianza Agrupada
        function generarExplicacionVarianzaAgrupada() {
            const exp = document.getElementById('explanation-dispersion-grouped');
            const vDet = excelData.agrupados.varianza_detalle;
            const n = vDet.cantidad;
            const mean = vDet.media;
            const variance = vDet.resultado;
            const stdDev = excelData.agrupados.desviacion;
            const cv = excelData.agrupados.cv;

            let html = `
                <h3>Explicación de la Varianza y Desvío Estándar (Datos Agrupados)</h3>
                <p>La varianza para datos agrupados pondera las desviaciones de las marcas de clase respecto a la media agrupada por la frecuencia de cada clase:</p>
                $$S^2 = \\frac{\\sum f_i \\cdot (x_{ic} - \\bar{x})^2}{n - 1}$$
                <ol>
                    <li>Calculamos el desvío al cuadrado de cada marca de clase ($x_{ic}$), multiplicamos por su frecuencia ($f_i$) y sumamos todos estos productos:
                        <ul>
                            <li>Suma de productos de desvíos ponderados: $\\sum f_i \\cdot (x_{ic} - \\bar{x})^2 = ${formatNumber(vDet.suma_desviaciones)}$</li>
                        </ul>
                    </li>
                    <li>Dividimos por el tamaño de la muestra menos uno ($n - 1 = ${n - 1}$):
                        $$S^2 = \\frac{${formatNumber(vDet.suma_desviaciones)}}{${n - 1}} = ${formatNumber(variance)}$$
                    </li>
                    <li>La <strong>Desviación Estándar Agrupada ($S$)</strong> es la raíz de la varianza:
                        $$S = \\sqrt{S^2} = \\sqrt{${formatNumber(variance)}} = ${formatNumber(stdDev)}$$
                    </li>
                    <li>El <strong>Coeficiente de Variación Agrupado ($CV$)</strong>:
                        $$CV = \\frac{S}{\\bar{x}} \\cdot 100\\% = \\frac{${formatNumber(stdDev)}}{${formatNumber(mean)}} \\cdot 100\\% = ${formatNumber(cv, 2)}\\%$$
                    </li>
                </ol>
            `;
            exp.innerHTML = html;
        }

        // Generar pasos de cálculo de Cuartiles Agrupados
        function generarExplicacionCuartilesAgrupada() {
            const exp = document.getElementById('explanation-quartiles-grouped');
            const qDet = excelData.agrupados.cuartiles;
            const n = excelData.no_agrupados.cantidad;
            const q1 = qDet.q1;
            const q3 = qDet.q3;
            const ric = qDet.ric;

            let html = `
                <h3>Explicación de Cuartiles e Intervalo Intercuartil (Datos Agrupados)</h3>
                <p>Aplicamos la fórmula de interpolación lineal sobre las clases de los cuartiles correspondientes:</p>
                $$Q_k = L_i + \\left( \\frac{\\frac{k \\cdot n}{4} - F_{i-1}}{f_i} \\right) \\cdot a$$
                <ol>
                    <li><strong>Primer Cuartil ($Q_1$)</strong>:
                        <ul>
                            <li>Posición de búsqueda: $\\frac{n}{4} = ${formatNumber(q1.posicion)}$</li>
                            <li>Pertenece al intervalo de la Clase ${q1.clase_indice}</li>
                            <li>Interpolación:
                                $$Q_1 = ${formatNumber(q1.limite_inferior)} + \\left( \\frac{${formatNumber(q1.posicion)} - ${q1.frecuencia_acumulada_anterior}}{${q1.frecuencia_clase}} \\right) \\cdot ${formatNumber(q1.amplitud)} = ${formatNumber(q1.resultado)}$$
                            </li>
                        </ul>
                    </li>
                    <li><strong>Tercer Cuartil ($Q_3$)</strong>:
                        <ul>
                            <li>Posición de búsqueda: $\\frac{3n}{4} = ${formatNumber(q3.posicion)}$</li>
                            <li>Pertenece al intervalo de la Clase ${q3.clase_indice}</li>
                            <li>Interpolación:
                                $$Q_3 = ${formatNumber(q3.limite_inferior)} + \\left( \\frac{${formatNumber(q3.posicion)} - ${q3.frecuencia_acumulada_anterior}}{${q3.frecuencia_clase}} \\right) \\cdot ${formatNumber(q3.amplitud)} = ${formatNumber(q3.resultado)}$$
                            </li>
                        </ul>
                    </li>
                    <li><strong>Rango Intercuartil Agrupado ($RIC$)</strong>:
                        $$RIC = Q_3 - Q_1 = ${formatNumber(q3.resultado)} - ${formatNumber(q1.resultado)} = ${formatNumber(ric)}$$
                    </li>
                </ol>
            `;
            exp.innerHTML = html;
        }

        // Generar interpretaciones académicas de no agrupados (Punto C)
        function generarInterpretacionNoAgrupada() {
            const container = document.getElementById('interpretation-content-ungrouped');
            const cv = excelData.no_agrupados.cv;
            const q1 = excelData.no_agrupados.cuartiles.q1;
            const q3 = excelData.no_agrupados.cuartiles.q3;

            const isHomogeneo = cv < 30;
            const cvBadgeClass = isHomogeneo ? 'badge-success' : 'badge-warning';
            const cvBadgeText = isHomogeneo ? 'Homogéneo' : 'Heterogéneo';
            const cvRepresentationText = isHomogeneo 
                ? 'indica que la **media aritmética es altamente representativa** del conjunto de datos, ya que los valores presentan baja dispersión y gran concentración en torno al promedio.'
                : 'indica que la **media aritmética NO es completamente representativa** del conjunto de datos, ya que existe una dispersión considerable (heterogeneidad) entre los valores de la muestra.';

            container.innerHTML = `
                <div class="interpretation-item">
                    <h4>
                        <i class="fa-solid fa-chart-line"></i> Coeficiente de Variación ($CV = ${formatNumber(cv, 2)}\\%$)
                        <span class="badge ${cvBadgeClass}">${cvBadgeText}</span>
                    </h4>
                    <p>El Coeficiente de Variación mide la dispersión relativa de los datos. Un valor de **${formatNumber(cv, 2)}%** ${cvRepresentationText}</p>
                </div>
                <div class="interpretation-item">
                    <h4><i class="fa-solid fa-arrow-down-short-wide"></i> Primer Cuartil ($Q_1 = ${formatNumber(q1)}$)</h4>
                    <p>El **25%** de los datos analizados son **menores o iguales a ${formatNumber(q1)}**, mientras que el **75%** restante de la muestra presenta valores **mayores o iguales a ${formatNumber(q1)}**.</p>
                </div>
                <div class="interpretation-item">
                    <h4><i class="fa-solid fa-arrow-up-wide-short"></i> Tercer Cuartil ($Q_3 = ${formatNumber(q3)}$)</h4>
                    <p>El **75%** de los datos analizados son **menores o iguales a ${formatNumber(q3)}**, mientras que el **25%** restante de la muestra presenta valores **mayores o iguales a ${formatNumber(q3)}**.</p>
                </div>
            `;
        }

        // Generar interpretaciones académicas de agrupados (Punto C)
        function generarInterpretacionAgrupada() {
            const container = document.getElementById('interpretation-content-grouped');
            const cv = excelData.agrupados.cv;
            const q1 = excelData.agrupados.cuartiles.q1.resultado;
            const q3 = excelData.agrupados.cuartiles.q3.resultado;

            const isHomogeneo = cv < 30;
            const cvBadgeClass = isHomogeneo ? 'badge-success' : 'badge-warning';
            const cvBadgeText = isHomogeneo ? 'Homogéneo' : 'Heterogéneo';
            const cvRepresentationText = isHomogeneo 
                ? 'indica que la **media aritmética agrupada es representativa** del conjunto de datos debido a la baja variabilidad entre los intervalos de clase.'
                : 'indica que la **media aritmética agrupada NO es completamente representativa** debido a la alta variabilidad y dispersión interna de las frecuencias en los intervalos de clase.';

            container.innerHTML = `
                <div class="interpretation-item">
                    <h4>
                        <i class="fa-solid fa-chart-line"></i> Coeficiente de Variación Agrupado ($CV = ${formatNumber(cv, 2)}\\%$)
                        <span class="badge ${cvBadgeClass}">${cvBadgeText}</span>
                    </h4>
                    <p>El Coeficiente de Variación Agrupado es de **${formatNumber(cv, 2)}%**, lo cual ${cvRepresentationText}</p>
                </div>
                <div class="interpretation-item">
                    <h4><i class="fa-solid fa-arrow-down-short-wide"></i> Primer Cuartil Agrupado ($Q_1 = ${formatNumber(q1)}$)</h4>
                    <p>En la distribución agrupada por intervalos, el **25%** de la muestra acumulada se encuentra en valores **menores o iguales a ${formatNumber(q1)}**, y el **75%** restante se distribuye en valores **mayores o iguales a ${formatNumber(q1)}**.</p>
                </div>
                <div class="interpretation-item">
                    <h4><i class="fa-solid fa-arrow-up-wide-short"></i> Tercer Cuartil Agrupado ($Q_3 = ${formatNumber(q3)}$)</h4>
                    <p>En la distribución agrupada por intervalos, el **75%** de la muestra acumulada se encuentra en valores **menores o iguales a ${formatNumber(q3)}**, y el **25%** restante se distribuye en valores **mayores o iguales a ${formatNumber(q3)}**.</p>
                </div>
            `;
        }

        // Toggle del acordeón de la planilla
        function togglePlanilla() {
            const icon = document.getElementById('collapse-icon');
            const content = document.getElementById('planilla-container');
            
            icon.classList.toggle('rotated');
            content.classList.toggle('open');
        }

        // Renderizar la planilla original del Excel
        function generarPlanillaOriginal() {
            const table = document.getElementById('table-planilla');
            table.innerHTML = '';

            const filas = excelData.planilla;
            if (filas.length === 0) return;

            // Generar cabecera (columnas A, B, C...)
            const headers = Object.keys(filas[Object.keys(filas)[0]]);
            const thead = document.createElement('thead');
            const headerTr = document.createElement('tr');
            
            // Columna de números de fila
            const thIndex = document.createElement('th');
            thIndex.textContent = '#';
            headerTr.appendChild(thIndex);

            headers.forEach(h => {
                const th = document.createElement('th');
                th.textContent = h;
                headerTr.appendChild(th);
            });
            thead.appendChild(headerTr);
            table.appendChild(thead);

            // Generar cuerpo
            const tbody = document.createElement('tbody');
            let idx = 1;
            for (const key in filas) {
                const row = filas[key];
                const tr = document.createElement('tr');
                
                const tdIndex = document.createElement('td');
                tdIndex.innerHTML = `<small style="color: var(--text-secondary);">${idx++}</small>`;
                tr.appendChild(tdIndex);

                headers.forEach(h => {
                    const td = document.createElement('td');
                    td.textContent = row[h] !== null ? row[h] : '';
                    // Resaltar la celda si contiene un valor numérico analizado
                    if (row[h] !== null && !isNaN(row[h]) && row[h] !== '') {
                        td.style.backgroundColor = 'rgba(99, 102, 241, 0.1)';
                        td.style.fontWeight = 'bold';
                    }
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            }
            table.appendChild(tbody);
        }

        // Función para renderizar los gráficos (Punto D)
        function renderizarGraficos() {
            // --- 1. Gráfico de Datos No Agrupados (Barras individuales) ---
            if (chartNoAgrupados !== null) {
                chartNoAgrupados.destroy();
            }

            const ctxNo = document.getElementById('chart-no-agrupados').getContext('2d');
            const tablaNo = excelData.no_agrupados.tabla;
            const labelsNo = tablaNo.map(f => f.dato.toString());
            const dataNo = tablaNo.map(f => f.f_i);

            // Colores por defecto para no agrupados (índigo)
            const bgColorsNo = dataNo.map(() => 'rgba(99, 102, 241, 0.5)');
            const borderColorsNo = dataNo.map(() => 'rgba(99, 102, 241, 1)');

            chartNoAgrupados = new Chart(ctxNo, {
                type: 'bar',
                data: {
                    labels: labelsNo,
                    datasets: [{
                        label: 'Frecuencia Absoluta (f_i)',
                        data: dataNo,
                        backgroundColor: bgColorsNo,
                        borderColor: borderColorsNo,
                        borderWidth: 1.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: { color: 'rgba(71, 85, 105, 0.2)' },
                            ticks: { color: '#94a3b8', font: { family: 'Outfit' } }
                        },
                        y: {
                            grid: { color: 'rgba(71, 85, 105, 0.2)' },
                            ticks: { color: '#94a3b8', font: { family: 'Outfit' }, stepSize: 1 },
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: '#f8fafc', font: { family: 'Outfit', weight: 'bold' } }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleColor: '#f8fafc',
                            bodyColor: '#94a3b8',
                            borderColor: '#475569',
                            borderWidth: 1
                        }
                    }
                }
            });

            // --- 2. Histograma de Datos Agrupados (Clases) ---
            if (chartAgrupados !== null) {
                chartAgrupados.destroy();
            }

            const ctxSi = document.getElementById('chart-agrupados').getContext('2d');
            const tablaSi = excelData.agrupados.tabla;
            // Intervalo Clase como label (ej: "[40 - 50)")
            const labelsSi = tablaSi.map(f => `[${formatNumber(f.lim_inf)} - ${formatNumber(f.lim_sup)})`);
            const dataSi = tablaSi.map(f => f.f_i);

            // Resaltar la frecuencia máxima (clase modal) en verde
            const maxFi = Math.max(...dataSi);
            const bgColorsSi = [];
            const borderColorsSi = [];

            dataSi.forEach(fi => {
                if (fi === maxFi && maxFi > 0) {
                    // Resaltado en verde (Modal)
                    bgColorsSi.push('rgba(16, 185, 129, 0.6)');
                    borderColorsSi.push('rgba(16, 185, 129, 1)');
                } else {
                    // Estándar índigo
                    bgColorsSi.push('rgba(99, 102, 241, 0.5)');
                    borderColorsSi.push('rgba(99, 102, 241, 1)');
                }
            });

            chartAgrupados = new Chart(ctxSi, {
                type: 'bar',
                data: {
                    labels: labelsSi,
                    datasets: [{
                        label: 'Frecuencia Absoluta (f_i)',
                        data: dataSi,
                        backgroundColor: bgColorsSi,
                        borderColor: borderColorsSi,
                        borderWidth: 1.5,
                        // Configuración del histograma (sin espacios entre barras)
                        barPercentage: 1.0,
                        categoryPercentage: 1.0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: { color: 'rgba(71, 85, 105, 0.2)' },
                            ticks: { color: '#94a3b8', font: { family: 'Outfit' } }
                        },
                        y: {
                            grid: { color: 'rgba(71, 85, 105, 0.2)' },
                            ticks: { color: '#94a3b8', font: { family: 'Outfit' } },
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: '#f8fafc', font: { family: 'Outfit', weight: 'bold' } }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleColor: '#f8fafc',
                            bodyColor: '#94a3b8',
                            borderColor: '#475569',
                            borderWidth: 1
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
