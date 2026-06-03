<?php
session_start();
include('database/dbconfig.php');
include('./lib/my_functions.php');
require_once 'includes/email_functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($connection, $_POST['name'] ?? 'Anónimo');
    $email = mysqli_real_escape_string($connection, $_POST['email'] ?? '');
    $subject_msg = mysqli_real_escape_string($connection, $_POST['subject'] ?? 'Consulta desde Soporte');
    $message = mysqli_real_escape_string($connection, $_POST['message'] ?? '');

    if (empty($email) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'El email y el mensaje son obligatorios.']);
        exit;
    }

    $to = 'sincrm@pedrodiaz.eu';
    $subject = 'SOPORTE: ' . $subject_msg;
    
    $body = "
    <h2 style='color: #002b49;'>Nueva Consulta de Soporte</h2>
    <p>Has recibido un mensaje desde el formulario de contacto de SINCRM.</p>
    <div style='background: #f6f9ff; padding: 20px; border-radius: 12px; border: 1px solid #e0f0ff;'>
        <p><strong>Nombre:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Asunto:</strong> $subject_msg</p>
        <p><strong>Mensaje:</strong><br>" . nl2br($message) . "</p>
    </div>
    <p style='font-size: 12px; color: #73777e; margin-top: 20px;'>Este mensaje ha sido generado automáticamente por el sistema de soporte.</p>
    ";

    $mail_sent = enviar_email($to, $subject, $body);

    if ($mail_sent) {
        write_log("Consulta de soporte enviada por $email", "INFO");
        echo json_encode(['status' => 'success', 'message' => 'Tu mensaje ha sido enviado correctamente. Nos pondremos en contacto contigo pronto.']);
    } else {
        write_log("Error al enviar consulta de soporte desde $email", "ERROR");
        echo json_encode(['status' => 'error', 'message' => 'No se pudo enviar el mensaje en este momento. Por favor, inténtalo de nuevo más tarde o escribe directamente a info@fnrm.es.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>