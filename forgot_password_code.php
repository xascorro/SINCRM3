<?php
session_start();
include('database/dbconfig.php');
require_once 'includes/email_functions.php';

/**
 * Función centralizada para enviar emails vía Pulse Hub API
 */
function send_pulse_email($to, $subject, $html) {
    return enviar_email($to, $subject, $html);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // PASO 1: Solicitar OTP
    if ($action == 'request_otp') {
        $email = mysqli_real_escape_string($connection, $_POST['email']);
        $query = "SELECT id, username FROM usuarios WHERE email='$email' AND activo=1";
        $result = mysqli_query($connection, $query);

        if (mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date("Y-m-d H:i:s", strtotime("+15 minutes"));
            
            $update = "UPDATE usuarios SET OTP_password='$otp', OTP_expires='$expires' WHERE email='$email'";
            if (mysqli_query($connection, $update)) {
                $subject = "Código de Seguridad - SINCRM";
                $html = "
                <h2>Recuperación de Contraseña</h2>
                <p>Hola <strong>".$user_data['username']."</strong>,</p>
                <p>Has solicitado restablecer tu contraseña. Utiliza el siguiente código para continuar con el proceso:</p>
                <div class='otp-box'>
                    <p style='margin-bottom: 10px; font-size: 12px; font-weight: bold; color: #64748b; text-transform: uppercase;'>Tu código es:</p>
                    <h1 class='otp-code'>$otp</h1>
                </div>
                <div class='alert-box alert-warning'>
                    Este código caducará en 15 minutos por motivos de seguridad.
                </div>
                <p>Si no has solicitado este cambio, puedes ignorar este correo con total seguridad.</p>";

                $mail_sent = send_pulse_email($email, $subject, $html);
                if ($mail_sent === true) {
                    write_log("OTP generado y enviado para $email", "INFO");
                    echo json_encode(['status' => 'success', 'message' => 'Código de verificación enviado a tu email.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al enviar el email: ' . $mail_sent]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error de base de datos.']);
            }
        } else {
            write_log("Intento de recuperación fallido: Email no encontrado o inactivo ($email)", "SECURITY");
            echo json_encode(['status' => 'error', 'message' => 'No se encontró ninguna cuenta activa con ese email.']);
        }
        exit;
    }

    // PASO 2: Verificar OTP
    if ($action == 'verify_otp') {
        $email = mysqli_real_escape_string($connection, $_POST['email']);
        $otp = mysqli_real_escape_string($connection, $_POST['otp']);
        
        $query = "SELECT id FROM usuarios WHERE email='$email' AND OTP_password='$otp' AND OTP_expires > NOW() AND activo=1";
        $result = mysqli_query($connection, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $_SESSION['reset_email'] = $email;
            $_SESSION['otp_verified'] = true;
            write_log("OTP verificado correctamente para $email", "INFO");
            echo json_encode(['status' => 'success', 'message' => 'Código verificado correctamente.']);
        } else {
            write_log("Intento de verificación de OTP fallido para $email", "SECURITY");
            echo json_encode(['status' => 'error', 'message' => 'Código inválido o caducado. Inténtalo de nuevo.']);
        }
        exit;
    }

    // PASO 3: Restablecer Contraseña
    if ($action == 'reset_password') {
        if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || !isset($_SESSION['reset_email'])) {
            echo json_encode(['status' => 'error', 'message' => 'Sesión no autorizada o caducada.']);
            exit;
        }
        
        $email = $_SESSION['reset_email'];
        $password = $_POST['password'];
        $password_r = $_POST['password_r'];
        
        if (strlen($password) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
            exit;
        }

        if ($password !== $password_r) {
            echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden.']);
            exit;
        }
        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $update = "UPDATE usuarios SET hash='$hash', OTP_password=NULL, OTP_expires=NULL WHERE email='$email'";
        
        if (mysqli_query($connection, $update)) {
            // Enviar email de confirmación de éxito
            $subject_confirm = "Contraseña Actualizada Correctamente - SINCRM";
            $html_confirm = "
            <h2 style='color: #10b981;'>¡Contraseña Cambiada!</h2>
            <p>Hola,</p>
            <p>Te informamos que la contraseña de tu cuenta en <strong>SINCRM</strong> ha sido actualizada correctamente.</p>
            <div class='alert-box alert-info'>
                Si no has realizado este cambio, por favor ponte en contacto con nosotros inmediatamente.
            </div>
            <div style='text-align: center; margin-top: 30px;'>
                <a href='https://" . $_SERVER['HTTP_HOST'] . "/login.php' class='btn'>Acceder a mi Cuenta</a>
            </div>";
            
            send_pulse_email($email, $subject_confirm, $html_confirm);
            
            write_log("Contraseña restablecida con éxito para $email", "SUCCESS");
            unset($_SESSION['reset_email']);
            unset($_SESSION['otp_verified']);
            echo json_encode(['status' => 'success', 'message' => 'Tu contraseña ha sido actualizada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la contraseña en la base de datos.']);
        }
        exit;
    }
}
?>