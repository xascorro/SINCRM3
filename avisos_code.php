<?php
include('security.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'silenciar') {
    $tipo = mysqli_real_escape_string($connection, $_POST['tipo']);
    $horas = intval($_POST['horas']);
    $es_descarte = isset($_POST['es_descarte']) ? intval($_POST['es_descarte']) : 0;
    $user_id = $_SESSION['id_usario'];

    $silencio_hasta = date('Y-m-d H:i:s', strtotime("+$horas hours"));

    // Borrar previos del mismo tipo para este usuario
    mysqli_query($connection, "DELETE FROM avisos_silenciados WHERE id_usuario = '$user_id' AND tipo_aviso = '$tipo'");

    // Insertar con la marca de descarte si corresponde
    $query = "INSERT INTO avisos_silenciados (id_usuario, tipo_aviso, silencio_hasta, es_descarte) VALUES ('$user_id', '$tipo', '$silencio_hasta', '$es_descarte')";
    
    if (mysqli_query($connection, $query)) {
        echo json_encode(['status' => 'success', 'until' => $silencio_hasta]);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($connection)]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'restablecer') {
    $user_id = $_SESSION['id_usario'];
    // SOLO borramos lo que NO es descarte permanente
    $query = "DELETE FROM avisos_silenciados WHERE id_usuario = '$user_id' AND es_descarte = 0";
    
    if (mysqli_query($connection, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($connection)]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'limpiar_todo') {
    $user_id = $_SESSION['id_usario'];
    // Borramos ABSOLUTAMENTE TODO (incluidos descartes)
    $query = "DELETE FROM avisos_silenciados WHERE id_usuario = '$user_id'";
    
    if (mysqli_query($connection, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($connection)]);
    }
    exit;
}
?>