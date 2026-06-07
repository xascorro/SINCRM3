<?php
/**
 * Script de Automatización: Envío de Inscripciones por Email al Cierre
 * Ejecución vía CRON: 00:15 (Procesa cierres del día anterior)
 */

// Simular entorno web para los scripts de informes
$_SESSION['id_rol'] = 1; 

include(__DIR__ . '/database/dbconfig.php');
include(__DIR__ . '/includes/email_functions.php');

// 1. Detectar competiciones cuyo plazo de inscripción terminó AYER
$ayer = date('Y-m-d', strtotime('-1 day'));
$q_comp = "SELECT id, nombre, figuras, fecha, dias_fin_inscripcion 
           FROM competiciones 
           WHERE date_add(fecha, interval -dias_fin_inscripcion day) = '$ayer'";

$res_comp = mysqli_query($connection, $q_comp);

if (mysqli_num_rows($res_comp) == 0) {
    write_log("Cron Inscripciones: No hay cierres para ayer ($ayer)", "INFO");
    exit();
}

while ($comp = mysqli_fetch_assoc($res_comp)) {
    $id_comp = $comp['id'];
    $nombre_comp = $comp['nombre'];
    $is_figuras = ($comp['figuras'] == 'si');
    
    write_log("Cron Inscripciones: Procesando cierre de '$nombre_comp' (ID: $id_comp) terminado el $ayer", "INFO");

    // 2. Generar el PDF mediante captura del buffer de salida
    // Construimos la URL interna para llamar al generador de PDF
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = "localhost"; // Ejecución interna en el contenedor
    
    // Ruta del informe según tipo
    if ($is_figuras) {
        $report_url = "http://localhost/informes/informe_figuras.php?id_competicion=$id_comp";
    } else {
        $report_url = "http://localhost/informes/informe_preinscripciones.php?id_competicion=$id_comp";
    }

    // Usamos cURL para obtener el binario del PDF
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $report_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // IMPORTANTE: Los informes requieren sesión. Para este cron, modificamos el informe 
    // o usamos un token de seguridad. Por simplicidad en esta prueba, asumimos acceso local.
    $pdf_content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code != 200 || empty($pdf_content)) {
        write_log("Cron Inscripciones: Error al generar PDF para ID $id_comp (HTTP $http_code)", "ERROR");
        continue;
    }

    // 3. Enviar vía Pulse Hub API (Servidor Blue)
    $destinatario = "xascorr@gmail.com";
    $asunto = "🔴 CIERRE INSCRIPCIONES: $nombre_comp";
    $cuerpo = "<h2>Notificación Automática de Cierre</h2>
               <p>El plazo de inscripción para la competición <strong>$nombre_comp</strong> ha finalizado hoy.</p>
               <p>Se adjunta el listado oficial de inscripciones capturado en el momento del cierre.</p>
               <ul>
                <li><strong>ID Competición:</strong> #$id_comp</li>
                <li><strong>Fecha Cierre:</strong> $hoy</li>
                <li><strong>Tipo:</strong> " . ($is_figuras ? 'Figuras' : 'Rutinas') . "</li>
               </ul>";

    // Modificamos enviar_email para soportar adjuntos base64 si es necesario, 
    // o usamos una versión extendida aquí.
    
    $token = "hub_mail_947d82b3c2e1";
    $api_url = "https://pulse.pedrodiaz.eu/mail/api.php";
    
    $post_fields = [
        'from' => "SINCRM <sincrm@pedrodiaz.eu>",
        'to' => $destinatario,
        'subject' => $asunto,
        'html' => wrap_email_template($asunto, $cuerpo),
        'attachment_name' => "Inscripciones_" . str_replace(' ', '_', $nombre_comp) . ".pdf",
        'attachment_content' => base64_encode($pdf_content)
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-HUB-TOKEN: $token"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $api_res = curl_exec($ch);
    curl_close($ch);

    write_log("Cron Inscripciones: Email enviado para '$nombre_comp'. Respuesta API: $api_res", "SUCCESS");
}
?>
