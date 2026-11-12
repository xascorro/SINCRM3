<?php
include('security.php');
include('includes/email_functions.php');

// Validar permisos: solo Secretarios y Admins (roles 1, 2, 3)
$roles_permitidos = [1, 2, 3];
if (!in_array($_SESSION['id_rol'] ?? 0, $roles_permitidos)) {
    if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
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

if ($id_competicion <= 0) {
    if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'ID de competición no válido.']);
        exit();
    }
    $_SESSION['estado'] = "Competición no válida.";
    header('Location: informes_competicion.php');
    exit();
}

// Obtener datos de la competición
$q_comp = "SELECT id, nombre, fecha, lugar, clave_liga, figuras FROM competiciones WHERE id = '$id_competicion' LIMIT 1";
$res_comp = mysqli_query($connection, $q_comp);
$comp = ($res_comp) ? mysqli_fetch_assoc($res_comp) : null;

if (!$comp) {
    if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Competición no encontrada en el sistema.']);
        exit();
    }
    $_SESSION['estado'] = "Competición no encontrada.";
    header('Location: informes_competicion.php');
    exit();
}

// =========================================================================
// ACCIÓN: OBTENER DESTINATARIOS PARA EL MODAL DE PUBLICACIÓN
// =========================================================================
if ($action === 'get_destinatarios') {
    header('Content-Type: application/json');

    // 1. Destinatario de Prueba
    $dest_prueba = [
        'email' => 'xascorro@gmail.com',
        'nombre' => 'Superadmin (Modo Prueba)'
    ];

    // 2. Clubes participantes
    $q_clubes = "
        SELECT DISTINCT c.id, c.nombre, c.nombre_corto, c.codigo 
        FROM clubes c 
        WHERE c.id IN (
            SELECT DISTINCT n.club 
            FROM inscripciones_figuras inf 
            JOIN nadadoras n ON inf.id_nadadora = n.id 
            WHERE inf.id_competicion = '$id_competicion' AND n.club IS NOT NULL
            UNION
            SELECT DISTINCT r.id_club 
            FROM rutinas r 
            WHERE r.id_competicion = '$id_competicion' AND r.id_club IS NOT NULL
            UNION
            SELECT DISTINCT n.club
            FROM rutinas_participantes rp
            JOIN rutinas r ON rp.id_rutina = r.id
            JOIN nadadoras n ON rp.id_nadadora = n.id
            WHERE r.id_competicion = '$id_competicion' AND n.club IS NOT NULL
        )
        ORDER BY c.nombre ASC
    ";
    $res_clubes = mysqli_query($connection, $q_clubes);
    $clubes_list = [];
    $clubes_nombres = [];
    if ($res_clubes) {
        while ($c = mysqli_fetch_assoc($res_clubes)) {
            $clubes_list[] = $c;
            $clubes_nombres[] = !empty($c['nombre_corto']) ? $c['nombre_corto'] : $c['nombre'];
        }
    }

    // Usuarios representantes de esos clubes
    $q_usuarios_clubes = "
        SELECT u.id, u.username, u.email, u.club, c.nombre as club_nombre, c.nombre_corto as club_corto
        FROM usuarios u
        JOIN clubes c ON u.club = c.id
        WHERE u.activo = 1 
          AND u.email IS NOT NULL 
          AND TRIM(u.email) != ''
          AND u.club IN (
            SELECT DISTINCT n.club 
            FROM inscripciones_figuras inf 
            JOIN nadadoras n ON inf.id_nadadora = n.id 
            WHERE inf.id_competicion = '$id_competicion' AND n.club IS NOT NULL
            UNION
            SELECT DISTINCT r.id_club 
            FROM rutinas r 
            WHERE r.id_competicion = '$id_competicion' AND r.id_club IS NOT NULL
            UNION
            SELECT DISTINCT n.club
            FROM rutinas_participantes rp
            JOIN rutinas r ON rp.id_rutina = r.id
            JOIN nadadoras n ON rp.id_nadadora = n.id
            WHERE r.id_competicion = '$id_competicion' AND n.club IS NOT NULL
          )
        ORDER BY c.nombre ASC, u.username ASC
    ";
    $res_u_clubes = mysqli_query($connection, $q_usuarios_clubes);
    $usuarios_clubes = [];
    if ($res_u_clubes) {
        while ($u = mysqli_fetch_assoc($res_u_clubes)) {
            $email = strtolower(trim($u['email']));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $usuarios_clubes[$email] = [
                    'id' => (int)$u['id'],
                    'nombre' => $u['username'],
                    'email' => $email,
                    'club' => $u['club_nombre'],
                    'club_corto' => $u['club_corto']
                ];
            }
        }
    }

    // 3. Jueces participantes
    $q_jueces = "
        SELECT DISTINCT j.id as id_juez, j.nombre, j.apellidos, u.email, u.username
        FROM jueces j
        JOIN usuarios u ON (u.id_juez_v3 = j.id OR LOWER(TRIM(u.username)) = LOWER(TRIM(CONCAT(j.nombre, ' ', j.apellidos))))
        WHERE u.activo = 1 
          AND u.email IS NOT NULL 
          AND TRIM(u.email) != ''
          AND j.id IN (
            SELECT DISTINCT id_juez FROM panel_jueces WHERE id_competicion = '$id_competicion' AND id_juez IS NOT NULL
            UNION
            SELECT DISTINCT pj.id_juez FROM panel_jueces pj JOIN fases f ON pj.id_fase = f.id WHERE f.id_competicion = '$id_competicion' AND pj.id_juez IS NOT NULL
            UNION
            SELECT DISTINCT id_juez FROM puesto_juez WHERE id_competicion = '$id_competicion' AND id_juez IS NOT NULL
          )
        ORDER BY j.apellidos ASC, j.nombre ASC
    ";
    $res_jueces = mysqli_query($connection, $q_jueces);
    $jueces_dest = [];
    if ($res_jueces) {
        while ($j = mysqli_fetch_assoc($res_jueces)) {
            $email = strtolower(trim($j['email']));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $jueces_dest[$email] = [
                    'id_juez' => (int)$j['id_juez'],
                    'nombre' => trim($j['nombre'] . ' ' . $j['apellidos']),
                    'email' => $email,
                    'username' => $j['username']
                ];
            }
        }
    }

    echo json_encode([
        'success' => true,
        'competicion' => [
            'id' => $comp['id'],
            'nombre' => $comp['nombre'],
            'fecha' => $comp['fecha'],
            'lugar' => $comp['lugar']
        ],
        'prueba' => $dest_prueba,
        'clubes' => [
            'total_clubes' => count($clubes_list),
            'total_usuarios' => count($usuarios_clubes),
            'clubes_nombres' => $clubes_nombres,
            'destinatarios' => array_values($usuarios_clubes)
        ],
        'jueces' => [
            'total_jueces' => count($jueces_dest),
            'destinatarios' => array_values($jueces_dest)
        ]
    ]);
    exit();
}

$tipo = preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['tipo'] ?? $_GET['tipo'] ?? ''));
$tipos_permitidos = ['inscripciones', 'orden', 'clasificacion', 'resultados', 'normativa', 'nadadoras', 'liga'];

if (!in_array($tipo, $tipos_permitidos)) {
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => "Tipo de documento no válido: '$tipo'."]);
        exit();
    }
    $_SESSION['estado'] = "Tipo de documento no válido.";
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
    'clasificacion' => 'Clasificación Detallada / Resultados Oficiales',
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
    } elseif ($tipo === 'clasificacion' || $tipo === 'resultados') {
        // Comprobar si hay rutinas o es pura de figuras
        $q_chk_rut = "SELECT COUNT(*) as n FROM rutinas WHERE id_competicion = '$id_competicion'";
        $res_chk = mysqli_query($connection, $q_chk_rut);
        $total_rutinas_comp = ($res_chk) ? (int)mysqli_fetch_assoc($res_chk)['n'] : 0;

        if ($total_rutinas_comp > 0) {
            $url_rel = "/informes/informe_puntuaciones_global.php?id_competicion={$id_competicion}&cron_token={$cron_token}";
        } else {
            $url_rel = "/informes/informe_figuras_resultados_categorias.php?id_competicion={$id_competicion}&titulo=Resultados&cron_token={$cron_token}";
        }
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
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

    // Si es clasificacion o resultados, mantener ambas copias sincronizadas para máxima compatibilidad
    if ($tipo === 'clasificacion') {
        @file_put_contents($docs_dir . "{$id_competicion}-resultados.pdf", $pdf_content);
        @chmod($docs_dir . "{$id_competicion}-resultados.pdf", 0664);
    } elseif ($tipo === 'resultados') {
        @file_put_contents($docs_dir . "{$id_competicion}-clasificacion.pdf", $pdf_content);
        @chmod($docs_dir . "{$id_competicion}-clasificacion.pdf", 0664);
    }

    // Determinar destinatarios del correo según los switches recibidos
    $dest_prueba_active = isset($_POST['dest_prueba']) && in_array($_POST['dest_prueba'], ['1', 'true', 'on', 1], true);
    $dest_clubes_active = isset($_POST['dest_clubes']) && in_array($_POST['dest_clubes'], ['1', 'true', 'on', 1], true);
    $dest_jueces_active = isset($_POST['dest_jueces']) && in_array($_POST['dest_jueces'], ['1', 'true', 'on', 1], true);

    $emails_finales = [];

    // 1. Añadir prueba si está activo
    if ($dest_prueba_active) {
        $emails_finales['xascorro@gmail.com'] = 'xascorro@gmail.com';
    }

    // 2. Añadir representantes de clubes participantes
    if ($dest_clubes_active) {
        $q_u_clubes = "
            SELECT u.email 
            FROM usuarios u 
            WHERE u.activo = 1 
              AND u.email IS NOT NULL 
              AND TRIM(u.email) != ''
              AND u.club IN (
                SELECT DISTINCT n.club 
                FROM inscripciones_figuras inf 
                JOIN nadadoras n ON inf.id_nadadora = n.id 
                WHERE inf.id_competicion = '$id_competicion' AND n.club IS NOT NULL
                UNION
                SELECT DISTINCT r.id_club 
                FROM rutinas r 
                WHERE r.id_competicion = '$id_competicion' AND r.id_club IS NOT NULL
                UNION
                SELECT DISTINCT n.club
                FROM rutinas_participantes rp
                JOIN rutinas r ON rp.id_rutina = r.id
                JOIN nadadoras n ON rp.id_nadadora = n.id
                WHERE r.id_competicion = '$id_competicion' AND n.club IS NOT NULL
              )
        ";
        $res_u = mysqli_query($connection, $q_u_clubes);
        if ($res_u) {
            while ($row_u = mysqli_fetch_assoc($res_u)) {
                $em = strtolower(trim($row_u['email']));
                if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
                    $emails_finales[$em] = $em;
                }
            }
        }
    }

    // 3. Añadir jueces participantes (por defecto deshabilitado salvo que se active explícitamente)
    if ($dest_jueces_active) {
        $q_j_mail = "
            SELECT DISTINCT u.email
            FROM jueces j
            JOIN usuarios u ON (u.id_juez_v3 = j.id OR LOWER(TRIM(u.username)) = LOWER(TRIM(CONCAT(j.nombre, ' ', j.apellidos))))
            WHERE u.activo = 1 
              AND u.email IS NOT NULL 
              AND TRIM(u.email) != ''
              AND j.id IN (
                SELECT DISTINCT id_juez FROM panel_jueces WHERE id_competicion = '$id_competicion' AND id_juez IS NOT NULL
                UNION
                SELECT DISTINCT pj.id_juez FROM panel_jueces pj JOIN fases f ON pj.id_fase = f.id WHERE f.id_competicion = '$id_competicion' AND pj.id_juez IS NOT NULL
                UNION
                SELECT DISTINCT id_juez FROM puesto_juez WHERE id_competicion = '$id_competicion' AND id_juez IS NOT NULL
              )
        ";
        $res_j = mysqli_query($connection, $q_j_mail);
        if ($res_j) {
            while ($row_j = mysqli_fetch_assoc($res_j)) {
                $em = strtolower(trim($row_j['email']));
                if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
                    $emails_finales[$em] = $em;
                }
            }
        }
    }

    $emails_enviados_ok = [];
    $errores_envio = [];

    if (!empty($emails_finales)) {
        $asunto = "📢 PUBLICACIÓN OFICIAL: {$tipo_label} - " . $comp['nombre'];
        $fecha_f = (!empty($comp['fecha'])) ? date('d/m/Y', strtotime($comp['fecha'])) : 'Fecha por determinar';
        $lugar_f = !empty($comp['lugar']) ? $comp['lugar'] : 'Sede oficial';
        $url_web = "https://sincrm.pedrodiaz.eu/docs/{$id_competicion}-{$tipo}.pdf";

        // Nombre de adjunto solicitado: {id}-{tipo}.pdf (ej: 81-orden.pdf, 81-clasificacion.pdf, 81-inscripciones.pdf)
        $attachment_name = "{$id_competicion}-{$tipo}.pdf";

        $cuerpo = "
            <h2 style='color:#0f172a; font-size:18px; font-weight:800; margin:0 0 12px 0;'>Publicación Oficial de Documentación</h2>
            <p style='color:#334155; font-size:14px; line-height:1.6; margin:0 0 16px 0;'>
                Se ha publicado oficialmente en la plataforma el documento <strong>{$tipo_label}</strong> para la competición <strong>{$comp['nombre']}</strong>.
            </p>
            <div style='background-color:#f1f5f9; border-radius:12px; padding:16px; margin-bottom:20px; border-left:4px solid #2563eb;'>
                <p style='margin:0 0 6px 0; font-size:13px; color:#1e293b;'><strong>Competición:</strong> #{$id_competicion} - {$comp['nombre']}</p>
                <p style='margin:0 0 6px 0; font-size:13px; color:#1e293b;'><strong>Fecha:</strong> {$fecha_f}</p>
                <p style='margin:0 0 6px 0; font-size:13px; color:#1e293b;'><strong>Lugar:</strong> {$lugar_f}</p>
                <p style='margin:0; font-size:13px; color:#1e293b;'><strong>Documento Adjunto:</strong> <code>{$attachment_name}</code></p>
            </div>
            <p style='text-align:center; margin:24px 0;'>
                <a href='{$url_web}' style='display:inline-block; background-color:#2563eb; color:#ffffff; font-weight:700; font-size:13px; padding:12px 24px; border-radius:10px; text-decoration:none;'>
                    Ver Documento Publicado en la Web
                </a>
            </p>
            <p style='color:#64748b; font-size:12px; margin:0;'>
                Encontrarás adjunto el documento oficial con las notas y firmas reglamentarias emitidas por la mesa técnica.
            </p>
        ";

        $attachment = [
            [
                'filename' => $attachment_name,
                'content' => base64_encode($pdf_content)
            ]
        ];

        foreach ($emails_finales as $dest_email) {
            $res_mail = enviar_email($dest_email, $asunto, $cuerpo, $attachment);
            if ($res_mail === true) {
                $emails_enviados_ok[] = $dest_email;
            } else {
                $errores_envio[] = "{$dest_email}: {$res_mail}";
            }
        }
    }

    $email_info_resumen = "";
    if (!empty($emails_enviados_ok)) {
        $email_info_resumen = " y enviado por email a " . count($emails_enviados_ok) . " destinatario(s)";
    }
    if (!empty($errores_envio)) {
        $email_info_resumen .= " (" . count($errores_envio) . " fallos de entrega)";
    }

    write_log("Documento oficial '{$tipo}' (#{$id_competicion}) publicado en docs/{$id_competicion}-{$tipo}.pdf por " . ($_SESSION['username'] ?? 'Secretario') . $email_info_resumen, "SUCCESS");

    $msg_exito = "¡{$tipo_label} publicado con éxito en la web!" . $email_info_resumen;

    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => $msg_exito, 
            'tipo' => $tipo,
            'file' => "docs/{$id_competicion}-{$tipo}.pdf",
            'filename' => "{$id_competicion}-{$tipo}.pdf",
            'file_size' => round(filesize($target_file) / 1024, 1) . ' KB',
            'updated_at' => date('d/m/Y H:i'),
            'emails_enviados' => $emails_enviados_ok,
            'errores_envio' => $errores_envio
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
            // Si es clasificacion o resultados, borrar ambas si existieran
            if ($tipo === 'clasificacion' && file_exists($docs_dir . "{$id_competicion}-resultados.pdf")) {
                @unlink($docs_dir . "{$id_competicion}-resultados.pdf");
            } elseif ($tipo === 'resultados' && file_exists($docs_dir . "{$id_competicion}-clasificacion.pdf")) {
                @unlink($docs_dir . "{$id_competicion}-clasificacion.pdf");
            }

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
