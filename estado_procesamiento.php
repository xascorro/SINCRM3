<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['mensajes_procesamiento']) && is_array($_SESSION['mensajes_procesamiento'])) {
    echo json_encode($_SESSION['mensajes_procesamiento']);
} else {
    echo json_encode([]);
}
?>
