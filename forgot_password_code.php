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
        $email = trim(strtolower(mysqli_real_escape_string($connection, $_POST['email'] ?? '')));
        $query = "SELECT id, username FROM usuarios WHERE email='$email' AND activo=1";
        $result = mysqli_query($connection, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Usar DATE_ADD(NOW(), INTERVAL 15 MINUTE) directamente en la base de datos para sincronización exacta
            $update = "UPDATE usuarios SET OTP_password='$otp', OTP_expires = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE email='$email'";
            if (mysqli_query($connection, $update)) {
                $subject = "Código de verificación: $otp - SINCRM";
                $username_clean = htmlspecialchars($user_data['username'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
                
                $html = "
                <h2 style='margin: 0 0 16px 0; font-size: 22px; font-weight: 700; color: #0f172a;'>Recuperación de Contraseña</h2>
                <p style='margin: 0 0 16px 0; font-size: 16px;'>Hola <strong>{$username_clean}</strong>,</p>
                <p style='margin: 0 0 16px 0; font-size: 16px;'>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en la plataforma <strong>SINCRM</strong>.</p>
                <p style='margin: 0 0 20px 0; font-size: 16px;'>Introduce el siguiente código de 6 dígitos en la pantalla de verificación para continuar:</p>
                
                <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin: 20px 0;'>
                    <tr>
                        <td style='padding: 16px 18px; vertical-align: middle; text-align: left;'>
                            <div style='font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;'>Código de Seguridad</div>
                            <div style='font-size: 14px; color: #64748b;'>Válido durante los próximos 15 minutos</div>
                        </td>
                        <td align='right' style='padding: 16px 18px; vertical-align: middle; text-align: right;'>
                            <div style='font-family: -apple-system, BlinkMacSystemFont, \"SF Mono\", Monaco, Consolas, monospace; font-size: 34px; font-weight: 800; color: #0284c7; letter-spacing: 8px; line-height: 1; font-variant-numeric: tabular-nums; white-space: nowrap;'>{$otp}</div>
                        </td>
                    </tr>
                </table>

                <div style='padding: 12px 16px; background-color: #f8fafc; border-left: 3px solid #0284c7; border-radius: 0 8px 8px 0; font-size: 14px; color: #475569; line-height: 1.5; margin: 18px 0;'>
                    <strong>Nota:</strong> Si tú no has solicitado este cambio de contraseña, puedes ignorar este correo con total seguridad; tu cuenta permanecerá intacta.
                </div>";

                $mail_sent = send_pulse_email($email, $subject, $html);
                if ($mail_sent === true) {
                    write_log("OTP generado y enviado para $email", "INFO");
                    echo json_encode(['status' => 'success', 'message' => 'Código de verificación enviado a tu email.']);
                } else {
                    write_log("Error al enviar email OTP para $email: $mail_sent", "ERROR");
                    echo json_encode(['status' => 'error', 'message' => 'Error al enviar el email: ' . $mail_sent]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error interno en la base de datos.']);
            }
        } else {
            write_log("Intento de recuperación fallido: Email no encontrado o inactivo ($email)", "SECURITY");
            echo json_encode(['status' => 'error', 'message' => 'No se encontró ninguna cuenta activa con ese email.']);
        }
        exit;
    }

    // PASO 2: Verificar OTP
    if ($action == 'verify_otp') {
        $email = trim(strtolower(mysqli_real_escape_string($connection, $_POST['email'] ?? '')));
        $otp = trim(mysqli_real_escape_string($connection, $_POST['otp'] ?? ''));
        
        $query = "SELECT id, username, OTP_password, OTP_expires, (OTP_expires > NOW()) as es_valido FROM usuarios WHERE email='$email' AND activo=1";
        $result = mysqli_query($connection, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user_row = mysqli_fetch_assoc($result);
            if (!empty($user_row['OTP_password']) && $user_row['OTP_password'] === $otp) {
                if ($user_row['es_valido'] == 1) {
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['otp_verified'] = true;
                    write_log("OTP verificado correctamente para $email", "INFO");
                    echo json_encode(['status' => 'success', 'message' => 'Código verificado correctamente.']);
                } else {
                    write_log("Intento de verificación de OTP fallido (código caducado) para $email", "WARNING");
                    echo json_encode(['status' => 'error', 'message' => 'El código ha caducado (validez: 15 minutos). Por favor solicita uno nuevo.']);
                }
            } else {
                write_log("Intento de verificación de OTP fallido (código incorrecto) para $email", "SECURITY");
                echo json_encode(['status' => 'error', 'message' => 'El código introducido no es correcto. Revisa el email recibido.']);
            }
        } else {
            write_log("Intento de verificación de OTP fallido (usuario no encontrado) para $email", "SECURITY");
            echo json_encode(['status' => 'error', 'message' => 'No se encontró ninguna cuenta activa con ese correo.']);
        }
        exit;
    }

    // PASO 3: Restablecer Contraseña
    if ($action == 'reset_password') {
        if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || !isset($_SESSION['reset_email'])) {
            echo json_encode(['status' => 'error', 'message' => 'Sesión de recuperación no autorizada o caducada.']);
            exit;
        }
        
        $email = $_SESSION['reset_email'];
        $password = $_POST['password'] ?? '';
        $password_r = $_POST['password_r'] ?? '';
        
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
            // Limpiar variables de sesión de recuperación
            unset($_SESSION['reset_email']);
            unset($_SESSION['otp_verified']);
            
            // Enviar email de confirmación con diseño claro
            $subject_confirm = "Contraseña actualizada correctamente - SINCRM";
            $login_url = "https://" . ($_SERVER['HTTP_HOST'] ?? 'sincrm.pedrodiaz.eu') . "/login.php";
            
            $html_confirm = "
            <h2 style='margin: 0 0 16px 0; font-size: 22px; font-weight: 700; color: #0f172a;'>Contraseña Actualizada</h2>
            <p style='margin: 0 0 16px 0; font-size: 16px;'>Hola,</p>
            <p style='margin: 0 0 18px 0; font-size: 16px;'>Te confirmamos que la contraseña de tu cuenta en <strong>SINCRM</strong> se ha actualizado con éxito.</p>
            <div style='padding: 14px 18px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; font-size: 14px; color: #166534; line-height: 1.5; margin: 20px 0;'>
                Ya puedes iniciar sesión con tu nueva contraseña desde cualquier dispositivo.
            </div>
            <div style='text-align: center; margin: 28px 0 10px 0;'>
                <a href='{$login_url}' style='display: inline-block; background-color: #0284c7; color: #ffffff; font-weight: 600; font-size: 15px; text-decoration: none; padding: 13px 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);'>Acceder a mi Cuenta</a>
            </div>";
            
            send_pulse_email($email, $subject_confirm, $html_confirm);
            write_log("Contraseña restablecida con éxito para $email", "SUCCESS");
            
            echo json_encode(['status' => 'success', 'message' => 'Tu contraseña ha sido actualizada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar la contraseña en la base de datos.']);
        }
        exit;
    }
}
?>