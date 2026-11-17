<?php
/**
 * Controlador AJAX para Vinculación 1-Clic de Telegram y Notificaciones de Usuario
 */

header('Content-Type: application/json; charset=utf-8');

include('security.php');
include('database/dbconfig.php');
include('includes/telegram_functions.php');

$user_id = intval($_SESSION['id_usario'] ?? 0);
if ($user_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión no válida o no autenticada']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// 1. GENERAR TOKEN DE VINCULACIÓN 1-CLIC
if ($action === 'generar_token') {
    $token = bin2hex(random_bytes(16)); // 32 caracteres seguros
    $bot_username = ltrim(get_sincrm_config('telegram_bot_username', 'SincrmBot'), '@');

    $update = mysqli_query($connection, "UPDATE usuarios SET 
        telegram_link_token = '$token', 
        telegram_token_expires = DATE_ADD(NOW(), INTERVAL 15 MINUTE) 
        WHERE id = '$user_id'");

    if ($update) {
        $deep_link = "https://t.me/{$bot_username}?start={$token}";
        echo json_encode([
            'status' => 'success',
            'token' => $token,
            'bot_username' => $bot_username,
            'link' => $deep_link
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar el token de vinculación']);
    }
    exit();
}

// 2. COMPROBAR ESTADO EN TIEMPO REAL (Polling suave desde Perfil)
if ($action === 'comprobar_estado') {
    $q = mysqli_query($connection, "SELECT telegram_chat_id, telegram_username, telegram_notif_activas, telegram_prefs 
                                    FROM usuarios WHERE id = '$user_id' LIMIT 1");
    if ($q && $user = mysqli_fetch_assoc($q)) {
        $vinculado = !empty($user['telegram_chat_id']);
        echo json_encode([
            'status' => 'success',
            'vinculado' => $vinculado,
            'chat_id' => $user['telegram_chat_id'] ?? null,
            'telegram_username' => $user['telegram_username'] ?? '',
            'notif_activas' => intval($user['telegram_notif_activas'] ?? 1),
            'prefs' => json_decode($user['telegram_prefs'] ?: '{}', true)
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
    }
    exit();
}

// 3. ENVIAR MENSAJE DE PRUEBA
if ($action === 'probar_mensaje') {
    $q = mysqli_query($connection, "SELECT username, telegram_chat_id, telegram_notif_activas FROM usuarios WHERE id = '$user_id' LIMIT 1");
    $user = ($q) ? mysqli_fetch_assoc($q) : null;

    if (empty($user['telegram_chat_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Aún no has vinculado tu cuenta de Telegram.']);
        exit();
    }

    $hora = date('d/m/Y H:i:s');
    $nombre = htmlspecialchars($user['username'] ?: 'Usuario');

    $msg = "🔔 <b>Prueba de Notificación de SINCRM</b>\n\n";
    $msg .= "¡Hola, <b>$nombre</b>! Tu canal de avisos en Telegram está perfectamente conectado y listo para recibir alertas en tiempo real.\n\n";
    $msg .= "⏱️ <i>Verificado el $hora</i>";

    $keyboard = [
        [['text' => '🌐 Ir a Mi Panel', 'url' => 'https://sincrm.pedrodiaz.eu/index.php']]
    ];

    $res = telegram_enviar_mensaje($user['telegram_chat_id'], $msg, $keyboard, 'HTML', 'PRUEBA_USUARIO');

    if (!empty($res['ok'])) {
        echo json_encode(['status' => 'success', 'message' => '¡Mensaje de prueba enviado con éxito a tu Telegram!']);
    } else {
        $err = $res['description'] ?? 'Error desconocido de Telegram';
        echo json_encode(['status' => 'error', 'message' => "Fallo al enviar mensaje: $err"]);
    }
    exit();
}

// 4. ACTUALIZAR PREFERENCIAS Y SWITCH GENERAL
if ($action === 'guardar_prefs') {
    $notif_activas = isset($_POST['notif_activas']) && $_POST['notif_activas'] == '1' ? 1 : 0;
    $prefs_array = [
        'competiciones' => isset($_POST['pref_competiciones']) && $_POST['pref_competiciones'] == '1',
        'cierres' => isset($_POST['pref_cierres']) && $_POST['pref_cierres'] == '1',
        'musica' => isset($_POST['pref_musica']) && $_POST['pref_musica'] == '1',
        'resultados' => isset($_POST['pref_resultados']) && $_POST['pref_resultados'] == '1',
        'paneles' => isset($_POST['pref_paneles']) && $_POST['pref_paneles'] == '1'
    ];
    $prefs_json = mysqli_real_escape_string($connection, json_encode($prefs_array));

    $update = mysqli_query($connection, "UPDATE usuarios SET 
        telegram_notif_activas = '$notif_activas', 
        telegram_prefs = '$prefs_json' 
        WHERE id = '$user_id'");

    if ($update) {
        echo json_encode(['status' => 'success', 'message' => 'Preferencias actualizadas correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar preferencias']);
    }
    exit();
}

// 5. DESVINCULAR CUENTA DE TELEGRAM
if ($action === 'desvincular') {
    $update = mysqli_query($connection, "UPDATE usuarios SET 
        telegram_chat_id = NULL, 
        telegram_username = NULL, 
        telegram_link_token = NULL, 
        telegram_token_expires = NULL, 
        telegram_notif_activas = 0 
        WHERE id = '$user_id'");

    if ($update) {
        write_log("Telegram: Usuario #$user_id desvinculó su cuenta voluntariamente desde el perfil web", "INFO");
        echo json_encode(['status' => 'success', 'message' => 'Tu cuenta ha sido desvinculada de Telegram']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al desvincular la cuenta']);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
