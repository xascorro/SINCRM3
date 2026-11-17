<?php
include('security.php');

$user_id = intval($_SESSION['id_usario'] ?? ($_SESSION['id_usuario'] ?? 0));

// =========================================================================
// 1. SOLICITUD DE CÓDIGO DE VERIFICACIÓN / CONFIRMACIÓN (AJAX)
// =========================================================================
if (isset($_POST['action']) && in_array($_POST['action'], ['enviar_codigo_verificacion', 'enviar_verificacion'], true)) {
    require_once('includes/email_functions.php');
    header('Content-Type: application/json');

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Sesión expirada o no válida.']);
        exit();
    }

    $q = mysqli_query($connection, "SELECT id, username, email, email_confirmado FROM usuarios WHERE id = '$user_id' LIMIT 1");
    if (!$q || mysqli_num_rows($q) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado.']);
        exit();
    }

    $user = mysqli_fetch_assoc($q);
    $email = trim($user['email'] ?? '');
    $username = $user['username'] ?: 'Usuario';

    if (empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Tu perfil no tiene ninguna dirección de correo configurada.']);
        exit();
    }

    if (!empty($user['email_confirmado'])) {
        echo json_encode(['status' => 'info', 'message' => 'Tu dirección de email ya está verificada.']);
        exit();
    }

    // Generar código numérico de 6 dígitos y token seguro
    $codigo_otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $update = mysqli_query($connection, "UPDATE usuarios SET 
        token_confirmacion = '$token', 
        OTP_password = '$codigo_otp', 
        OTP_expires = '$expires' 
        WHERE id = '$user_id'");

    if (!$update) {
        echo json_encode(['status' => 'error', 'message' => 'Error técnico al generar el código de verificación.']);
        exit();
    }

    $link = "https://sincrm.pedrodiaz.eu/login_code.php?confirmar_email=" . $token;
    $subject = "Tu código de confirmación: {$codigo_otp} - SINCRM";
    $cuerpo = "
        <div style='font-family:-apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,sans-serif; max-width:580px; margin:0 auto; padding:20px; color:#1e293b;'>
            <div style='background:linear-gradient(135deg, #2563eb 0%, #1e40af 100%); padding:24px; border-radius:16px; color:#ffffff; margin-bottom:20px;'>
                <h1 style='margin:0 0 6px 0; font-size:20px; font-weight:800;'>Verificación de Correo Electrónico</h1>
                <p style='margin:0; font-size:13px; opacity:0.9;'>Plataforma Oficial SINCRM</p>
            </div>
            <div style='background-color:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-bottom:20px;'>
                <p style='margin:0 0 12px 0; font-size:14px; color:#334155;'>Hola <strong>" . htmlspecialchars($username) . "</strong>,</p>
                <p style='margin:0 0 16px 0; font-size:13px; color:#64748b; line-height:1.6;'>
                    Introduce el siguiente código de 6 dígitos en tu pantalla de perfil para verificar tu correo (<code>" . htmlspecialchars($email) . "</code>) y activar las notificaciones oficiales:
                </p>
                
                <div style='background:#f8fafc; border:2px dashed #93c5fd; border-radius:14px; padding:20px; margin:24px 0; text-align:center;'>
                    <span style='font-family:ui-monospace,SFMono-Regular,Consolas,monospace; font-size:36px; font-weight:900; letter-spacing:10px; color:#1d4ed8; display:inline-block;'>{$codigo_otp}</span>
                    <p style='font-size:11px; color:#64748b; margin:8px 0 0 0; font-weight:600;'>Código válido durante 15 minutos</p>
                </div>

                <div style='text-align:center; margin:24px 0;'>
                    <p style='font-size:12px; color:#94a3b8; margin:0 0 10px 0;'>O si lo prefieres, pulsa directamente en este botón:</p>
                    <a href='$link' style='background:#2563eb; color:#ffffff; padding:12px 24px; text-decoration:none; border-radius:12px; font-size:12px; font-weight:800; display:inline-block;'>
                        ✓ Confirmar en un Clic
                    </a>
                </div>
            </div>
            <p style='font-size:11px; color:#94a3b8; text-align:center; margin:0;'>
                Si no has solicitado este código, puedes ignorar este correo de forma segura.
            </p>
        </div>
    ";

    $envio = enviar_email($email, $subject, $cuerpo);

    if ($envio === true) {
        write_log("Código de verificación OTP enviado a $email (#$user_id)", "INFO");
        echo json_encode([
            'status' => 'success', 
            'email' => $email,
            'message' => 'Hemos enviado un código de 6 dígitos a ' . $email . '. Revisa tu bandeja de entrada o spam.'
        ]);
    } else {
        write_log("Error al enviar código OTP a $email: " . json_encode($envio), "ERROR");
        echo json_encode(['status' => 'error', 'message' => 'No se pudo enviar el correo con el código. Inténtalo de nuevo en unos minutos.']);
    }
    exit();
}


// =========================================================================
// 2. VALIDAR CÓDIGO OTP Y ACTIVAR NOTIFICACIONES (AJAX)
// =========================================================================
if (isset($_POST['action']) && $_POST['action'] === 'validar_codigo_y_activar') {
    header('Content-Type: application/json');

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Sesión expirada o no válida.']);
        exit();
    }

    $codigo_introducido = trim($_POST['codigo'] ?? '');
    if (empty($codigo_introducido) || strlen($codigo_introducido) !== 6) {
        echo json_encode(['status' => 'error', 'message' => 'Debes introducir el código completo de 6 dígitos.']);
        exit();
    }

    $q = mysqli_query($connection, "SELECT id, email, OTP_password, OTP_expires FROM usuarios WHERE id = '$user_id' LIMIT 1");
    if (!$q || mysqli_num_rows($q) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado.']);
        exit();
    }

    $user = mysqli_fetch_assoc($q);
    $otp_db = trim($user['OTP_password'] ?? '');
    $otp_exp = $user['OTP_expires'] ?? '1970-01-01 00:00:00';
    $email = $user['email'] ?? '';

    // Comprobar expiración
    if (strtotime($otp_exp) < time()) {
        echo json_encode(['status' => 'error', 'message' => 'El código ha caducado (duración 15 min). Por favor, solicita uno nuevo.']);
        exit();
    }

    // Comprobar código
    if ($codigo_introducido !== $otp_db) {
        echo json_encode(['status' => 'error', 'message' => 'El código introducido no es correcto. Comprueba los 6 dígitos.']);
        exit();
    }

    // Código válido: Activar email verificado Y activar recibir_emails
    $update = mysqli_query($connection, "UPDATE usuarios SET 
        email_confirmado = 1, 
        recibir_emails = 1, 
        OTP_password = NULL, 
        OTP_expires = NULL, 
        token_confirmacion = NULL 
        WHERE id = '$user_id'");

    if ($update) {
        write_log("Email confirmado y notificaciones activadas con código para $email (#$user_id)", "SUCCESS");
        echo json_encode([
            'status' => 'success', 
            'message' => '¡Email verificado correctamente y recepción de notificaciones activada!'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error técnico al actualizar el estado.']);
    }
    exit();
}


// =========================================================================
// 3. CAMBIO RÁPIDO DE PREFERENCIA DE EMAILS (AJAX para usuarios ya verificados)
// =========================================================================
if (isset($_POST['action']) && $_POST['action'] === 'toggle_recibir_emails') {
    header('Content-Type: application/json');

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Sesión expirada.']);
        exit();
    }

    $recibir = (isset($_POST['recibir']) && in_array($_POST['recibir'], ['1', 'true', 1], true)) ? 1 : 0;

    $q = mysqli_query($connection, "SELECT email_confirmado FROM usuarios WHERE id = '$user_id' LIMIT 1");
    $u = mysqli_fetch_assoc($q);

    if ($recibir === 1 && empty($u['email_confirmado'])) {
        echo json_encode([
            'status' => 'requiere_verificacion', 
            'message' => 'Para activar las notificaciones por email primero debes verificar tu dirección de correo.'
        ]);
        exit();
    }

    mysqli_query($connection, "UPDATE usuarios SET recibir_emails = '$recibir' WHERE id = '$user_id'");
    echo json_encode([
        'status' => 'success', 
        'recibir' => $recibir, 
        'message' => ($recibir === 1) ? 'Notificaciones por email activadas.' : 'Notificaciones por email desactivadas.'
    ]);
    exit();
}


// =========================================================================
// 4. ACTUALIZACIÓN GENERAL DEL PERFIL (Formulario)
// =========================================================================
if(isset($_POST['update_profile_btn'])){
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $email = mysqli_real_escape_string($connection, trim($_POST['email']));
    $telefono = mysqli_real_escape_string($connection, $_POST['telefono']);
    $new_password = $_POST['new_password'];
    $r_new_password = $_POST['r_new_password'];

    // Validar duplicidad de email (si se cambió)
    $check_email = mysqli_query($connection, "SELECT id FROM usuarios WHERE email = '$email' AND id != '$user_id'");
    if(mysqli_num_rows($check_email) > 0){
        $_SESSION['estado'] = "El correo electrónico ya está registrado por otro usuario.";
        header('Location: perfil.php');
        exit();
    }

    // Detectar si el email ha cambiado respecto al actual
    $cur_user_q = mysqli_query($connection, "SELECT email, email_confirmado FROM usuarios WHERE id = '$user_id' LIMIT 1");
    $cur_user = mysqli_fetch_assoc($cur_user_q) ?: [];
    $email_actual = strtolower(trim($cur_user['email'] ?? ''));
    $email_nuevo = strtolower(trim($email));
    $email_ha_cambiado = ($email_actual !== $email_nuevo && !empty($email_actual));

    $recibir_emails = (isset($_POST['recibir_emails']) && $_POST['recibir_emails'] == '1') ? 1 : 0;
    
    // Si no está verificado o si cambia de email, no se puede forzar recibir_emails sin verificar
    if (($recibir_emails == 1 && empty($cur_user['email_confirmado'])) || $email_ha_cambiado) {
        $recibir_emails = 0;
    }

    $query_parts = [
        "username = '$username'",
        "email = '$email'",
        "telefono = '$telefono'",
        "recibir_emails = '$recibir_emails'"
    ];

    $token_nuevo = null;
    $codigo_nuevo = null;
    if ($email_ha_cambiado) {
        $token_nuevo = bin2hex(random_bytes(32));
        $codigo_nuevo = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expires_nuevo = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $query_parts[] = "email_confirmado = 0";
        $query_parts[] = "token_confirmacion = '$token_nuevo'";
        $query_parts[] = "OTP_password = '$codigo_nuevo'";
        $query_parts[] = "OTP_expires = '$expires_nuevo'";
    }

    // Lógica de contraseña
    if(!empty($new_password)){
        if(strlen($new_password) < 6){
            $_SESSION['estado'] = "La nueva contraseña debe tener al menos 6 caracteres.";
            header('Location: perfil.php');
            exit();
        }
        if($new_password !== $r_new_password){
            $_SESSION['estado'] = "Las contraseñas no coinciden.";
            header('Location: perfil.php');
            exit();
        }
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $query_parts[] = "hash = '$hashed'";
    }

    // Lógica de subida de foto
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)){
            $target_dir = "images/users/";
            if(!is_dir($target_dir)){
                mkdir($target_dir, 0777, true);
            }
            $new_filename = "user_" . $user_id . "_" . time() . "." . $ext;
            $target_file = $target_dir . $new_filename;
            
            if(move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)){
                $query_parts[] = "foto = '$target_file'";
                $_SESSION['foto'] = $target_file;
            } else {
                write_log("Error al mover la foto subida de perfil", "ERROR");
            }
        } else {
            $_SESSION['estado'] = "Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP.";
            header('Location: perfil.php');
            exit();
        }
    }

    $sql = "UPDATE usuarios SET " . implode(", ", $query_parts) . " WHERE id = '$user_id'";
    $query_run = mysqli_query($connection, $sql);

    if($query_run){
        $_SESSION['username'] = $email;
        $_SESSION['email'] = $email;
        
        write_log("Perfil actualizado por el usuario #$user_id ($email)", "INFO");
        
        if ($email_ha_cambiado && !empty($token_nuevo)) {
            require_once('includes/email_functions.php');
            $link = "https://sincrm.pedrodiaz.eu/login_code.php?confirmar_email=" . $token_nuevo;
            $subject = "Confirma tu nuevo email: {$codigo_nuevo} - SINCRM";
            $cuerpo_nuevo = "
                <div style='font-family:-apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,sans-serif; max-width:580px; margin:0 auto; padding:20px; color:#1e293b;'>
                    <div style='background:linear-gradient(135deg, #2563eb 0%, #1e40af 100%); padding:24px; border-radius:16px; color:#ffffff; margin-bottom:20px;'>
                        <h1 style='margin:0 0 6px 0; font-size:20px; font-weight:800;'>Cambio de Correo Electrónico</h1>
                        <p style='margin:0; font-size:13px; opacity:0.9;'>Plataforma Oficial SINCRM</p>
                    </div>
                    <div style='background-color:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-bottom:20px;'>
                        <p style='margin:0 0 12px 0; font-size:14px; color:#334155;'>Hola <strong>" . htmlspecialchars($username) . "</strong>,</p>
                        <p style='margin:0 0 16px 0; font-size:13px; color:#64748b; line-height:1.6;'>
                            Has actualizado tu dirección a <code>" . htmlspecialchars($email) . "</code>. Introduce este código en tu perfil para confirmarla:
                        </p>
                        <div style='background:#f8fafc; border:2px dashed #93c5fd; border-radius:14px; padding:20px; margin:24px 0; text-align:center;'>
                            <span style='font-family:monospace; font-size:36px; font-weight:900; letter-spacing:10px; color:#1d4ed8;'>{$codigo_nuevo}</span>
                        </div>
                        <div style='text-align:center; margin:20px 0;'>
                            <a href='$link' style='background:#2563eb; color:#ffffff; padding:12px 24px; text-decoration:none; border-radius:12px; font-size:12px; font-weight:800; display:inline-block;'>
                                ✓ Confirmar Nueva Dirección
                            </a>
                        </div>
                    </div>
                </div>
            ";
            enviar_email($email, $subject, $cuerpo_nuevo);
            $_SESSION['correcto'] = "Perfil actualizado. Como has cambiado tu email, te hemos enviado un código de verificación a $email.";
        } else {
            $_SESSION['correcto'] = "Tu perfil ha sido actualizado correctamente.";
        }
    } else {
        write_log("Error al actualizar perfil: " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = "Error técnico al actualizar el perfil.";
    }
    header('Location: perfil.php');
    exit();
}
?>