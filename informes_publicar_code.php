<?php
include('security.php');
include('includes/email_functions.php');

// Validar permisos: solo Secretarios y Admins (roles 1, 2, 3)
$roles_permitidos = [1, 2, 3];
if (!in_array($_SESSION['id_rol'] ?? 0, $roles_permitidos)) {
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Acceso denegado. Se requieren permisos de Secretario o Administrador.']);
        exit();
    }
    $_SESSION['estado'] = "No tienes permisos para realizar esta acción.";
    header('Location: informes_competicion.php');
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id_competicion = (int)($_POST['id_competicion'] ?? $_GET['id_competicion'] ?? ($_SESSION['id_competicion_usuario'] ?? 0));
$tipo = preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['tipo'] ?? $_GET['tipo'] ?? ''));

// Tipos válidos permitidos
$tipos_permitidos = ['inscripciones', 'orden', 'resultados', 'normativa', 'nadadoras', 'liga'];

if ($id_competicion <= 0 || !in_array($tipo, $tipos_permitidos)) {
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Parámetros inválidos.']);
        exit();
    }
    $_SESSION['estado'] = "Competición o tipo de documento no válido.";
    header('Location: informes_competicion.php');
    exit();
}

// Obtener datos de la competición
$q_comp = "SELECT id, nombre, fecha, lugar, clave_liga, figuras FROM competiciones WHERE id = '$id_competicion' LIMIT 1";
$res_comp = mysqli_query($connection, $q_comp);
$comp = ($res_comp) ? mysqli_fetch_assoc($res_comp) : null;

if (!$comp) {
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Competición no encontrada.']);
        exit();
    }
    $_SESSION['estado'] = "Competición no encontrada.";
    header('Location: informes_competicion.php');
    exit();
}

$docs_dir = __DIR__ . '/docs/';
if (!is_dir($docs_dir)) {
    @mkdir($docs_dir, 0775, true);
}
$target_file = $docs_dir . "{$id_competicion}-{$tipo}.pdf";

// Nombres legibles para emails y logs
$labels = [
    'inscripciones' => 'Listado Oficial de Inscripciones',
    'orden' => 'Orden de Actuación / Salida Oficial',
    'resultados' => 'Resultados Finales Oficiales',
    'normativa' => 'Normativa Oficial de Competición',
    'nadadoras' => 'Censo de Atletas Participantes',
    'liga' => 'Ranking Oficial de Liga'
];
$tipo_label = $labels[$tipo] ?? ucfirst($tipo);

// =========================================================================
// ACCIÓN 1: PUBLICAR EN LA WEB (Generación de PDF oficial y guardado en docs/)
// =========================================================================
if ($action === 'publicar') {
    $cron_token = "internal_cron_" . md5("SincrmInternalCron" . date('Y-m-d'));
    $host_header = $_SERVER['SERVER_NAME'] ?? 'sincrm.pedrodiaz.eu';
    
    // Mapeo a las URLs generadoras internas
    if ($tipo === 'inscripciones') {
        $url_rel = "/informes/informe_figuras.php?id_competicion={$id_competicion}&titulo=Inscripciones&cron_token={$cron_token}";
    } elseif ($tipo === 'orden') {
        $url_rel = "/informes/informe_figuras.php?id_competicion={$id_competicion}&titulo=orden&cron_token={$cron_token}";
    } elseif ($tipo === 'resultados') {
        $url_rel = "/informes/informe_figuras_resultados_categorias.php?id_competicion={$id_competicion}&titulo=Resultados&cron_token={$cron_token}";
    } elseif ($tipo === 'liga') {
        $url_rel = "/informes/informe_figuras_liga_categorias.php?id_competicion={$id_competicion}&cron_token={$cron_token}";
    } else {
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => "El documento '$tipo' debe subirse manualmente en formato PDF."]);
            exit();
        }
        $_SESSION['estado'] = "Este documento no se puede auto-generar, debe subirse manualmente.";
        header('Location: informes_competicion.php');
        exit();
    }

    $full_url = "http://localhost" . $url_rel;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $full_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Host: $host_header"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 40);
    $pdf_content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err = curl_error($ch);
    curl_close($ch);

    if ($http_code != 200 || empty($pdf_content) || strlen($pdf_content) < 500) {
        $msg_err = "Error al generar el PDF oficial (HTTP $http_code). " . ($curl_err ? "Curl: $curl_err" : "");
        write_log("Error al publicar '$tipo' para comp #$id_competicion: $msg_err", "ERROR");
        
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $msg_err]);
            exit();
        }
        $_SESSION['estado'] = $msg_err;
        header('Location: informes_competicion.php');
        exit();
    }

    // Guardar en docs/{id}-{tipo}.pdf
    if (@file_put_contents($target_file, $pdf_content) === false) {
        $msg_err = "No se pudo escribir el archivo en el servidor (permisos docs/).";
        write_log("Error de escritura en $target_file", "ERROR");
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $msg_err]);
            exit();
        }
        $_SESSION['estado'] = $msg_err;
        header('Location: informes_competicion.php');
        exit();
    }
    @chmod($target_file, 0664);

    $enviar_email = isset($_POST['enviar_email']) && ($_POST['enviar_email'] === '1' || $_POST['enviar_email'] === 'true' || $_POST['enviar_email'] === 'on');
    $email_enviado = false;
    $email_info = "";

    if ($enviar_email) {
        // MODO PRUEBAS SOLICITADO: Solo enviar a superadmin
        $destinatario = "xascorro@gmail.com";
        $asunto = "📢 PUBLICACIÓN OFICIAL: {$tipo_label} - " . $comp['nombre'];
        
        $fecha_f = (!empty($comp['fecha'])) ? date('d/m/Y', strtotime($comp['fecha'])) : 'Fecha por determinar';
        $lugar_f = !empty($comp['lugar']) ? $comp['lugar'] : 'Sede oficial';
        $url_web = "https://sincrm.pedrodiaz.eu/docs/{$id_competicion}-{$tipo}.pdf";

        $cuerpo = "
            <h2 style='color:#0f172a; font-size:18px; font-weight:800; margin:0 0 12px 0;'>Publicación Oficial de Documentación</h2>
            <p style='color:#334155; font-size:14px; line-height:1.6; margin:0 0 16px 0;'>
                Se ha publicado oficialmente en la plataforma el documento <strong>{$tipo_label}</strong> para la competición <strong>{$comp['nombre']}</strong>.
            </p>
            <div style='background-color:#f1f5f9; border-radius:12px; padding:16px; margin-bottom:20px; border-left:4px solid #2563eb;'>
                <p style='margin:0 0 6px 0; font-size:13px; color:#1e293b;'><strong>Competición:</strong> #{$id_competicion} - {$comp['nombre']}</p>
                <p style='margin:0 0 6px 0; font-size:13px; color:#1e293b;'><strong>Fecha:</strong> {$fecha_f}</p>
                <p style='margin:0 0 6px 0; font-size:13px; color:#1e293b;'><strong>Lugar:</strong> {$lugar_f}</p>
                <p style='margin:0; font-size:13px; color:#1e293b;'><strong>Documento:</strong> {$tipo_label} (Adjunto en este email)</p>
            </div>
            <p style='text-align:center; margin:24px 0;'>
                <a href='{$url_web}' style='display:inline-block; background-color:#2563eb; color:#ffffff; font-weight:700; font-size:13px; padding:12px 24px; border-radius:10px; text-decoration:none;'>
                    Ver Documento Publicado en la Web
                </a>
            </p>
            <p style='color:#64748b; font-size:12px; margin:0;'>
                * Nota: Durante la fase de pruebas este aviso se remite exclusivamente al superadministrador del sistema.
            </p>
        ";

        $attachment = [
            [
                'filename' => ucfirst($tipo) . "_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $comp['nombre']) . ".pdf",
                'content' => base64_encode($pdf_content)
            ]
        ];

        $res_mail = enviar_email($destinatario, $asunto, $cuerpo, $attachment);
        if ($res_mail === true) {
            $email_enviado = true;
            $email_info = " y notificación enviada a {$destinatario}";
        } else {
            $email_info = " (pero falló el email: {$res_mail})";
        }
    }

    write_log("Documento oficial '{$tipo}' publicado en la web para competición #{$id_competicion} por " . ($_SESSION['username'] ?? 'Secretario') . $email_info, "SUCCESS");

    $msg_exito = "¡{$tipo_label} publicado con éxito en la web!" . $email_info;

    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => $msg_exito, 
            'tipo' => $tipo,
            'file' => "docs/{$id_competicion}-{$tipo}.pdf",
            'file_size' => round(filesize($target_file) / 1024, 1) . ' KB',
            'updated_at' => date('d/m/Y H:i')
        ]);
        exit();
    }

    $_SESSION['correcto'] = $msg_exito;
    header('Location: informes_competicion.php');
    exit();
}

// =========================================================================
// ACCIÓN 2: BORRAR SUBIDA / DESPUBLICAR DE LA WEB
// =========================================================================
if ($action === 'borrar' || $action === 'despublicar') {
    if (file_exists($target_file)) {
        if (@unlink($target_file)) {
            write_log("Documento '{$tipo}' (#{$id_competicion}) eliminado/despublicado de la web por " . ($_SESSION['username'] ?? 'Usuario'), "WARNING");
            
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "El documento '{$tipo_label}' ha sido eliminado y ya no está publicado en la web.",
                    'tipo' => $tipo
                ]);
                exit();
            }
            $_SESSION['correcto'] = "El documento '{$tipo_label}' ha sido retirado de la web con éxito.";
        } else {
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => "No se pudo eliminar el archivo del servidor."]);
                exit();
            }
            $_SESSION['estado'] = "Error al intentar borrar el archivo del servidor.";
        }
    } else {
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => "El documento no estaba publicado.", 'tipo' => $tipo]);
            exit();
        }
        $_SESSION['correcto'] = "El documento no estaba publicado.";
    }

    $redirect = $_POST['redirect'] ?? 'informes_competicion.php';
    header("Location: $redirect");
    exit();
}

// =========================================================================
// ACCIÓN 3: SUBIDA MANUAL DE PDF (Normativa o documento personalizado)
// =========================================================================
if ($action === 'subir_manual') {
    if (!isset($_FILES['archivo_pdf']) || $_FILES['archivo_pdf']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['estado'] = "No se ha seleccionado ningún archivo PDF o hubo un error en la subida.";
        header('Location: informes_competicion.php');
        exit();
    }

    $ext = strtolower(pathinfo($_FILES['archivo_pdf']['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        $_SESSION['estado'] = "El archivo debe ser un documento PDF (.pdf).";
        header('Location: informes_competicion.php');
        exit();
    }

    if (move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $target_file)) {
        @chmod($target_file, 0664);
        write_log("Documento manual '{$tipo}' subido para competición #{$id_competicion} por " . ($_SESSION['username'] ?? 'Usuario'), "SUCCESS");
        $_SESSION['correcto'] = "Documento '{$tipo_label}' subido y publicado en la web con éxito.";
    } else {
        $_SESSION['estado'] = "Error al guardar el archivo en la carpeta docs/.";
    }

    header('Location: informes_competicion.php');
    exit();
}

// Si llega aquí sin acción reconocida
$_SESSION['estado'] = "Acción no reconocida.";
header('Location: informes_competicion.php');
exit();
