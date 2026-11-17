<?php
/**
 * SINCRM v4 - Automatización Integral de Eventos y Plazos de Competición
 * Ejecución vía CRON: 00:15 (Cierres técnicos) y 08:30 (Avisos matutinos de Apertura y Sorteo)
 * 
 * Gestiona y notifica por Telegram (@sincrm_bot) y Email:
 * 1. Cierre de Inscripciones (con generación de PDF oficial, guardado en docs/ y envío Telegram + Email)
 * 2. Cierre de Coach Cards (aviso de fin de plazo para tarjetas técnicas)
 * 3. Cierre de Músicas (aviso de fin de plazo para subida de audio)
 * 4. Apertura de Inscripciones (aviso de inicio de inscripciones con calendario de plazos)
 * 5. Día del Sorteo (aviso matutino con enlace a Google Meet/Teams para unirse en directo)
 */

// Simular entorno web para los scripts de informes
$_SESSION['id_rol'] = 1; 

include(__DIR__ . '/database/dbconfig.php');
include(__DIR__ . '/includes/email_functions.php');
include_once(__DIR__ . '/includes/telegram_functions.php');

$hoy = date('Y-m-d');
$hora = (int)date('H');
$is_cli = (php_sapi_name() === 'cli');
$test_mode = (isset($_GET['test']) || in_array('--test', $argv ?? []));

if ($is_cli) {
    echo "=== SINCRM AUTOMATIZACIÓN DE EVENTOS ===\n";
    echo "Fecha: $hoy | Hora: " . date('H:i') . " | Modo: " . ($test_mode ? "TEST (Simulación)" : "PRODUCCIÓN") . "\n";
}

/**
 * Comprobar si ya se ha enviado esta notificación hoy al usuario para evitar duplicidades
 */
function ya_notificado_hoy($connection, $chat_id, $tipo_evento) {
    $safe_chat = mysqli_real_escape_string($connection, strval($chat_id));
    $safe_tipo = mysqli_real_escape_string($connection, $tipo_evento);
    $q = mysqli_query($connection, "SELECT id FROM telegram_mensajes_log 
                                    WHERE chat_id = '$safe_chat' 
                                      AND tipo_evento = '$safe_tipo' 
                                      AND estado = 'ENVIADO' 
                                      AND DATE(creado) = CURDATE() 
                                    LIMIT 1");
    return ($q && mysqli_num_rows($q) > 0);
}

/**
 * Formatear fecha a texto en español
 */
function fecha_es($date_str) {
    if (empty($date_str)) return '';
    $ts = strtotime($date_str);
    $meses = [1=>'enero', 2=>'febrero', 3=>'marzo', 4=>'abril', 5=>'mayo', 6=>'junio',
              7=>'julio', 8=>'agosto', 9=>'septiembre', 10=>'octubre', 11=>'noviembre', 12=>'diciembre'];
    $dia = date('j', $ts);
    $mes = $meses[(int)date('n', $ts)] ?? '';
    $anio = date('Y', $ts);
    return "$dia de $mes de $anio";
}

/**
 * Obtener usuarios con Telegram activo y sus preferencias
 */
function get_usuarios_telegram($connection) {
    $users = [];
    $q = mysqli_query($connection, "SELECT id, username, email, telegram_chat_id, telegram_prefs 
                                    FROM usuarios 
                                    WHERE telegram_chat_id IS NOT NULL 
                                      AND telegram_notif_activas = 1 
                                      AND activo = 1");
    if ($q) {
        while ($u = mysqli_fetch_assoc($q)) {
            $u['prefs'] = json_decode($u['telegram_prefs'] ?? '{}', true) ?: [];
            $users[] = $u;
        }
    }
    return $users;
}

$tg_users = get_usuarios_telegram($connection);
if ($is_cli) {
    echo "Usuarios Telegram activos encontrados: " . count($tg_users) . "\n";
}

// =========================================================================
// 1. CIERRE DE INSCRIPCIONES (date_add(fecha, interval -dias_fin_inscripcion day) = '$hoy')
// =========================================================================
$q_cierres = "SELECT id, nombre, figuras, fecha, dias_fin_inscripcion 
              FROM competiciones 
              WHERE date_add(fecha, interval -dias_fin_inscripcion day) = '$hoy'";
$res_cierres = mysqli_query($connection, $q_cierres);

if ($res_cierres && mysqli_num_rows($res_cierres) > 0) {
    while ($comp = mysqli_fetch_assoc($res_cierres)) {
        $id_comp = $comp['id'];
        $nombre_comp = $comp['nombre'];
        $is_figuras = ($comp['figuras'] == 'si');
        $tipo_evento = "CIERRE_INSCRIPCIONES_{$id_comp}";

        write_log("Cron Eventos: Procesando CIERRE INSCRIPCIONES de '$nombre_comp' (#$id_comp)", "INFO");
        if ($is_cli) echo "[CIERRE INSCRIPCIÓN] Procesando #$id_comp - $nombre_comp\n";

        // Generar PDF mediante llamada interna
        $cron_token = "internal_cron_" . md5("SincrmInternalCron" . date('Y-m-d'));
        $report_url = "http://localhost/informes/informe_figuras.php?id_competicion=$id_comp&titulo=Inscripciones&cron_token=$cron_token";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $report_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Host: sincrm.pedrodiaz.eu"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $pdf_content = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $pdf_file = __DIR__ . "/docs/{$id_comp}-inscripciones.pdf";
        if ($http_code == 200 && !empty($pdf_content)) {
            if (!is_dir(__DIR__ . '/docs')) {
                @mkdir(__DIR__ . '/docs', 0775, true);
            }
            @file_put_contents($pdf_file, $pdf_content);
            @chmod($pdf_file, 0664);

            // Enviar por Email a superadmin
            if (!$test_mode) {
                $destinatario = "xascorro@gmail.com";
                $asunto = "🔴 CIERRE INSCRIPCIONES: $nombre_comp";
                $cuerpo = "<h2>Notificación Automática de Cierre</h2>
                           <p>El plazo de inscripción para la competición <strong>$nombre_comp</strong> ha finalizado hoy.</p>
                           <p>Se adjunta el listado oficial de inscripciones capturado en el momento del cierre.</p>
                           <ul>
                            <li><strong>ID Competición:</strong> #$id_comp</li>
                            <li><strong>Fecha Cierre:</strong> $hoy</li>
                            <li><strong>Tipo:</strong> " . ($is_figuras ? 'Figuras' : 'Rutinas') . "</li>
                           </ul>";

                $token = "hub_mail_947d82b3c2e1";
                $api_url = "https://pulse.pedrodiaz.eu/mail/send_resend.php";
                $attachment_data = [
                    [
                        'filename' => "Inscripciones_" . str_replace(' ', '_', $nombre_comp) . ".pdf",
                        'content' => base64_encode($pdf_content)
                    ]
                ];

                $post_fields = [
                    'from' => "SINCRM <sincrm@pedrodiaz.eu>",
                    'to' => $destinatario,
                    'subject' => $asunto,
                    'html' => wrap_email_template($asunto, $cuerpo),
                    'attachments' => json_encode($attachment_data)
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api_url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-HUB-TOKEN: $token"]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $api_res = curl_exec($ch);
                curl_close($ch);
                write_log("Cron Eventos: Email enviado para '$nombre_comp'. Respuesta API: $api_res", "SUCCESS");
            }
        }

        // Enviar por Telegram adjuntando el PDF
        if (file_exists($pdf_file)) {
            $tg_caption = "🔴 <b>CIERRE OFICIAL DE INSCRIPCIONES</b>\n\n";
            $tg_caption .= "Ha finalizado el plazo de inscripción para:\n";
            $tg_caption .= "🏊 <b>" . htmlspecialchars($nombre_comp) . "</b>\n";
            $tg_caption .= "📅 <i>Captura definitiva a fecha " . fecha_es($hoy) . "</i>\n\n";
            $tg_caption .= "Se adjunta el listado oficial completo de inscripciones capturado en el momento del cierre.";

            $tg_enviados = 0;
            foreach ($tg_users as $tgu) {
                if (isset($tgu['prefs']['cierres']) && !$tgu['prefs']['cierres']) continue;
                if (!$test_mode && ya_notificado_hoy($connection, $tgu['telegram_chat_id'], $tipo_evento)) continue;

                if (!$test_mode) {
                    $t_res = telegram_enviar_documento($tgu['telegram_chat_id'], $pdf_file, $tg_caption, basename($pdf_file), $tipo_evento);
                    if (!empty($t_res['ok'])) $tg_enviados++;
                } else {
                    $tg_enviados++;
                }
            }
            write_log("Cron Eventos: Telegram Cierre Inscripciones enviado a $tg_enviados usuarios (#$id_comp)", "INFO");
        }
    }
}


// =========================================================================
// 2. CIERRE DE COACH CARDS (date_add(c.fecha, interval -c.dias_coach_card day) = '$hoy')
// =========================================================================
$q_cc = "SELECT c.id, c.nombre, c.fecha, c.dias_coach_card 
         FROM competiciones c
         WHERE date_add(c.fecha, interval -c.dias_coach_card day) = '$hoy'
           AND c.id IN (SELECT DISTINCT id_competicion FROM fases WHERE elementos_coach_card > 0)";
$res_cc = mysqli_query($connection, $q_cc);

if ($res_cc && mysqli_num_rows($res_cc) > 0) {
    while ($comp = mysqli_fetch_assoc($res_cc)) {
        $id_comp = $comp['id'];
        $nombre_comp = $comp['nombre'];
        $tipo_evento = "CIERRE_COACH_CARD_{$id_comp}";

        write_log("Cron Eventos: Procesando CIERRE COACH CARDS de '$nombre_comp' (#$id_comp)", "INFO");
        if ($is_cli) echo "[CIERRE COACH CARDS] Procesando #$id_comp - $nombre_comp\n";

        $tg_texto = "📋 <b>CIERRE PLAZO DE COACH CARDS</b>\n\n";
        $tg_texto .= "Ha finalizado el plazo para la presentación y modificación de tarjetas técnicas (Coach Cards) para:\n";
        $tg_texto .= "🏊 <b>" . htmlspecialchars($nombre_comp) . "</b>\n";
        $tg_texto .= "📅 <i>Cierre definitivo a fecha " . fecha_es($hoy) . "</i>\n\n";
        $tg_texto .= "⚠️ A partir de este momento las tarjetas quedan bloqueadas para revisión técnica por parte del Comité de Jueces y Controladores de Sincronización.";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📋 Consultar Coach Cards', 'url' => 'https://sincrm.pedrodiaz.eu/coach_card.php']
                ]
            ]
        ];

        $tg_enviados = 0;
        foreach ($tg_users as $tgu) {
            if (isset($tgu['prefs']['musica']) && !$tgu['prefs']['musica']) continue;
            if (!$test_mode && ya_notificado_hoy($connection, $tgu['telegram_chat_id'], $tipo_evento)) continue;

            if (!$test_mode) {
                $t_res = telegram_enviar_mensaje($tgu['telegram_chat_id'], $tg_texto, $keyboard, 'HTML', $tipo_evento);
                if (!empty($t_res['ok'])) $tg_enviados++;
            } else {
                $tg_enviados++;
            }
        }
        write_log("Cron Eventos: Telegram Cierre Coach Cards enviado a $tg_enviados usuarios (#$id_comp)", "INFO");
    }
}


// =========================================================================
// 3. CIERRE DE MÚSICAS (date_add(fecha, interval -dias_musica day) = '$hoy')
// =========================================================================
$q_musica = "SELECT id, nombre, fecha, dias_musica, figuras 
             FROM competiciones 
             WHERE figuras != 'si' 
               AND dias_musica > 0 
               AND date_add(fecha, interval -dias_musica day) = '$hoy'";
$res_musica = mysqli_query($connection, $q_musica);

if ($res_musica && mysqli_num_rows($res_musica) > 0) {
    while ($comp = mysqli_fetch_assoc($res_musica)) {
        $id_comp = $comp['id'];
        $nombre_comp = $comp['nombre'];
        $tipo_evento = "CIERRE_MUSICA_{$id_comp}";

        write_log("Cron Eventos: Procesando CIERRE MÚSICAS de '$nombre_comp' (#$id_comp)", "INFO");
        if ($is_cli) echo "[CIERRE MÚSICA] Procesando #$id_comp - $nombre_comp\n";

        $tg_texto = "🎵 <b>CIERRE PLAZO DE MÚSICAS</b>\n\n";
        $tg_texto .= "Ha finalizado el plazo oficial para la subida de archivos de música para:\n";
        $tg_texto .= "🏊 <b>" . htmlspecialchars($nombre_comp) . "</b>\n";
        $tg_texto .= "📅 <i>Cierre definitivo a fecha " . fecha_es($hoy) . "</i>\n\n";
        $tg_texto .= "🎧 La organización procederá a la descarga del paquete oficial estructurado para la megafonía y control de sonido.";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🎵 Ver Ficheros de Música', 'url' => "https://sincrm.pedrodiaz.eu/informes_competicion.php?id={$id_comp}"]
                ]
            ]
        ];

        $tg_enviados = 0;
        foreach ($tg_users as $tgu) {
            if (isset($tgu['prefs']['musica']) && !$tgu['prefs']['musica']) continue;
            if (!$test_mode && ya_notificado_hoy($connection, $tgu['telegram_chat_id'], $tipo_evento)) continue;

            if (!$test_mode) {
                $t_res = telegram_enviar_mensaje($tgu['telegram_chat_id'], $tg_texto, $keyboard, 'HTML', $tipo_evento);
                if (!empty($t_res['ok'])) $tg_enviados++;
            } else {
                $tg_enviados++;
            }
        }
        write_log("Cron Eventos: Telegram Cierre Música enviado a $tg_enviados usuarios (#$id_comp)", "INFO");
    }
}


// =========================================================================
// 4. APERTURA DE INSCRIPCIONES (date_add(fecha, interval -dias_inicio_inscripcion day) = '$hoy')
// =========================================================================
$q_apertura = "SELECT id, nombre, lugar, fecha, dias_inicio_inscripcion, dias_fin_inscripcion, dias_coach_card, dias_musica, dias_sorteo, figuras, hora_sorteo, enlace_sorteo 
               FROM competiciones 
               WHERE date_add(fecha, interval -dias_inicio_inscripcion day) = '$hoy'";
$res_apertura = mysqli_query($connection, $q_apertura);

if ($res_apertura && mysqli_num_rows($res_apertura) > 0) {
    while ($comp = mysqli_fetch_assoc($res_apertura)) {
        $id_comp = $comp['id'];
        $nombre_comp = $comp['nombre'];
        $tipo_evento = "APERTURA_INSCRIPCIONES_{$id_comp}";

        write_log("Cron Eventos: Procesando APERTURA INSCRIPCIONES de '$nombre_comp' (#$id_comp)", "INFO");
        if ($is_cli) echo "[APERTURA INSCRIPCIONES] Procesando #$id_comp - $nombre_comp\n";

        // Cálculo de fechas clave
        $f_comp_es = fecha_es($comp['fecha']);
        $f_fin_raw = date('Y-m-d', strtotime("-{$comp['dias_fin_inscripcion']} days", strtotime($comp['fecha'])));
        $f_fin_es = fecha_es($f_fin_raw);
        $f_sorteo_raw = date('Y-m-d', strtotime("-{$comp['dias_sorteo']} days", strtotime($comp['fecha'])));
        $f_sorteo_es = fecha_es($f_sorteo_raw);

        // Hora del sorteo (semana impar: 11:00 h, semana par: 17:00 h si no está fijada)
        $num_sem_sorteo = (int)date('W', strtotime($f_sorteo_raw));
        $hora_sorteo_auto = ($num_sem_sorteo % 2 !== 0) ? '11:00' : '17:00';
        $hora_sorteo = !empty($comp['hora_sorteo']) ? $comp['hora_sorteo'] : $hora_sorteo_auto;

        $tg_texto = "🟢 <b>APERTURA OFICIAL DE INSCRIPCIONES</b>\n\n";
        $tg_texto .= "¡Queda abierto el plazo de inscripción para:\n";
        $tg_texto .= "🏊 <b>" . htmlspecialchars($nombre_comp) . "</b>\n";
        $tg_texto .= "📅 <b>Fecha del Campeonato:</b> $f_comp_es\n";
        if (!empty($comp['lugar'])) {
            $tg_texto .= "📍 <b>Sede:</b> " . htmlspecialchars($comp['lugar']) . "\n";
        }
        $tg_texto .= "\n⏰ <b>Calendario y Plazos Clave:</b>\n";
        $tg_texto .= "• 🔴 <b>Cierre de Inscripciones:</b> $f_fin_es (23:59 h)\n";

        // Si tiene coach card
        $res_has_cc = mysqli_query($connection, "SELECT COUNT(*) as num FROM fases WHERE id_competicion = $id_comp AND elementos_coach_card > 0");
        if ($res_has_cc && ($row_cc = mysqli_fetch_assoc($res_has_cc)) && $row_cc['num'] > 0) {
            $f_cc_raw = date('Y-m-d', strtotime("-{$comp['dias_coach_card']} days", strtotime($comp['fecha'])));
            $tg_texto .= "• 📋 <b>Límite Coach Cards:</b> " . fecha_es($f_cc_raw) . "\n";
        }

        // Si tiene música
        if ($comp['figuras'] != 'si' && !empty($comp['dias_musica']) && (int)$comp['dias_musica'] > 0) {
            $f_m_raw = date('Y-m-d', strtotime("-{$comp['dias_musica']} days", strtotime($comp['fecha'])));
            $tg_texto .= "• 🎵 <b>Límite Archivos Música:</b> " . fecha_es($f_m_raw) . "\n";
        }

        $tg_texto .= "• 🎲 <b>Día del Sorteo:</b> $f_sorteo_es ({$hora_sorteo} h)\n\n";
        $tg_texto .= "Ya puedes inscribir a tus deportistas desde la plataforma oficial SINCRM.";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🏊 Inscribir Nadadoras', 'url' => 'https://sincrm.pedrodiaz.eu/inscripciones.php'],
                    ['text' => 'ℹ️ Ver Ficha', 'url' => "https://sincrm.pedrodiaz.eu/index.php"]
                ]
            ]
        ];

        $tg_enviados = 0;
        foreach ($tg_users as $tgu) {
            if (isset($tgu['prefs']['competiciones']) && !$tgu['prefs']['competiciones']) continue;
            if (!$test_mode && ya_notificado_hoy($connection, $tgu['telegram_chat_id'], $tipo_evento)) continue;

            if (!$test_mode) {
                $t_res = telegram_enviar_mensaje($tgu['telegram_chat_id'], $tg_texto, $keyboard, 'HTML', $tipo_evento);
                if (!empty($t_res['ok'])) $tg_enviados++;
            } else {
                $tg_enviados++;
            }
        }
        write_log("Cron Eventos: Telegram Apertura Inscripciones enviado a $tg_enviados usuarios (#$id_comp)", "INFO");
    }
}


// =========================================================================
// 5. DÍA DEL SORTEO (date_add(fecha, interval -dias_sorteo day) = '$hoy')
// =========================================================================
$q_sorteo = "SELECT id, nombre, lugar, fecha, dias_sorteo, enlace_sorteo, hora_sorteo 
             FROM competiciones 
             WHERE date_add(fecha, interval -dias_sorteo day) = '$hoy'";
$res_sorteo = mysqli_query($connection, $q_sorteo);

if ($res_sorteo && mysqli_num_rows($res_sorteo) > 0) {
    while ($comp = mysqli_fetch_assoc($res_sorteo)) {
        $id_comp = $comp['id'];
        $nombre_comp = $comp['nombre'];
        $tipo_evento = "DIA_SORTEO_{$id_comp}";

        write_log("Cron Eventos: Procesando DÍA DEL SORTEO de '$nombre_comp' (#$id_comp)", "INFO");
        if ($is_cli) echo "[DÍA DEL SORTEO] Procesando #$id_comp - $nombre_comp\n";

        $f_comp_es = fecha_es($comp['fecha']);
        $f_sorteo_raw = date('Y-m-d', strtotime("-{$comp['dias_sorteo']} days", strtotime($comp['fecha'])));
        $num_sem_sorteo = (int)date('W', strtotime($f_sorteo_raw));
        $hora_sorteo_auto = ($num_sem_sorteo % 2 !== 0) ? '11:00' : '17:00';
        $hora_sorteo = !empty($comp['hora_sorteo']) ? $comp['hora_sorteo'] : $hora_sorteo_auto;
        $enlace_sorteo = !empty($comp['enlace_sorteo']) ? $comp['enlace_sorteo'] : 'https://meet.google.com/jyz-emho-coy';

        $tg_texto = "🎲 <b>HOY: SORTEO DE ORDEN DE SALIDA</b>\n\n";
        $tg_texto .= "Hoy se celebra el sorteo oficial del orden de actuación para:\n";
        $tg_texto .= "🏊 <b>" . htmlspecialchars($nombre_comp) . "</b>\n";
        $tg_texto .= "📅 <b>Campeonato:</b> $f_comp_es\n";
        $tg_texto .= "⏰ <b>Hora del Sorteo:</b> {$hora_sorteo} h\n";
        if (!empty($comp['lugar'])) {
            $tg_texto .= "📍 <b>Sede:</b> " . htmlspecialchars($comp['lugar']) . "\n";
        }
        $tg_texto .= "\n📢 En breve se determinará el orden oficial de salida de todas las participantes.";

        $tg_texto .= "\n\n🎥 <b>Videollamada en Directo:</b>\n";
        $tg_texto .= "Puedes seguir el sorteo en tiempo real a las <b>{$hora_sorteo} h</b> aquí:\n";
        $tg_texto .= "👉 <a href=\"" . htmlspecialchars($enlace_sorteo) . "\">" . htmlspecialchars($enlace_sorteo) . "</a>";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => "🎥 Unirse al Sorteo ({$hora_sorteo} h)", 'url' => $enlace_sorteo]
                ],
                [
                    ['text' => '🏊 Ver Competición en SINCRM', 'url' => "https://sincrm.pedrodiaz.eu/index.php"]
                ]
            ]
        ];

        $tg_enviados = 0;
        foreach ($tg_users as $tgu) {
            if (isset($tgu['prefs']['competiciones']) && !$tgu['prefs']['competiciones']) continue;
            if (!$test_mode && ya_notificado_hoy($connection, $tgu['telegram_chat_id'], $tipo_evento)) continue;

            if (!$test_mode) {
                $t_res = telegram_enviar_mensaje($tgu['telegram_chat_id'], $tg_texto, $keyboard, 'HTML', $tipo_evento);
                if (!empty($t_res['ok'])) $tg_enviados++;
            } else {
                $tg_enviados++;
            }
        }
        write_log("Cron Eventos: Telegram Día del Sorteo enviado a $tg_enviados usuarios (#$id_comp)", "INFO");
    }
}

if ($is_cli) {
    echo "=== PROCESO FINALIZADO CON ÉXITO ===\n";
}
?>