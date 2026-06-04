<?php
include('security.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'analisis_especifico') {
    $tipo = $_POST['tipo'];
    $registros = [];
    $titulo = "";
    $mensaje = "";

    switch ($tipo) {
        case 'deportistas_duplicados':
            $titulo = "Deportistas Duplicadas";
            $mensaje = "Se han detectado nadadoras con el mismo nombre y apellidos o misma licencia:";
            // Por nombre y apellidos
            $q = "SELECT GROUP_CONCAT(id) as ids, nombre, apellidos, COUNT(*) as n FROM nadadoras GROUP BY nombre, apellidos HAVING n > 1";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['ids'], 'nombre' => $r['nombre']." ".$r['apellidos'], 'detalle' => "Repetida ".$r['n']." veces"];
            }
            // Por licencia (si no está vacía)
            $q2 = "SELECT GROUP_CONCAT(id) as ids, licencia, COUNT(*) as n FROM nadadoras WHERE licencia != '' AND licencia IS NOT NULL GROUP BY licencia HAVING n > 1";
            $res2 = mysqli_query($connection, $q2);
            while($r = mysqli_fetch_assoc($res2)) {
                $registros[] = ['id' => $r['ids'], 'nombre' => "Licencia: ".$r['licencia'], 'detalle' => "Compartida por ".$r['n']." perfiles"];
            }
            break;

        case 'deportistas_sin_inscripciones':
            $titulo = "Deportistas sin Actividad";
            $mensaje = "Nadadoras que no figuran en ninguna lista de salida ni de rutinas:";
            $q = "SELECT n.id, n.nombre, n.apellidos, c.nombre_corto as club 
                  FROM nadadoras n 
                  LEFT JOIN clubes c ON n.club = c.id
                  WHERE n.id NOT IN (SELECT id_nadadora FROM inscripciones_figuras) 
                  AND n.id NOT IN (SELECT id_nadadora FROM rutinas_participantes)
                  LIMIT 100";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['id'], 'nombre' => $r['apellidos'].", ".$r['nombre'], 'detalle' => "Club: ".($r['club'] ?: 'Sin club')];
            }
            break;

        case 'jueces_duplicados':
            $titulo = "Jueces Duplicados";
            $mensaje = "Fichas de jueces con datos idénticos detectadas:";
            $q = "SELECT GROUP_CONCAT(id) as ids, nombre, apellidos, COUNT(*) as n FROM jueces GROUP BY nombre, apellidos HAVING n > 1";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['ids'], 'nombre' => $r['nombre']." ".$r['apellidos'], 'detalle' => "Existen ".$r['n']." fichas para este nombre"];
            }
            break;

        case 'jueces_sin_cuenta':
            $titulo = "Jueces sin Vinculación Web";
            $mensaje = "Jueces del censo que no tienen un usuario web asociado para ver sus auditorías:";
            $q = "SELECT j.id, j.nombre, j.apellidos, f.nombre_corto as fed 
                  FROM jueces j 
                  LEFT JOIN federaciones f ON j.federacion = f.id
                  WHERE j.id NOT IN (SELECT id_juez_v3 FROM usuarios WHERE id_juez_v3 IS NOT NULL)
                  AND j.activo = 1";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['id'], 'nombre' => $r['nombre']." ".$r['apellidos'], 'detalle' => "Fed: ".($r['fed'] ?: 'N/A')];
            }
            break;

        case 'fases_vacias':
            $titulo = "Fases sin Inscripciones";
            $mensaje = "Fases activas que no tienen ninguna nadadora inscrita:";
            $q = "SELECT f.id, cat.nombre as cat, fig.nombre as fig, c.nombre as comp 
                  FROM fases f
                  JOIN categorias cat ON f.id_categoria = cat.id
                  LEFT JOIN figuras fig ON f.id_figura = fig.id
                  JOIN competiciones c ON f.id_competicion = c.id
                  WHERE f.id NOT IN (SELECT id_fase FROM inscripciones_figuras)
                  AND c.activo = 'si'";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['id'], 'nombre' => ($r['fig'] ?: 'Rutina')." (".$r['cat'].")", 'detalle' => $r['comp']];
            }
            break;

        case 'competiciones_sin_fases':
            $titulo = "Eventos sin Configuración";
            $mensaje = "Competiciones dadas de alta que no contienen ninguna fase técnica:";
            $q = "SELECT id, nombre, fecha FROM competiciones WHERE id NOT IN (SELECT id_competicion FROM fases)";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['id'], 'nombre' => $r['nombre'], 'detalle' => "Fecha: ".$r['fecha']];
            }
            break;

        case 'notas_huerfanas':
            $titulo = "Integridad de Notas";
            $mensaje = "Se han encontrado notas que apuntan a paneles eliminados. SE RECOMIENDA LIMPIEZA:";
            // Figuras
            $q = "SELECT f.id as fase_id, c.nombre as comp, pj.id_panel_juez, COUNT(*) as n 
                  FROM puntuaciones_jueces pj
                  JOIN inscripciones_figuras ifig ON pj.id_inscripcion_figuras = ifig.id
                  JOIN fases f ON ifig.id_fase = f.id
                  JOIN competiciones c ON f.id_competicion = c.id
                  WHERE pj.id_panel_juez NOT IN (SELECT id FROM panel_jueces)
                  GROUP BY pj.id_panel_juez";
            $res = mysqli_query($connection, $q);
            while($r = mysqli_fetch_assoc($res)) {
                $registros[] = ['id' => $r['id_panel_juez'], 'nombre' => "Fase #".$r['fase_id']." (Figuras)", 'detalle' => $r['n']." notas huérfanas en ".$r['comp']];
            }
            // Rutinas
            $q2 = "SELECT r.id as rut_id, c.nombre as comp, pj.id_panel_juez, COUNT(*) as n 
                   FROM puntuaciones_jueces pj
                   JOIN rutinas r ON pj.id_rutina = r.id
                   JOIN competiciones c ON r.id_competicion = c.id
                   WHERE pj.id_panel_juez NOT IN (SELECT id FROM panel_jueces)
                   GROUP BY pj.id_panel_juez";
            $res2 = mysqli_query($connection, $q2);
            while($r2 = mysqli_fetch_assoc($res2)) {
                $registros[] = ['id' => $r2['id_panel_juez'], 'nombre' => "Rutina #".$r2['rut_id'], 'detalle' => $r2['n']." notas huérfanas en ".$r2['comp']];
            }
            break;

            case 'archivos_musica_huerfanos':
            $titulo = "Archivos de Música Huérfanos";
            $mensaje = "Archivos MP3 detectados en el servidor que no pertenecen a ninguna rutina activa en su carpeta correspondiente:";
            $path_base = './public/music/';
            if (is_dir($path_base)) {
                $items = scandir($path_base);
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $comp_path = $path_base . $item;
                    if (is_dir($comp_path)) {
                        $id_comp = $item;
                        $comp_exists = false;
                        $comp_name = "COMPETICIÓN NO ENCONTRADA";

                        if (is_numeric($id_comp)) {
                            $q_comp = "SELECT nombre FROM competiciones WHERE id = " . (int)$id_comp;
                            $res_comp = mysqli_query($connection, $q_comp);
                            if ($res_comp && mysqli_num_rows($res_comp) > 0) {
                                $comp_exists = true;
                                $comp_name = mysqli_fetch_assoc($res_comp)['nombre'];
                            }
                        }

                        $files = scandir($comp_path);
                        foreach ($files as $file) {
                            if (pathinfo($file, PATHINFO_EXTENSION) === 'mp3') {
                                $id_rutina = pathinfo($file, PATHINFO_FILENAME);
                                $is_orphan = true;

                                if (is_numeric($id_rutina) && $comp_exists) {
                                    $q_rut = "SELECT id FROM rutinas WHERE id = " . (int)$id_rutina . " AND id_competicion = " . (int)$id_comp;
                                    $res_rut = mysqli_query($connection, $q_rut);
                                    if ($res_rut && mysqli_num_rows($res_rut) > 0) {
                                        $is_orphan = false;
                                    }
                                }

                                if ($is_orphan) {
                                    $registros[] = [
                                        'id' => $id_comp . '/' . $id_rutina,
                                        'nombre' => "MP3: " . $file,
                                        'detalle' => "Carpeta: " . $id_comp . " (" . $comp_name . ")"
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            break;
            }


    echo json_encode([
        'status' => 'success',
        'titulo' => $titulo,
        'mensaje' => $mensaje,
        'registros' => $registros
    ]);
    exit;
}

// Acción: Obtener lista de competiciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_competiciones_list') {
    $q = "SELECT id, nombre, fecha FROM competiciones ORDER BY fecha DESC";
    $res = mysqli_query($connection, $q);
    $comps = [];
    while($r = mysqli_fetch_assoc($res)) { $comps[] = $r; }
    echo json_encode(['status' => 'success', 'competiciones' => $comps]);
    exit;
}

// Acción: Simular borrado en cascada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'simular_cascada') {
    $ids = json_decode($_POST['ids'], true);
    if (!$ids) exit;
    $id_list = implode(',', array_map('intval', $ids));
    
    $conteo = [];
    $total_global = 0;
    $db_space_bytes = 0;

    // Obtener tamaños promedio de tablas para estimación
    $table_stats = [];
    $q_stats = "SELECT TABLE_NAME, AVG_ROW_LENGTH FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()";
    $res_stats = mysqli_query($connection, $q_stats);
    while($s = mysqli_fetch_assoc($res_stats)) {
        $table_stats[$s['TABLE_NAME']] = (int)$s['AVG_ROW_LENGTH'];
    }

    $tablas_map = [
        'competiciones' => "SELECT COUNT(*) FROM competiciones WHERE id IN ($id_list)",
        'fases' => "SELECT COUNT(*) FROM fases WHERE id_competicion IN ($id_list)",
        'inscripciones_figuras' => "SELECT COUNT(*) FROM inscripciones_figuras WHERE id_competicion IN ($id_list)",
        'rutinas' => "SELECT COUNT(*) FROM rutinas WHERE id_competicion IN ($id_list)",
        'paneles' => "SELECT COUNT(*) FROM paneles WHERE id_competicion IN ($id_list)",
        'panel_jueces' => "SELECT COUNT(*) FROM panel_jueces WHERE id_competicion IN ($id_list)",
        'rutinas_participantes' => "SELECT COUNT(*) FROM rutinas_participantes WHERE id_competicion IN ($id_list)",
        'puntuaciones_jueces' => "SELECT COUNT(*) FROM puntuaciones_jueces WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
        'puntuaciones_elementos' => "SELECT COUNT(*) FROM puntuaciones_elementos WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
        'puntuaciones_paneles' => "SELECT COUNT(*) FROM puntuaciones_paneles WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
        'hibridos_rutina' => "SELECT COUNT(*) FROM hibridos_rutina WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
        'resultados_figuras' => "SELECT COUNT(*) FROM resultados_figuras WHERE id_competicion IN ($id_list)",
        'resultados_figuras_categorias' => "SELECT COUNT(*) FROM resultados_figuras_categorias WHERE id_competicion IN ($id_list)",
        'resultados_rutinas_categorias' => "SELECT COUNT(*) FROM resultados_rutinas_categorias WHERE id_competicion IN ($id_list)",
        'auditoria_jueces_stats' => "SELECT COUNT(*) FROM auditoria_jueces_stats WHERE id_competicion IN ($id_list)",
        'auditoria_jueces_puntos' => "SELECT COUNT(*) FROM auditoria_jueces_puntos WHERE id_competicion IN ($id_list)",
        'penalizaciones_rutinas' => "SELECT COUNT(*) FROM penalizaciones_rutinas WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
        'penalizaciones_artistico' => "SELECT COUNT(*) FROM penalizaciones_artistico WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
        'penalizaciones_elementos' => "SELECT COUNT(*) FROM penalizaciones_elementos WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))"
    ];

    foreach ($tablas_map as $table_name => $sql) {
        $res = mysqli_query($connection, $sql);
        $count = mysqli_fetch_array($res)[0];
        $label = ucwords(str_replace('_', ' ', $table_name));
        $conteo[] = ['entidad' => $label, 'total' => (int)$count];
        $total_global += $count;
        $db_space_bytes += ($count * ($table_stats[$table_name] ?? 100));
    }

    // Conteo de archivos físicos (Música y PDFs)
    $total_mp3 = 0;
    $total_pdf = 0;
    $disk_space_bytes = 0;

    foreach ($ids as $id_c) {
        // Música
        $dir_music = './public/music/' . (int)$id_c;
        if (is_dir($dir_music)) {
            $files_mp3 = array_diff(scandir($dir_music), array('.', '..'));
            foreach ($files_mp3 as $f_mp3) {
                $total_mp3++;
                $disk_space_bytes += filesize($dir_music . '/' . $f_mp3);
            }
        }

        // Documentos PDF en ./docs/
        $dir_docs = './docs/';
        if (is_dir($dir_docs)) {
            $files_docs = scandir($dir_docs);
            $prefix = (int)$id_c . '_';
            foreach ($files_docs as $f_doc) {
                if (strpos($f_doc, $prefix) === 0 && pathinfo($f_doc, PATHINFO_EXTENSION) === 'pdf') {
                    $total_pdf++;
                    $disk_space_bytes += filesize($dir_docs . $f_doc);
                }
            }
        }
    }
    
    $conteo[] = ['entidad' => 'Archivos Música (MP3)', 'total' => $total_mp3];
    $conteo[] = ['entidad' => 'Documentos Generados (PDF)', 'total' => $total_pdf];

    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }

    echo json_encode([
        'status' => 'success', 
        'conteo' => $conteo, 
        'total_global' => $total_global,
        'db_reclaimed' => formatBytes($db_space_bytes),
        'disk_reclaimed' => formatBytes($disk_space_bytes)
    ]);
    exit;
}

// Acción: Ejecutar borrado en cascada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ejecutar_cascada') {
    $ids = json_decode($_POST['ids'], true);
    if (!$ids) exit;
    $id_list = implode(',', array_map('intval', $ids));

    mysqli_begin_transaction($connection);
    try {
        $total_borrado = 0;

        // 0. Borrar archivos físicos
        foreach ($ids as $id_c) {
            // A. Música
            $dir_music = './public/music/' . (int)$id_c;
            if (is_dir($dir_music)) {
                $files = array_diff(scandir($dir_music), array('.', '..'));
                foreach ($files as $f) {
                    @unlink($dir_music . '/' . $f);
                }
                if (@rmdir($dir_music)) {
                    write_log("LIMPIEZA CASCADA: Carpeta de música eliminada para competición #$id_c", "INFO");
                }
            }

            // B. Documentos PDF (docs/)
            $dir_docs = './docs/';
            if (is_dir($dir_docs)) {
                $files_docs = scandir($dir_docs);
                $prefix = (int)$id_c . '_';
                foreach ($files_docs as $f_doc) {
                    if (strpos($f_doc, $prefix) === 0 && pathinfo($f_doc, PATHINFO_EXTENSION) === 'pdf') {
                        if (@unlink($dir_docs . $f_doc)) {
                            $total_borrado++; // Contamos PDFs borrados como registros afectados
                        }
                    }
                }
            }
        }

        // 1. Borrar Notas y dependencias profundas (usando subqueries)
        $queries_dep = [
            "DELETE FROM puntuaciones_jueces WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
            "DELETE FROM puntuaciones_elementos WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
            "DELETE FROM puntuaciones_paneles WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
            "DELETE FROM penalizaciones_rutinas WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
            "DELETE FROM penalizaciones_artistico WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
            "DELETE FROM penalizaciones_elementos WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
            "DELETE FROM hibridos_rutina WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))"
        ];

        // 2. Borrar entidades vinculadas directamente a id_competicion
        $queries_direct = [
            "DELETE FROM panel_jueces WHERE id_competicion IN ($id_list)",
            "DELETE FROM paneles WHERE id_competicion IN ($id_list)",
            "DELETE FROM rutinas_participantes WHERE id_competicion IN ($id_list)",
            "DELETE FROM rutinas WHERE id_competicion IN ($id_list)",
            "DELETE FROM inscripciones_figuras WHERE id_competicion IN ($id_list)",
            "DELETE FROM fases WHERE id_competicion IN ($id_list)",
            "DELETE FROM resultados_figuras WHERE id_competicion IN ($id_list)",
            "DELETE FROM resultados_figuras_categorias WHERE id_competicion IN ($id_list)",
            "DELETE FROM resultados_rutinas_categorias WHERE id_competicion IN ($id_list)",
            "DELETE FROM auditoria_jueces_stats WHERE id_competicion IN ($id_list)",
            "DELETE FROM auditoria_jueces_puntos WHERE id_competicion IN ($id_list)",
            "DELETE FROM calculos_clasificacion WHERE id_competicion IN ($id_list)",
            "DELETE FROM competiciones WHERE id IN ($id_list)"
        ];

        foreach (array_merge($queries_dep, $queries_direct) as $q) {
            if (mysqli_query($connection, $q)) {
                $total_borrado += mysqli_affected_rows($connection);
            } else {
                throw new Exception(mysqli_error($connection));
            }
        }

        mysqli_commit($connection);
        write_log("LIMPIEZA EN CASCADA: Eliminadas competiciones [$id_list]. Total registros borrados: $total_borrado", "SECURITY");
        echo json_encode(['status' => 'success', 'total_borrado' => $total_borrado]);

        } catch (Exception $e) {
        mysqli_rollback($connection);
        echo json_encode(['status' => 'error', 'message' => "Fallo en cascada: ".$e->getMessage()]);
        }
        exit;
        }

        // Acción: Borrar archivo de música huérfano (y carpeta si queda vacía)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'borrar_archivo_huerfano') {
        $rel_path = $_POST['id']; // Recibimos algo como "52/710"
        if (empty($rel_path) || strpos($rel_path, '..') !== false) {
        echo json_encode(['status' => 'error', 'message' => 'Ruta no válida']);
        exit;
        }

        $path_base = './public/music/';
        $file_path = $path_base . $rel_path . '.mp3';
        $dir_path  = dirname($file_path);

        if (file_exists($file_path)) {
        if (unlink($file_path)) {
            write_log("ARCHIVO HUÉRFANO ELIMINADO: $file_path", "INFO");

            // Si la carpeta está vacía o la competición no existe, intentamos borrarla
            $parts = explode('/', $rel_path);
            $id_comp = (int)$parts[0];

            // Verificar si la competición existe
            $q_check = "SELECT id FROM competiciones WHERE id = $id_comp";
            $comp_exists = mysqli_num_rows(mysqli_query($connection, $q_check)) > 0;

            // Escanear carpeta para ver si queda algo
            $files_left = array_diff(scandir($dir_path), array('.', '..'));

            $borrado_carpeta = false;
            if (empty($files_left) || !$comp_exists) {
                // Borrar todos los archivos si la competición no existe
                if (!$comp_exists) {
                    foreach ($files_left as $f) {
                        @unlink($dir_path . '/' . $f);
                    }
                }

                if (@rmdir($dir_path)) {
                    write_log("CARPETA DE MÚSICA ELIMINADA (VACÍA O SIN COMPETICIÓN): $dir_path", "INFO");
                    $borrado_carpeta = true;
                }
            }

            echo json_encode([
                'status' => 'success', 
                'message' => 'Archivo eliminado correctamente',
                'carpeta_borrada' => $borrado_carpeta
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el archivo físico']);
        }
        } else {
        echo json_encode(['status' => 'error', 'message' => 'El archivo ya no existe']);
        }
        exit;
        }
        ?>