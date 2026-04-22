<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';
require_once __DIR__ . '/../includes/config.php';

/**
 * Función universal para enviar emails
 */
function enviar_email($destinatario, $asunto, $cuerpo, $adjunto = null) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor (Modo GoDaddy Relay)
        $mail->isSMTP();
        $mail->Host       = EMAIL_HOST;
        $mail->SMTPAuth   = (EMAIL_PASS != ''); // Solo si hay password
        $mail->Username   = EMAIL_USER;
        $mail->Password   = EMAIL_PASS;
        $mail->Port       = EMAIL_PORT;
        $mail->SMTPDebug  = SMTP::DEBUG_OFF;
        $mail->Timeout    = 10; // 10 segundos máximo para conectar
        $mail->SMTPKeepAlive = false; 

        // Si es GoDaddy localhost, a veces hay que desactivar SMTPSecure
        if (EMAIL_HOST == 'localhost' || EMAIL_PORT == 25) {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        }

        // Destinatarios
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($destinatario);

        // Adjuntos
        if ($adjunto && file_exists($adjunto)) {
            $mail->addAttachment($adjunto);
        }

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo;
        $mail->AltBody = strip_tags($cuerpo);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Podríamos loguear el error aquí
        error_log("Error al enviar email: " . $mail->ErrorInfo);
        return "Error: " . $mail->ErrorInfo;
    }
}
?>