<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Log de eventos</h1>

        <pre><code>
        <?php
        // Ruta al archivo de log
        $archivo_log = './log/log.txt';

        // Número de líneas por página
        $lineas_por_pagina = 100;

        // Obtener la página actual desde la URL (si existe)
        $pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;

        // Función para leer el log, invertirlo y paginarlo
        function leer_log($archivo, $lineas_por_pagina, $pagina_actual) {
            // Leer el archivo completo en un array
            $lineas = file($archivo);

            // Invertir el array completo
            $lineas = array_reverse($lineas);

            // Calcular el total de páginas (basado en el array invertido)
            $total_paginas = ceil(count($lineas) / $lineas_por_pagina);

            // Calcular el índice inicial y final para la página actual
            $inicio = ($pagina_actual - 1) * $lineas_por_pagina;
            $fin = min($inicio + $lineas_por_pagina, count($lineas));

            // Obtener el fragmento deseado del array invertido
            $pagina_actual_lineas = array_slice($lineas, $inicio, $lineas_por_pagina);

            // Mostrar las líneas de la página actual
            foreach ($pagina_actual_lineas as $linea) {
                echo "<p>" . htmlspecialchars($linea) . "</p>";
            }

            // Crear los enlaces de paginación con Bootstrap
            echo '<div class="text-center">';
            echo '<nav aria-label="Page navigation">';
            echo '<ul class="pagination">';

            // Botón "Anterior"
            if ($pagina_actual > 1) {
                echo '<li class="page-item"><a class="page-link" href="?pagina=' . ($pagina_actual - 1) . '">Anterior</a></li>';
            }

            // Números de página
            for ($i = 1; $i <= $total_paginas; $i++) {
                $clase = ($i == $pagina_actual) ? 'active' : '';
                echo '<li class="page-item ' . $clase . '"><a class="page-link" href="?pagina=' . $i . '">' . $i . '</a></li>';
            }

            // Botón "Siguiente"
            if ($pagina_actual < $total_paginas) {
                echo '<li class="page-item"><a class="page-link" href="?pagina=' . ($pagina_actual + 1) . '">Siguiente</a></li>';
            }

            echo '</ul>';
            echo '</nav>';
            echo '</div>';
        }

        leer_log($archivo_log, $lineas_por_pagina, $pagina_actual);
        ?>
        </code></pre>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
