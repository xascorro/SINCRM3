<?php
// Informe Global de Puntuaciones (Wrapper de compatibilidad v4)
if (!isset($_GET['titulo'])) {
    $_GET['titulo'] = 'Clasificación Detallada Global';
}
require_once(__DIR__ . '/informe_puntuaciones.php');
