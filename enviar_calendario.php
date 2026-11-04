<?php
/**
 * Generador y emisor del Calendario de Competiciones 2026-2027
 * Vía Pulse Hub (Servidor Blue) con adjunto .ICS para Outlook y Google Calendar
 */

require_once(__DIR__ . '/includes/email_functions.php');

$events = [
    [
        "id" => 81,
        "name" => "XIV Liga de Figuras (1ª Jornada)",
        "start" => "2026-11-14",
        "end" => "2026-11-15",
        "location" => "Lorca / Alhama de Murcia",
        "desc" => "Competición Mixta: Figuras Alevín/Infantil y Rutinas Técnicas Junior/Senior."
    ],
    [
        "id" => 83,
        "name" => "Pase de Nivel 1",
        "start" => "2026-11-21",
        "end" => "2026-11-22",
        "location" => "Piscina Cubierta Municipal, Alhama de Murcia",
        "desc" => "Pruebas oficiales de pase de nivel de figuras técnicas FNRM."
    ],
    [
        "id" => 84,
        "name" => "Pase de Nivel 2",
        "start" => "2026-12-19",
        "end" => "2026-12-20",
        "location" => "Piscina Cubierta Municipal, Alhama de Murcia",
        "desc" => "Pruebas oficiales de pase de nivel de figuras técnicas FNRM."
    ],
    [
        "id" => 85,
        "name" => "Pase de Nivel 3",
        "start" => "2027-01-23",
        "end" => "2027-01-24",
        "location" => "Piscina Cubierta Municipal, Alhama de Murcia",
        "desc" => "Pruebas oficiales de pase de nivel de figuras técnicas FNRM."
    ],
    [
        "id" => 86,
        "name" => "XIV Liga de Figuras (2ª Jornada)",
        "start" => "2027-02-20",
        "end" => "2027-02-21",
        "location" => "Lorca",
        "desc" => "Competición Mixta: Figuras Alevín/Infantil y Rutinas Técnicas Junior/Senior."
    ],
    [
        "id" => 87,
        "name" => "Pase de Nivel 4",
        "start" => "2027-03-06",
        "end" => "2027-03-07",
        "location" => "Piscina Cubierta Municipal, Alhama de Murcia",
        "desc" => "Pruebas oficiales de pase de nivel de figuras técnicas FNRM."
    ],
    [
        "id" => 88,
        "name" => "Campeonato Regional Escolar de Figuras",
        "start" => "2027-03-13",
        "end" => "2027-03-14",
        "location" => "Por determinar",
        "desc" => "Competición escolar de figuras individual (no federada)."
    ],
    [
        "id" => 89,
        "name" => "XX Campeonato Regional OPEN (10-11 Abril)",
        "start" => "2027-04-10",
        "end" => "2027-04-12",
        "location" => "Por determinar",
        "desc" => "Campeonato Regional Open de Rutinas completas (Solos, Dúos, Equipos, Acrobática)."
    ],
    [
        "id" => 90,
        "name" => "XIV Liga de Figuras (3ª Jornada)",
        "start" => "2027-05-15",
        "end" => "2027-05-16",
        "location" => "Por determinar",
        "desc" => "Competición Mixta: Figuras Alevín/Infantil y Rutinas Técnicas Junior/Senior."
    ],
    [
        "id" => 91,
        "name" => "XIV Liga de Figuras (4ª Jornada)",
        "start" => "2027-06-12",
        "end" => "2027-06-13",
        "location" => "Por determinar",
        "desc" => "Competición Mixta: Figuras Alevín/Infantil y Rutinas Técnicas Junior/Senior (Jornada Final)."
    ],
    [
        "id" => 92,
        "name" => "Campeonato Regional Escolar de Rutinas OPEN",
        "start" => "2027-06-19",
        "end" => "2027-06-20",
        "location" => "Por determinar",
        "desc" => "Competición escolar de rutinas completas con música (no federada)."
    ]
];

// 1. Construir el archivo .ICS (RFC-5545)
$now_stamp = gmdate('Ymd\THis\Z');
$ics = "BEGIN:VCALENDAR\r\n";
$ics .= "VERSION:2.0\r\n";
$ics .= "PRODID:-//SINCRM//Calendario Natacion Artistica 2026-2027//ES\r\n";
$ics .= "CALSCALE:GREGORIAN\r\n";
$ics .= "METHOD:PUBLISH\r\n";
$ics .= "X-WR-CALNAME:Sincro Temporada 2026-2027\r\n";
$ics .= "X-WR-TIMEZONE:Europe/Madrid\r\n";

foreach ($events as $ev) {
    $st = str_replace('-', '', $ev['start']);
    $en = str_replace('-', '', $ev['end']);
    $ics .= "BEGIN:VEVENT\r\n";
    $ics .= "UID:sincrm-comp-{$ev['id']}@sincrm.pedrodiaz.eu\r\n";
    $ics .= "DTSTAMP:{$now_stamp}\r\n";
    $ics .= "DTSTART;VALUE=DATE:{$st}\r\n";
    $ics .= "DTEND;VALUE=DATE:{$en}\r\n";
    $ics .= "SUMMARY:{$ev['name']}\r\n";
    $ics .= "LOCATION:{$ev['location']}\r\n";
    $ics .= "DESCRIPTION:{$ev['desc']} | Sistema SINCRM: https://sincrm.pedrodiaz.eu/competiciones.php\r\n";
    $ics .= "STATUS:CONFIRMED\r\n";
    $ics .= "TRANSP:TRANSPARENT\r\n";
    $ics .= "END:VEVENT\r\n";
}
$ics .= "END:VCALENDAR\r\n";

// Guardar en el directorio web para descarga directa
$ics_filepath = __DIR__ . '/Calendario_Sincro_2026_2027.ics';
file_put_contents($ics_filepath, $ics);
chmod($ics_filepath, 0664);

// También copiar a beta si existe
if (is_dir('/var/www/html/beta')) {
    @copy($ics_filepath, '/var/www/html/beta/Calendario_Sincro_2026_2027.ics');
    @chmod('/var/www/html/beta/Calendario_Sincro_2026_2027.ics', 0664);
}

echo "ICS generado correctamente (" . strlen($ics) . " bytes)\n";

// 2. Preparar el correo electrónico con Servidor Blue
$destinatarios = [
    "pedrofrancisco.diaz@elpozo.com",
    "xascorro@gmail.com"
];

$asunto = "🗓️ Calendario Oficial Natación Artística 2026-2027 (Importación Outlook / Google)";

$tabla_html = "<table style='width:100%; border-collapse: collapse; margin-top: 15px; font-size: 13px;'>
<thead>
    <tr style='background: #0f172a; color: #ffffff; text-align: left;'>
        <th style='padding: 10px; border-radius: 8px 0 0 0;'>Fecha</th>
        <th style='padding: 10px;'>Competición</th>
        <th style='padding: 10px; border-radius: 0 8px 0 0;'>Sede</th>
    </tr>
</thead>
<tbody>";

$bg_flip = false;
foreach ($events as $ev) {
    $row_bg = $bg_flip ? "#f8fafc" : "#ffffff";
    $bg_flip = !$bg_flip;
    $fecha_formateada = date('d/m/Y', strtotime($ev['start']));
    $tabla_html .= "<tr style='background: $row_bg; border-bottom: 1px solid #e2e8f0;'>
        <td style='padding: 8px 10px; font-weight: bold; color: #1e293b; white-space: nowrap;'>$fecha_formateada</td>
        <td style='padding: 8px 10px; color: #334155; font-weight: 600;'>{$ev['name']}</td>
        <td style='padding: 8px 10px; color: #64748b; font-size: 12px;'>{$ev['location']}</td>
    </tr>";
}
$tabla_html .= "</tbody></table>";

$cuerpo = "
<h2>Calendario Oficial de Competiciones FNRM (2026-2027)</h2>
<p>Hola, Pedro:</p>
<p>Se adjunta a este correo el archivo oficial <strong>Calendario_Sincro_2026_2027.ics</strong> para importar directamente todos los eventos de la temporada a tu calendario de <strong>Outlook</strong> del trabajo y a <strong>Google Calendar</strong>.</p>

<div class='alert-box alert-info' style='background: #eff6ff; padding: 15px; border-radius: 12px; border-left: 5px solid #3b82f6; margin: 15px 0;'>
    <h3 style='margin: 0 0 8px 0; color: #1e40af; font-size: 14px;'>📌 Instrucciones de Importación Rápida:</h3>
    <ul style='margin: 0; padding-left: 20px; font-size: 13px; color: #1e3a8a; line-height: 1.6;'>
        <li><strong>En Outlook:</strong> Haz doble clic sobre el archivo adjunto <code>.ics</code> y selecciona <em>\"Guardar y cerrar\"</em> o <em>\"Importar a mi calendario\"</em>.</li>
        <li><strong>En Google Calendar:</strong> Puedes descargar el adjunto, ir a <a href='https://calendar.google.com/calendar/r/settings/export' target='_blank' style='color: #2563eb; font-weight: bold;'>Ajustes &gt; Importar</a> y subir el archivo <code>.ics</code> para que queden todos los eventos agendados al instante.</li>
        <li><strong>Descarga directa online:</strong> <a href='https://sincrm.pedrodiaz.eu/Calendario_Sincro_2026_2027.ics' style='color: #2563eb; font-weight: bold;'>Descargar Calendario (.ics)</a></li>
    </ul>
</div>

<h3 style='color: #0f172a; margin-top: 25px;'>Resumen de Eventos Agendados:</h3>
$tabla_html

<p style='margin-top: 25px; font-size: 12px; color: #64748b;'>Este calendario ha sido generado automáticamente desde el sistema <strong>SINCRM</strong>.</p>
";

$token = "hub_mail_947d82b3c2e1";
$api_url = "https://pulse.pedrodiaz.eu/mail/send_resend.php";

$attachment_data = [
    [
        'filename' => "Calendario_Sincro_2026_2027.ics",
        'content' => base64_encode($ics)
    ]
];

foreach ($destinatarios as $dest) {
    echo "Enviando correo a: $dest ... ";
    $post_fields = [
        'from' => "SINCRM <sincrm@pedrodiaz.eu>",
        'to' => $dest,
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
    $curl_err = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        echo "ERROR CURL: $curl_err\n";
    } else {
        echo "OK - Respuesta Servidor Blue: $api_res\n";
    }
}
?>
