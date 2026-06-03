<?php
/**
 * Función universal para enviar emails vía Pulse Hub API (Reemplaza PHPMailer)
 */
function enviar_email($destinatario, $asunto, $cuerpo, $adjunto = null, $debug = false) {
    $token = "hub_mail_947d82b3c2e1";
    $url = "https://pulse.pedrodiaz.eu/mail/api.php";
    $from = "SINCRM4 <sincrm@pedrodiaz.eu>";

    // Aplicar el diseño unificado de SINCRM4
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

    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("Error Pulse Hub (enviar_email): " . $error);
        return "Error: " . $error;
    }
    
    return true;
}

/**
 * Plantilla de diseño unificada para SINCRM4
 */
function wrap_email_template($title, $content) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <style>
            body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 24px; overflow: hidden; shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
            .header { background: #0f172a; padding: 40px; text-align: center; }
            .logo-text { font-size: 28px; font-weight: 900; color: #ffffff; letter-spacing: -1px; font-style: italic; }
            .logo-accent { color: #3b82f6; }
            .content { padding: 40px; color: #334155; line-height: 1.6; }
            .content h2 { color: #0f172a; font-size: 24px; font-weight: 800; margin-top: 0; letter-spacing: -0.5px; }
            .content p { font-size: 16px; margin-bottom: 20px; }
            .footer { background: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0; }
            .footer p { font-size: 12px; color: #94a3b8; margin: 0; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; }
            .btn { display: inline-block; padding: 16px 32px; background: #0f172a; color: #ffffff !important; text-decoration: none; border-radius: 16px; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-top: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.3s; }
            .otp-box { background: #f1f5f9; border-radius: 20px; padding: 30px; text-align: center; margin: 20px 0; border: 2px dashed #cbd5e1; }
            .otp-code { font-size: 42px; font-weight: 900; color: #3b82f6; letter-spacing: 8px; margin: 0; }
            .alert-box { padding: 20px; border-radius: 16px; margin: 20px 0; font-size: 14px; font-weight: 600; }
            .alert-warning { background: #fff7ed; color: #9a3412; border-left: 6px solid #f97316; }
            .alert-info { background: #eff6ff; color: #1e40af; border-left: 6px solid #3b82f6; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo-text'>SINCRM <span class='logo-accent'>3</span></div>
            </div>
            <div class='content'>
                $content
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " SINCRM4 - Natación Artística</p>
                <div style='margin-top: 10px;'>
                    <a href='mailto:sincrm@pedrodiaz.eu' style='color: #3b82f6; font-size: 11px; text-decoration: none; font-weight: bold;'>sincrm@pedrodiaz.eu</a>
                </div>
            </div>
        </div>
    </body>
    </html>";
}
?>