<?php
/**
 * Función universal para enviar emails vía Pulse Hub API (Servidor Blue)
 */
function enviar_email($destinatario, $asunto, $cuerpo, $adjunto = null, $debug = false) {
    $token = "hub_mail_947d82b3c2e1";
    $url = "https://pulse.pedrodiaz.eu/mail/api.php";
    $from = "SINCRM <sincrm@pedrodiaz.eu>";

    // Aplicar la plantilla visual en tonos claros con distribución horizontal equilibrada
    $html_final = wrap_email_template($asunto, $cuerpo);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-HUB-TOKEN: $token"));
    
    $fields = array(
        'from' => $from,
        'to' => $destinatario,
        'subject' => $asunto,
        'html' => $html_final
    );

    if (!empty($adjunto) && is_array($adjunto)) {
        $fields['attachments'] = json_encode($adjunto);
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($error) {
        error_log("Error Pulse Hub (enviar_email): " . $error);
        return "Error de conexión: " . $error;
    }
    
    if ($http_code >= 400) {
        error_log("Error Pulse Hub HTTP $http_code: " . $response);
        return "Error del servidor de correo ($http_code)";
    }

    $json = json_decode($response, true);
    if (isset($json['error'])) {
        error_log("Error Pulse Hub API: " . $json['error']);
        return "Error: " . $json['error'];
    }
    
    return true;
}

/**
 * Plantilla de diseño editorial y humana en tonos claros (sin estética IA)
 * Optimización horizontal (640px de ancho, cabecera y pie balanceados)
 */
function wrap_email_template($title, $content) {
    $current_year = date('Y');
    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>" . htmlspecialchars($title) . "</title>
    </head>
    <body style='margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #334155;'>
        <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background-color: #f8fafc; padding: 16px 8px;'>
            <tr>
                <td align='center'>
                    <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='max-width: 640px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 8px -2px rgba(15, 23, 42, 0.05);'>
                        
                        <!-- CABECERA HORIZONTAL COMPACTA -->
                        <tr>
                            <td style='padding: 18px 20px; border-bottom: 1px solid #f1f5f9; background-color: #ffffff;'>
                                <table role='presentation' width='100%' cellpadding='0' cellspacing='0'>
                                    <tr>
                                        <td align='left' style='vertical-align: middle;'>
                                            <span style='font-size: 21px; font-weight: 900; letter-spacing: 0.5px; color: #0f172a; display: inline-block; vertical-align: middle;'>SINCRM</span>
                                            <span style='display: inline-block; vertical-align: middle; margin: 0 8px; color: #cbd5e1; font-weight: 300;'>|</span>
                                            <span style='font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; color: #0284c7; display: inline-block; vertical-align: middle;'>Natación Artística</span>
                                        </td>
                                        <td align='right' style='vertical-align: middle;'>
                                            <span style='font-size: 13px; color: #94a3b8; font-weight: 500;'>sincrm.pedrodiaz.eu</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- CUERPO PRINCIPAL CON MÁXIMO APROVECHAMIENTO (MÓVIL FRIENDLY) -->
                        <tr>
                            <td style='padding: 24px 20px; font-size: 16px; line-height: 1.6; color: #334155;'>
                                $content
                            </td>
                        </tr>

                        <!-- PIE DE MENSAJE -->
                        <tr>
                            <td style='padding: 16px 20px; background-color: #fafafa; border-top: 1px solid #f1f5f9;'>
                                <table role='presentation' width='100%' cellpadding='0' cellspacing='0'>
                                    <tr>
                                        <td align='left' style='font-size: 13px; color: #94a3b8; line-height: 1.5; vertical-align: middle;'>
                                            <span style='font-weight: 600; color: #64748b;'>SINCRM</span> &middot; Sistema de Gestión Deportiva
                                            <span style='display: block; margin-top: 2px; font-size: 12px;'>
                                                Contacto: <a href='mailto:sincrm@pedrodiaz.eu' style='color: #0284c7; text-decoration: none;'>sincrm@pedrodiaz.eu</a>
                                            </span>
                                        </td>
                                        <td align='right' style='font-size: 12px; color: #cbd5e1; white-space: nowrap; vertical-align: middle;'>
                                            &copy; {$current_year}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>";
}
?>