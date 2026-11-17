<?php
/**
 * Webhook Oficial de Telegram para SINCRM
 * Procesa comandos de usuarios, vinculación 1-clic y consultas rápidas
 */

// Evitar bloqueos de sesión y establecer entorno
ignore_user_abort(true);
set_time_limit(30);

require_once(__DIR__ . '/database/dbconfig.php');
require_once(__DIR__ . '/includes/telegram_functions.php');

// 1. Validar encabezado secreto de Telegram si está configurado
$secret_config = get_sincrm_config('telegram_webhook_secret', '');
if (!empty($secret_config)) {
    $header_secret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
    if ($header_secret !== $secret_config) {
        http_response_code(403);
        echo json_encode(['error' => 'Acceso denegado: Secret token no coincide']);
        exit();
    }
}

// 2. Leer payload entrante
$raw_input = file_get_contents('php://input');
if (empty($raw_input)) {
    http_response_code(200);
    echo "Webhook activo. Esperando eventos.";
    exit();
}

$update = json_decode($raw_input, true);
if (!is_array($update)) {
    http_response_code(200);
    exit();
}

// 3. Procesar mensajes
$message = $update['message'] ?? $update['edited_message'] ?? null;
if (!$message || empty($message['chat']['id'])) {
    http_response_code(200);
    exit();
}

$chat_id = $message['chat']['id'];
$chat_type = $message['chat']['type'] ?? 'private';
$from_username = $message['from']['username'] ?? '';
$from_first_name = $message['from']['first_name'] ?? '';
$text = trim($message['text'] ?? '');

// Responder siempre 200 a Telegram de inmediato
http_response_code(200);
echo json_encode(['status' => 'received']);

// Solo procesar chats privados para seguridad del usuario
if ($chat_type !== 'private') {
    exit();
}

// Identificar si este chat_id ya pertenece a un usuario
$q_user = mysqli_query($connection, "SELECT u.*, r.nombre as rol_nombre, c.nombre as club_nombre, c.nombre_corto as club_corto 
                                     FROM usuarios u 
                                     LEFT JOIN roles r ON u.id_rol = r.id 
                                     LEFT JOIN clubes c ON u.club = c.id 
                                     WHERE u.telegram_chat_id = '$chat_id' AND u.activo = 1 LIMIT 1");
$current_user = ($q_user && mysqli_num_rows($q_user) > 0) ? mysqli_fetch_assoc($q_user) : null;

// LÓGICA DE COMANDOS

// COMANDO: /start [token]
if (strpos($text, '/start') === 0) {
    $parts = explode(' ', $text);
    $token = trim($parts[1] ?? '');

    if (!empty($token)) {
        // Intento de vinculación 1-clic con token
        $safe_token = mysqli_real_escape_string($connection, $token);
        $q_token = mysqli_query($connection, "SELECT u.*, r.nombre as rol_nombre, c.nombre as club_nombre, c.nombre_corto as club_corto 
                                              FROM usuarios u 
                                              LEFT JOIN roles r ON u.id_rol = r.id 
                                              LEFT JOIN clubes c ON u.club = c.id 
                                              WHERE u.telegram_link_token = '$safe_token' 
                                                AND u.telegram_token_expires >= NOW() 
                                                AND u.activo = 1 LIMIT 1");

        if ($q_token && mysqli_num_rows($q_token) > 0) {
            $user = mysqli_fetch_assoc($q_token);
            $user_id = $user['id'];
            $safe_username = mysqli_real_escape_string($connection, $from_username);

            // Asociar chat_id y limpiar token
            mysqli_query($connection, "UPDATE usuarios SET 
                telegram_chat_id = '$chat_id', 
                telegram_username = '$safe_username', 
                telegram_link_token = NULL, 
                telegram_token_expires = NULL, 
                telegram_notif_activas = 1 
                WHERE id = '$user_id'");

            write_log("Telegram Bot: Usuario #$user_id ({$user['username']}) vinculó exitosamente chat_id $chat_id (@$from_username)", "SUCCESS");

            $nombre = htmlspecialchars($user['username'] ?: $from_first_name);
            $rol = htmlspecialchars($user['rol_nombre'] ?: 'Usuario');
            $club = htmlspecialchars($user['club_corto'] ?: ($user['club_nombre'] ?: 'Federación / General'));

            $msg = "🏊‍♂️ <b>¡Cuenta vinculada con éxito a SINCRM!</b>\n\n";
            $msg .= "Hola <b>$nombre</b>, tu cuenta de SINCRM ha quedado conectada a este chat.\n\n";
            $msg .= "📋 <b>Detalles de tu cuenta:</b>\n";
            $msg .= "• <b>Email:</b> <code>" . htmlspecialchars($user['email']) . "</code>\n";
            $msg .= "• <b>Rol:</b> $rol\n";
            $msg .= "• <b>Club / Entidad:</b> $club\n\n";
            $msg .= "🔔 A partir de ahora recibirás aquí avisos oficiales de competiciones, órdenes de salida, avisos de músicas y resultados en tiempo real.\n\n";
            $msg .= "Escribe /ayuda para ver los comandos disponibles.";

            $keyboard = [
                [
                    ['text' => '🌐 Abrir SINCRM Web', 'url' => 'https://sincrm.pedrodiaz.eu/index.php'],
                    ['text' => '🏆 Ver Resultados', 'callback_data' => 'ver_resultados']
                ]
            ];

            telegram_enviar_mensaje($chat_id, $msg, $keyboard, 'HTML', 'VINCULACION_EXITOSA');
            exit();
        } else {
            // Token inválido o caducado
            $msg = "⚠️ <b>Enlace de vinculación no válido o caducado</b>\n\n";
            $msg .= "El token de vinculación ya ha sido utilizado o han transcurrido más de 15 minutos desde su generación.\n\n";
            $msg .= "👉 Vuelve a tu <b>Perfil en SINCRM Web</b> (https://sincrm.pedrodiaz.eu/perfil.php) y pulsa en <b>'Conectar con Telegram'</b> para generar un enlace nuevo.";

            telegram_enviar_mensaje($chat_id, $msg, null, 'HTML', 'VINCULACION_FALLIDA');
            exit();
        }
    }

    // /start sin parámetros
    if ($current_user) {
        $nombre = htmlspecialchars($current_user['username'] ?: $from_first_name);
        $msg = "👋 <b>¡Hola de nuevo, $nombre!</b>\n\n";
        $msg .= "Tu cuenta de SINCRM ya se encuentra vinculada y activa.\n\n";
        $msg .= "📌 <b>Comandos disponibles:</b>\n";
        $msg .= "• /ayuda - Ver menú de opciones y estado\n";
        if ($current_user['id_rol'] == 5) {
            $msg .= "• /misrutinas - Ver rutinas y música de mi club\n";
        }
        if (!empty($current_user['id_juez_v3']) || in_array($current_user['id_rol'], [1, 2, 3, 4])) {
            $msg .= "• /mispaneles - Consultar paneles asignados\n";
        }
        $msg .= "• /resultados - Consultar resultados recientes\n";
        $msg .= "• /desvincular - Desconectar este chat de SINCRM";

        $keyboard = [
            [['text' => '🌐 Panel Principal', 'url' => 'https://sincrm.pedrodiaz.eu/index.php']]
        ];

        telegram_enviar_mensaje($chat_id, $msg, $keyboard, 'HTML', 'START_EXISTENTE');
        exit();
    } else {
        $msg = "🏊‍♀️ <b>Bienvenido al Bot Oficial de SINCRM</b>\n\n";
        $msg .= "Este bot permite a clubes, jueces y secretaría recibir notificaciones oficiales en tiempo real sobre competiciones de natación artística.\n\n";
        $msg .= "🔒 <b>Para vincular tu cuenta:</b>\n";
        $msg .= "1. Inicia sesión en https://sincrm.pedrodiaz.eu\n";
        $msg .= "2. Entra en <b>Mi Perfil</b>\n";
        $msg .= "3. Pulsa el botón <b>'Conectar con Telegram'</b>\n\n";
        $msg .= "El enlace abrirá automáticamente este chat y dejará tu usuario registrado.";

        $keyboard = [
            [['text' => '🔗 Ir a Mi Perfil en SINCRM', 'url' => 'https://sincrm.pedrodiaz.eu/perfil.php']]
        ];

        telegram_enviar_mensaje($chat_id, $msg, $keyboard, 'HTML', 'START_NUEVO');
        exit();
    }
}

// Si no está vinculado y envía cualquier otro texto
if (!$current_user) {
    $msg = "ℹ️ Tu cuenta de Telegram no está vinculada a ningún usuario en SINCRM.\n\n";
    $msg .= "Por favor, accede a tu perfil en https://sincrm.pedrodiaz.eu/perfil.php y pulsa en <b>'Conectar con Telegram'</b> para comenzar a recibir avisos.";
    telegram_enviar_mensaje($chat_id, $msg, null, 'HTML', 'NO_VINCULADO');
    exit();
}

// COMANDO: /ayuda
if ($text === '/ayuda') {
    $nombre = htmlspecialchars($current_user['username']);
    $rol = htmlspecialchars($current_user['rol_nombre'] ?? 'Usuario');

    $msg = "🤖 <b>Centro de Ayuda SINCRM</b>\n";
    $msg .= "Usuario: <b>$nombre</b> ($rol)\n\n";
    $msg .= "Comandos disponibles:\n";
    $msg .= "• /resultados - Ver últimas actas y resultados oficiales\n";

    if ($current_user['id_rol'] == 5) {
        $msg .= "• /misrutinas - Estado de rutinas y música de tu club\n";
    }
    if (!empty($current_user['id_juez_v3']) || in_array($current_user['id_rol'], [1, 2, 3, 4])) {
        $msg .= "• /mispaneles - Paneles técnicos asignados\n";
    }
    $msg .= "• /desvincular - Desconectar tu cuenta de este chat\n\n";
    $msg .= "<i>Las notificaciones de cierres de plazo y avisos oficiales se envían automáticamente sin necesidad de solicitarlas.</i>";

    telegram_enviar_mensaje($chat_id, $msg, null, 'HTML', 'AYUDA');
    exit();
}

// COMANDO: /resultados
if ($text === '/resultados') {
    $q_res = mysqli_query($connection, "SELECT id, nombre, fecha FROM competiciones WHERE fecha <= CURDATE() ORDER BY fecha DESC, id DESC LIMIT 5");
    $msg = "🏆 <b>Últimos Resultados y Competiciones:</b>\n\n";
    $keyboard = [];

    while ($c = mysqli_fetch_assoc($q_res)) {
        $fecha = date("d/m/Y", strtotime($c['fecha']));
        $nombre = htmlspecialchars($c['nombre']);
        $msg .= "• <b>$nombre</b> ($fecha)\n";
        $keyboard[] = [
            ['text' => "📄 Ver {$c['nombre']}", 'url' => "https://sincrm.pedrodiaz.eu/informes_competicion.php?id_competicion={$c['id']}"]
        ];
    }

    telegram_enviar_mensaje($chat_id, $msg, $keyboard, 'HTML', 'CONSULTA_RESULTADOS');
    exit();
}

// COMANDO: /misrutinas (Solo para rol Club)
if ($text === '/misrutinas') {
    $club_id = intval($current_user['club'] ?? 0);
    if ($club_id <= 0) {
        telegram_enviar_mensaje($chat_id, "ℹ️ No tienes un club asignado en tu cuenta.", null, 'HTML', 'MISRUTINAS');
        exit();
    }

    // Próxima competición
    $q_next = mysqli_query($connection, "SELECT id, nombre, fecha FROM competiciones WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 1");
    if (!$q_next || mysqli_num_rows($q_next) == 0) {
        telegram_enviar_mensaje($chat_id, "ℹ️ No hay competiciones programadas próximas.", null, 'HTML', 'MISRUTINAS');
        exit();
    }

    $next_comp = mysqli_fetch_assoc($q_next);
    $id_comp = $next_comp['id'];

    $q_rut = mysqli_query($connection, "SELECT r.*, m.nombre as modalidad_nom, c.nombre as categoria_nom, f.nombre as fase_nom 
                                        FROM rutinas r 
                                        LEFT JOIN modalidades m ON r.modalidad = m.id 
                                        LEFT JOIN categorias c ON r.categoria = c.id 
                                        LEFT JOIN fases f ON r.fase = f.id 
                                        WHERE f.competicion = '$id_comp' AND r.club = '$club_id' 
                                        ORDER BY f.orden, r.orden");

    $msg = "🏊‍♀️ <b>Rutinas de tu Club para:</b>\n";
    $msg .= "<b>" . htmlspecialchars($next_comp['nombre']) . "</b> (" . date("d/m/Y", strtotime($next_comp['fecha'])) . ")\n\n";

    if (mysqli_num_rows($q_rut) == 0) {
        $msg .= "No hay rutinas inscritas para tu club en esta competición.";
    } else {
        while ($r = mysqli_fetch_assoc($q_rut)) {
            $has_music = !empty($r['musica']) && file_exists(__DIR__ . '/' . $r['musica']);
            $music_icon = $has_music ? "🎵 OK" : "⚠️ <b>FALTA MÚSICA</b>";
            $orden = ($r['orden'] > 0) ? "#{$r['orden']}" : "Sin orden";
            $msg .= "• <b>{$r['modalidad_nom']}</b> ({$r['categoria_nom']}) - $orden | $music_icon\n";
        }
    }

    $keyboard = [
        [['text' => '📂 Subir Música / Gestionar', 'url' => 'https://sincrm.pedrodiaz.eu/rutinas.php']]
    ];

    telegram_enviar_mensaje($chat_id, $msg, $keyboard, 'HTML', 'MISRUTINAS');
    exit();
}

// COMANDO: /mispaneles (Para jueces)
if ($text === '/mispaneles') {
    $id_juez = intval($current_user['id_juez_v3'] ?? 0);
    if ($id_juez <= 0) {
        telegram_enviar_mensaje($chat_id, "ℹ️ Tu cuenta de usuario no está asociada a un perfil oficial de Juez.", null, 'HTML', 'MISPANELES');
        exit();
    }

    $q_next = mysqli_query($connection, "SELECT id, nombre, fecha FROM competiciones WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 1");
    if (!$q_next || mysqli_num_rows($q_next) == 0) {
        telegram_enviar_mensaje($chat_id, "ℹ️ No hay competiciones activas próximas.", null, 'HTML', 'MISPANELES');
        exit();
    }

    $next_comp = mysqli_fetch_assoc($q_next);
    $id_comp = $next_comp['id'];

    $q_pan = mysqli_query($connection, "SELECT pj.*, pt.nombre as panel_nombre, f.nombre as fase_nombre 
                                        FROM panel_jueces pj 
                                        LEFT JOIN paneles p ON pj.id_panel = p.id 
                                        LEFT JOIN paneles_tipo pt ON p.id_tipo = pt.id 
                                        LEFT JOIN fases f ON p.id_fase = f.id 
                                        WHERE f.competicion = '$id_comp' AND pj.id_juez = '$id_juez'");

    $msg = "⚖️ <b>Tus Paneles Asignados:</b>\n";
    $msg .= "<b>" . htmlspecialchars($next_comp['nombre']) . "</b>\n\n";

    if (!$q_pan || mysqli_num_rows($q_pan) == 0) {
        $msg .= "Aún no se han configurado paneles para tu juez en este evento.";
    } else {
        while ($p = mysqli_fetch_assoc($q_pan)) {
            $msg .= "• <b>Fase:</b> {$p['fase_nombre']} | <b>Panel:</b> {$p['panel_nombre']} (Puesto {$p['posicion']})\n";
        }
    }

    telegram_enviar_mensaje($chat_id, $msg, null, 'HTML', 'MISPANELES');
    exit();
}

// COMANDO: /desvincular
if ($text === '/desvincular') {
    $user_id = $current_user['id'];
    mysqli_query($connection, "UPDATE usuarios SET 
        telegram_chat_id = NULL, 
        telegram_username = NULL, 
        telegram_notif_activas = 0 
        WHERE id = '$user_id'");

    write_log("Telegram Bot: Usuario #$user_id ({$current_user['username']}) desvinculó su cuenta desde Telegram", "INFO");

    $msg = "🔌 <b>Cuenta Desvinculada</b>\n\n";
    $msg .= "Tu cuenta de SINCRM ha sido desconectada de este chat de Telegram.\n";
    $msg .= "Ya no recibirás alertas ni notificaciones aquí. Puedes volver a vincularte cuando quieras desde tu perfil web.";

    telegram_enviar_mensaje($chat_id, $msg, null, 'HTML', 'DESVINCULACION');
    exit();
}

// Respuesta por defecto para mensajes no reconocidos
$msg = "ℹ️ No reconozco ese comando.\nEscribe /ayuda para ver las opciones disponibles.";
telegram_enviar_mensaje($chat_id, $msg, null, 'HTML', 'COMANDO_DESCONOCIDO');
