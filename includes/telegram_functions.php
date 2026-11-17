<?php
/**
 * Biblioteca Central de Integración con Telegram para SINCRM
 * Manejo de API, Webhooks, Envíos Push y Registro de Auditoría
 */

if (!function_exists('get_sincrm_config')) {
    function get_sincrm_config($clave, $default = null) {
        global $connection;
        static $config_cache = [];

        if (isset($config_cache[$clave])) {
            return $config_cache[$clave];
        }

        if (!$connection) {
            return $default;
        }

        $safe_clave = mysqli_real_escape_string($connection, $clave);
        $res = mysqli_query($connection, "SELECT valor FROM configuracion_sistema WHERE clave = '$safe_clave' LIMIT 1");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            $config_cache[$clave] = $row['valor'];
            return $row['valor'];
        }

        return $default;
    }
}

if (!function_exists('set_sincrm_config')) {
    function set_sincrm_config($clave, $valor, $descripcion = null) {
        global $connection;
        if (!$connection) return false;

        $safe_clave = mysqli_real_escape_string($connection, $clave);
        $safe_valor = mysqli_real_escape_string($connection, $valor);
        $safe_desc = $descripcion ? "'" . mysqli_real_escape_string($connection, $descripcion) . "'" : "NULL";

        $sql = "INSERT INTO configuracion_sistema (clave, valor, descripcion) 
                VALUES ('$safe_clave', '$safe_valor', $safe_desc) 
                ON DUPLICATE KEY UPDATE valor = '$safe_valor'";
        if ($descripcion) {
            $sql .= ", descripcion = $safe_desc";
        }

        return mysqli_query($connection, $sql);
    }
}

/**
 * Llamada genérica a la API de Telegram
 */
function telegram_api_call($method, $params = [], $is_multipart = false) {
    $token = get_sincrm_config('telegram_bot_token', '');
    if (empty($token)) {
        return ['ok' => false, 'description' => 'Token de Telegram no configurado'];
    }

    $url = "https://api.telegram.org/bot{$token}/{$method}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    if (!empty($params)) {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($is_multipart) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error) {
        return ['ok' => false, 'description' => 'cURL Error: ' . $error];
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        return ['ok' => false, 'description' => "Respuesta no JSON ($http_code): " . substr($response, 0, 200)];
    }

    return $data;
}

/**
 * Registrar mensaje en log de auditoría
 */
function telegram_log_mensaje($id_usuario, $chat_id, $tipo_evento, $mensaje, $estado, $error_info = null) {
    global $connection;
    if (!$connection) return;

    $id_user_sql = $id_usuario ? intval($id_usuario) : "NULL";
    $safe_chat = mysqli_real_escape_string($connection, strval($chat_id));
    $safe_tipo = mysqli_real_escape_string($connection, substr($tipo_evento, 0, 50));
    $safe_msg = mysqli_real_escape_string($connection, $mensaje);
    $safe_estado = in_array($estado, ['ENVIADO', 'ERROR']) ? $estado : 'ERROR';
    $safe_error = $error_info ? "'" . mysqli_real_escape_string($connection, substr($error_info, 0, 500)) . "'" : "NULL";

    @mysqli_query($connection, "INSERT INTO telegram_mensajes_log 
        (id_usuario, chat_id, tipo_evento, mensaje, estado, error_info) 
        VALUES ($id_user_sql, '$safe_chat', '$safe_tipo', '$safe_msg', '$safe_estado', $safe_error)");
}

/**
 * Enviar mensaje de texto con soporte HTML / Markdown y teclado interactivo
 */
function telegram_enviar_mensaje($chat_id, $texto, $inline_keyboard = null, $parse_mode = 'HTML', $tipo_evento = 'NOTIFICACION') {
    if (get_sincrm_config('telegram_notif_enabled', '1') !== '1') {
        return ['ok' => false, 'description' => 'Notificaciones globales desactivadas en el sistema'];
    }

    if (empty($chat_id)) {
        return ['ok' => false, 'description' => 'Chat ID vacío'];
    }

    $payload = [
        'chat_id' => $chat_id,
        'text' => $texto,
        'parse_mode' => $parse_mode,
        'disable_web_page_preview' => false
    ];

    if (!empty($inline_keyboard)) {
        $payload['reply_markup'] = [
            'inline_keyboard' => $inline_keyboard
        ];
    }

    $res = telegram_api_call('sendMessage', $payload);

    $estado = (!empty($res['ok'])) ? 'ENVIADO' : 'ERROR';
    $err = (!empty($res['description'])) ? $res['description'] : null;
    telegram_log_mensaje(null, $chat_id, $tipo_evento, $texto, $estado, $err);

    return $res;
}

/**
 * Enviar documento (PDF, ZIP, etc.) a un chat
 */
function telegram_enviar_documento($chat_id, $file_path, $caption = "", $custom_filename = null, $tipo_evento = 'DOCUMENTO') {
    if (get_sincrm_config('telegram_notif_enabled', '1') !== '1') {
        return ['ok' => false, 'description' => 'Notificaciones globales desactivadas'];
    }

    if (!file_exists($file_path)) {
        return ['ok' => false, 'description' => 'Archivo no encontrado: ' . $file_path];
    }

    $filename = $custom_filename ?: basename($file_path);
    $cfile = new CURLFile(realpath($file_path), 'application/octet-stream', $filename);

    $params = [
        'chat_id' => $chat_id,
        'document' => $cfile,
        'caption' => $caption,
        'parse_mode' => 'HTML'
    ];

    $res = telegram_api_call('sendDocument', $params, true);

    $estado = (!empty($res['ok'])) ? 'ENVIADO' : 'ERROR';
    $err = (!empty($res['description'])) ? $res['description'] : null;
    telegram_log_mensaje(null, $chat_id, $tipo_evento, "Documento: $filename | $caption", $estado, $err);

    return $res;
}

/**
 * Notificar a un usuario concreto por su ID de usuario en SINCRM
 */
function telegram_notificar_usuario($id_usuario, $mensaje, $inline_keyboard = null, $parse_mode = 'HTML', $tipo_evento = 'NOTIFICACION_USUARIO') {
    global $connection;
    $id_usuario = intval($id_usuario);
    if ($id_usuario <= 0) return ['ok' => false, 'description' => 'ID de usuario inválido'];

    $q = mysqli_query($connection, "SELECT telegram_chat_id, telegram_notif_activas FROM usuarios WHERE id = '$id_usuario' LIMIT 1");
    if (!$q || mysqli_num_rows($q) == 0) {
        return ['ok' => false, 'description' => 'Usuario no encontrado'];
    }

    $user = mysqli_fetch_assoc($q);
    if (empty($user['telegram_chat_id'])) {
        return ['ok' => false, 'description' => 'Usuario no tiene Telegram vinculado'];
    }

    if (intval($user['telegram_notif_activas'] ?? 1) !== 1) {
        return ['ok' => false, 'description' => 'El usuario ha silenciado sus notificaciones de Telegram'];
    }

    return telegram_enviar_mensaje($user['telegram_chat_id'], $mensaje, $inline_keyboard, $parse_mode, $tipo_evento);
}

/**
 * Notificar a todos los usuarios vinculados de un Club específico
 */
function telegram_notificar_club($id_club, $mensaje, $inline_keyboard = null, $tipo_evento = 'NOTIFICACION_CLUB') {
    global $connection;
    $id_club = intval($id_club);
    $q = mysqli_query($connection, "SELECT id, telegram_chat_id FROM usuarios WHERE club = '$id_club' AND activo = 1 AND telegram_chat_id IS NOT NULL AND telegram_notif_activas = 1");
    $enviados = 0;
    while ($row = mysqli_fetch_assoc($q)) {
        $res = telegram_enviar_mensaje($row['telegram_chat_id'], $mensaje, $inline_keyboard, 'HTML', $tipo_evento);
        if (!empty($res['ok'])) $enviados++;
    }
    return ['ok' => true, 'enviados' => $enviados];
}

/**
 * Notificar a un Juez específico por su ID de Juez v3
 */
function telegram_notificar_juez($id_juez, $mensaje, $inline_keyboard = null, $tipo_evento = 'NOTIFICACION_JUEZ') {
    global $connection;
    $id_juez = intval($id_juez);
    $q = mysqli_query($connection, "SELECT id, telegram_chat_id FROM usuarios WHERE id_juez_v3 = '$id_juez' AND activo = 1 AND telegram_chat_id IS NOT NULL AND telegram_notif_activas = 1 LIMIT 1");
    if ($q && $row = mysqli_fetch_assoc($q)) {
        return telegram_enviar_mensaje($row['telegram_chat_id'], $mensaje, $inline_keyboard, 'HTML', $tipo_evento);
    }
    return ['ok' => false, 'description' => 'Juez no vinculado a Telegram'];
}

/**
 * Enviar broadcast a un rol completo (ej: todos los clubes o todos los jueces)
 */
function telegram_notificar_rol($id_rol, $mensaje, $inline_keyboard = null, $tipo_evento = 'BROADCAST_ROL') {
    global $connection;
    $id_rol = intval($id_rol);
    $q = mysqli_query($connection, "SELECT id, telegram_chat_id FROM usuarios WHERE id_rol = '$id_rol' AND activo = 1 AND telegram_chat_id IS NOT NULL AND telegram_notif_activas = 1");
    $enviados = 0;
    while ($row = mysqli_fetch_assoc($q)) {
        $res = telegram_enviar_mensaje($row['telegram_chat_id'], $mensaje, $inline_keyboard, 'HTML', $tipo_evento);
        if (!empty($res['ok'])) $enviados++;
    }
    return ['ok' => true, 'enviados' => $enviados];
}

/**
 * Registrar o actualizar Webhook con Telegram
 */
function telegram_setup_webhook($webhook_url, $secret_token = null) {
    $params = [
        'url' => $webhook_url,
        'allowed_updates' => ['message', 'callback_query'],
        'drop_pending_updates' => false
    ];
    if (!empty($secret_token)) {
        $params['secret_token'] = $secret_token;
    }
    return telegram_api_call('setWebhook', $params);
}

/**
 * Consultar estado actual del Webhook
 */
function telegram_get_webhook_info() {
    return telegram_api_call('getWebhookInfo');
}

/**
 * Eliminar el Webhook de Telegram
 */
function telegram_delete_webhook() {
    return telegram_api_call('deleteWebhook');
}

/**
 * Obtener datos del Bot (getMe)
 */
function telegram_get_bot_info() {
    return telegram_api_call('getMe');
}

/**
 * Configurar foto de perfil oficial del Bot
 */
function telegram_set_bot_photo($file_path) {
    if (!file_exists($file_path)) {
        return ['ok' => false, 'description' => 'Archivo no encontrado: ' . $file_path];
    }
    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : 'image/png';
    $cfile = new CURLFile(realpath($file_path), $mime, basename($file_path));
    $params = [
        'photo' => json_encode(['type' => 'static', 'photo' => 'attach://avatar']),
        'avatar' => $cfile
    ];
    return telegram_api_call('setMyProfilePhoto', $params, true);
}

