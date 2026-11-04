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

// Acción: Simular borrado en cascada / modular
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'simular_cascada') {
    $ids = json_decode($_POST['ids'], true);
    if (!$ids) exit;
    $id_list = implode(',', array_map('intval', $ids));
    
    // Obtener información de las competiciones seleccionadas
    $comps = [];
    $res_comps = mysqli_query($connection, "SELECT id, nombre, fecha FROM competiciones WHERE id IN ($id_list) ORDER BY fecha DESC");
    if ($res_comps) {
        while($c = mysqli_fetch_assoc($res_comps)) {
            $comps[] = $c;
        }
    }

    // Obtener tamaños promedio de tablas para estimación de BD
    $table_stats = [];
    $q_stats = "SELECT TABLE_NAME, AVG_ROW_LENGTH FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()";
    $res_stats = mysqli_query($connection, $q_stats);
    if ($res_stats) {
        while($s = mysqli_fetch_assoc($res_stats)) {
            $table_stats[$s['TABLE_NAME']] = (int)$s['AVG_ROW_LENGTH'];
        }
    }

    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }

    // 1. MÚSICA (Archivos MP3 y referencias en rutinas)
    $total_mp3 = 0;
    $disk_music_bytes = 0;
    foreach ($ids as $id_c) {
        $dir_music = './public/music/' . (int)$id_c;
        if (is_dir($dir_music)) {
            $files_mp3 = array_diff(scandir($dir_music), array('.', '..'));
            foreach ($files_mp3 as $f_mp3) {
                $total_mp3++;
                $disk_music_bytes += filesize($dir_music . '/' . $f_mp3);
            }
        }
    }
    $res_rut_mus = mysqli_query($connection, "SELECT COUNT(*) FROM rutinas WHERE id_competicion IN ($id_list) AND (music_name IS NOT NULL OR music_original_name IS NOT NULL)");
    $rut_con_musica = $res_rut_mus ? (int)mysqli_fetch_array($res_rut_mus)[0] : 0;

    // 2. COACH CARDS & ELEMENTOS DD
    $res_cc = mysqli_query($connection, "SELECT COUNT(*) FROM hibridos_rutina WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))");
    $total_cc_elementos = $res_cc ? (int)mysqli_fetch_array($res_cc)[0] : 0;
    $res_rut_cc = mysqli_query($connection, "SELECT COUNT(*) FROM rutinas WHERE id_competicion IN ($id_list) AND dd_total > 0");
    $rut_con_cc = $res_rut_cc ? (int)mysqli_fetch_array($res_rut_cc)[0] : 0;

    // 3. INSCRIPCIONES & RUTINAS (DEPORTISTAS)
    $res_fig = mysqli_query($connection, "SELECT COUNT(*) FROM inscripciones_figuras WHERE id_competicion IN ($id_list)");
    $total_figuras_insc = $res_fig ? (int)mysqli_fetch_array($res_fig)[0] : 0;

    $res_rut = mysqli_query($connection, "SELECT COUNT(*) FROM rutinas WHERE id_competicion IN ($id_list)");
    $total_rutinas = $res_rut ? (int)mysqli_fetch_array($res_rut)[0] : 0;

    $res_part = mysqli_query($connection, "SELECT COUNT(*) FROM rutinas_participantes WHERE id_competicion IN ($id_list) OR id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))");
    $total_participantes = $res_part ? (int)mysqli_fetch_array($res_part)[0] : 0;
    $total_inscripciones_modulo = $total_figuras_insc + $total_rutinas + $total_participantes;

    // 4. PUNTUACIONES, PENALIZACIONES Y RESULTADOS
    $tablas_puntuaciones = [
        'puntuaciones_jueces' => "SELECT COUNT(*) FROM puntuaciones_jueces WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
        'puntuaciones_elementos' => "SELECT COUNT(*) FROM puntuaciones_elementos WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
        'puntuaciones_paneles' => "SELECT COUNT(*) FROM puntuaciones_paneles WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
        'penalizaciones_rutinas' => "SELECT COUNT(*) FROM penalizaciones_rutinas WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
        'penalizaciones_artistico' => "SELECT COUNT(*) FROM penalizaciones_artistico WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
        'penalizaciones_elementos' => "SELECT COUNT(*) FROM penalizaciones_elementos WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
        'errores_sincronizacion' => "SELECT COUNT(*) FROM errores_sincronizacion WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
        'resultados_figuras' => "SELECT COUNT(*) FROM resultados_figuras WHERE id_competicion IN ($id_list)",
        'resultados_figuras_categorias' => "SELECT COUNT(*) FROM resultados_figuras_categorias WHERE id_competicion IN ($id_list)",
        'resultados_rutinas_categorias' => "SELECT COUNT(*) FROM resultados_rutinas_categorias WHERE id_competicion IN ($id_list)",
        'auditoria_jueces_stats' => "SELECT COUNT(*) FROM auditoria_jueces_stats WHERE id_competicion IN ($id_list)",
        'auditoria_jueces_puntos' => "SELECT COUNT(*) FROM auditoria_jueces_puntos WHERE id_competicion IN ($id_list)",
        'calculos_clasificacion' => "SELECT COUNT(*) FROM calculos_clasificacion WHERE id_competicion IN ($id_list)"
    ];
    $total_puntuaciones_modulo = 0;
    foreach ($tablas_puntuaciones as $sql_p) {
        $res_p = mysqli_query($connection, $sql_p);
        if ($res_p) {
            $total_puntuaciones_modulo += (int)mysqli_fetch_array($res_p)[0];
        }
    }

    // 5. SORTEOS Y ÓRDENES DE ACTUACIÓN
    $res_sort_fig = mysqli_query($connection, "SELECT COUNT(*) FROM inscripciones_figuras WHERE id_competicion IN ($id_list) AND orden IS NOT NULL AND orden > 0");
    $sort_fig = $res_sort_fig ? (int)mysqli_fetch_array($res_sort_fig)[0] : 0;
    $res_sort_rut = mysqli_query($connection, "SELECT COUNT(*) FROM rutinas WHERE id_competicion IN ($id_list) AND orden != 0 AND orden IS NOT NULL");
    $sort_rut = $res_sort_rut ? (int)mysqli_fetch_array($res_sort_rut)[0] : 0;
    $total_sorteos_modulo = $sort_fig + $sort_rut;

    // 6. PANELES DE JUECES
    $res_pan = mysqli_query($connection, "SELECT COUNT(*) FROM paneles WHERE id_competicion IN ($id_list)");
    $pan_count = $res_pan ? (int)mysqli_fetch_array($res_pan)[0] : 0;
    $res_pan_j = mysqli_query($connection, "SELECT COUNT(*) FROM panel_jueces WHERE id_competicion IN ($id_list)");
    $pan_j_count = $res_pan_j ? (int)mysqli_fetch_array($res_pan_j)[0] : 0;
    $res_pj = mysqli_query($connection, "SELECT COUNT(*) FROM puesto_juez WHERE id_competicion IN ($id_list)");
    $pj_count = $res_pj ? (int)mysqli_fetch_array($res_pj)[0] : 0;
    $total_paneles_modulo = $pan_count + $pan_j_count + $pj_count;

    // 7. DOCUMENTOS PDF GENERADOS (docs/)
    $total_pdf = 0;
    $disk_docs_bytes = 0;
    $dir_docs = './docs/';
    if (is_dir($dir_docs)) {
        $files_docs = scandir($dir_docs);
        foreach ($ids as $id_c) {
            $prefix = (int)$id_c . '_';
            foreach ($files_docs as $f_doc) {
                if (strpos($f_doc, $prefix) === 0 && pathinfo($f_doc, PATHINFO_EXTENSION) === 'pdf') {
                    $total_pdf++;
                    $disk_docs_bytes += filesize($dir_docs . $f_doc);
                }
            }
        }
    }

    // 8. COMPETICIÓN Y FASES TÉCNICAS
    $res_fas = mysqli_query($connection, "SELECT COUNT(*) FROM fases WHERE id_competicion IN ($id_list)");
    $total_fases = $res_fas ? (int)mysqli_fetch_array($res_fas)[0] : 0;
    $total_comps = count($ids);
    $total_competicion_modulo = $total_fases + $total_comps;

    // Totales globales y cálculo de bytes de base de datos
    $tablas_map = array_merge([
        'competiciones' => "SELECT COUNT(*) FROM competiciones WHERE id IN ($id_list)",
        'fases' => "SELECT COUNT(*) FROM fases WHERE id_competicion IN ($id_list)",
        'inscripciones_figuras' => "SELECT COUNT(*) FROM inscripciones_figuras WHERE id_competicion IN ($id_list)",
        'rutinas' => "SELECT COUNT(*) FROM rutinas WHERE id_competicion IN ($id_list)",
        'paneles' => "SELECT COUNT(*) FROM paneles WHERE id_competicion IN ($id_list)",
        'panel_jueces' => "SELECT COUNT(*) FROM panel_jueces WHERE id_competicion IN ($id_list)",
        'rutinas_participantes' => "SELECT COUNT(*) FROM rutinas_participantes WHERE id_competicion IN ($id_list)",
        'hibridos_rutina' => "SELECT COUNT(*) FROM hibridos_rutina WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))"
    ], $tablas_puntuaciones);

    $conteo = [];
    $total_global_db = 0;
    $db_space_bytes = 0;

    foreach ($tablas_map as $table_name => $sql) {
        $res = mysqli_query($connection, $sql);
        $count = $res ? (int)mysqli_fetch_array($res)[0] : 0;
        $label = ucwords(str_replace('_', ' ', $table_name));
        $conteo[] = ['entidad' => $label, 'total' => $count];
        $total_global_db += $count;
        $db_space_bytes += ($count * ($table_stats[$table_name] ?? 120));
    }

    $conteo[] = ['entidad' => 'Archivos Música (MP3)', 'total' => $total_mp3];
    $conteo[] = ['entidad' => 'Documentos Generados (PDF)', 'total' => $total_pdf];

    $total_global = $total_global_db + $total_mp3 + $total_pdf;
    $total_disk_bytes = $disk_music_bytes + $disk_docs_bytes;

    // Construcción del objeto modular para el selector interactivo
    $modulos = [
        'musica' => [
            'key' => 'musica',
            'titulo' => 'Músicas de Rutinas (Archivos MP3)',
            'descripcion' => 'Archivos MP3 en servidor y enlaces de audio en rutinas',
            'icono' => 'fas fa-music',
            'color' => 'indigo',
            'total' => $total_mp3,
            'bytes_disco' => $disk_music_bytes,
            'badge' => $total_mp3 > 0 ? "$total_mp3 MP3 (" . formatBytes($disk_music_bytes) . ")" : '0 archivos',
            'detalle' => "$total_mp3 archivos en disco • $rut_con_musica rutinas con audio vinculado",
            'is_competicion' => false
        ],
        'coach_cards' => [
            'key' => 'coach_cards',
            'titulo' => 'Coach Cards y Dificultad (Híbridos)',
            'descripcion' => 'Elementos técnicos declarados y reseteo de DD total a 0',
            'icono' => 'fas fa-clone',
            'color' => 'blue',
            'total' => $total_cc_elementos,
            'bytes_disco' => 0,
            'badge' => "$total_cc_elementos elementos",
            'detalle' => "$total_cc_elementos movimientos en hibridos_rutina • $rut_con_cc rutinas con DD",
            'is_competicion' => false
        ],
        'inscripciones' => [
            'key' => 'inscripciones',
            'titulo' => 'Inscripciones y Rutinas (Deportistas)',
            'descripcion' => 'Inscripciones de figuras, rutinas y nadadoras participantes asignadas',
            'icono' => 'fas fa-person-swimming',
            'color' => 'pink',
            'total' => $total_inscripciones_modulo,
            'bytes_disco' => 0,
            'badge' => "$total_inscripciones_modulo registros",
            'detalle' => "$total_figuras_insc figuras • $total_rutinas rutinas • $total_participantes deportistas asignadas",
            'is_competicion' => false
        ],
        'puntuaciones' => [
            'key' => 'puntuaciones',
            'titulo' => 'Puntuaciones, Penalizaciones y BIAS',
            'descripcion' => 'Notas de jueces, paneles, elementos, actas oficiales y clasificaciones',
            'icono' => 'fas fa-scale-balanced',
            'color' => 'amber',
            'total' => $total_puntuaciones_modulo,
            'bytes_disco' => 0,
            'badge' => "$total_puntuaciones_modulo notas",
            'detalle' => "$total_puntuaciones_modulo registros entre jueces, paneles, sincronización y BIAS",
            'is_competicion' => false
        ],
        'sorteos' => [
            'key' => 'sorteos',
            'titulo' => 'Sorteos y Órdenes de Actuación',
            'descripcion' => 'Resetear los turnos asignados a nadadoras y rutinas a estado inicial',
            'icono' => 'fas fa-shuffle',
            'color' => 'purple',
            'total' => $total_sorteos_modulo,
            'bytes_disco' => 0,
            'badge' => "$total_sorteos_modulo turnos",
            'detalle' => "$sort_fig turnos en figuras • $sort_rut turnos en rutinas",
            'is_competicion' => false
        ],
        'paneles' => [
            'key' => 'paneles',
            'titulo' => 'Paneles de Jueces y Asignaciones',
            'descripcion' => 'Configuración de paneles técnicos y jueces convocados a la mesa',
            'icono' => 'fas fa-users-gear',
            'color' => 'emerald',
            'total' => $total_paneles_modulo,
            'bytes_disco' => 0,
            'badge' => "$total_paneles_modulo registros",
            'detalle' => "$pan_count paneles técnicos • $pan_j_count asignaciones arbitrales",
            'is_competicion' => false
        ],
        'documentos' => [
            'key' => 'documentos',
            'titulo' => 'Documentos Oficiales Generados (PDF)',
            'descripcion' => 'Actas, orden de salida y listados almacenados en la carpeta docs/',
            'icono' => 'fas fa-file-pdf',
            'color' => 'red',
            'total' => $total_pdf,
            'bytes_disco' => $disk_docs_bytes,
            'badge' => $total_pdf > 0 ? "$total_pdf PDFs (" . formatBytes($disk_docs_bytes) . ")" : '0 PDFs',
            'detalle' => "$total_pdf archivos PDF generados para este evento",
            'is_competicion' => false
        ],
        'competicion' => [
            'key' => 'competicion',
            'titulo' => 'Competición y Fases Técnicas (Cascada Total)',
            'descripcion' => 'Elimina las fases técnicas y la propia competición del calendario oficial',
            'icono' => 'fas fa-trash-can',
            'color' => 'slate',
            'total' => $total_competicion_modulo,
            'bytes_disco' => 0,
            'badge' => '⚠️ Eliminación Total',
            'detalle' => "$total_comps competición • $total_fases fases técnicas asociadas",
            'is_competicion' => true
        ]
    ];

    echo json_encode([
        'status' => 'success',
        'competiciones' => $comps,
        'modulos' => $modulos,
        'conteo' => $conteo,
        'total_global' => $total_global,
        'db_reclaimed' => formatBytes($db_space_bytes),
        'disk_reclaimed' => formatBytes($total_disk_bytes)
    ]);
    exit;
}

// Acción: Ejecutar borrado en cascada / modular
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ejecutar_cascada') {
    $ids = json_decode($_POST['ids'], true);
    if (!$ids) exit;
    $id_list = implode(',', array_map('intval', $ids));

    // Módulos seleccionados por el usuario
    $modulos = isset($_POST['modulos']) ? json_decode($_POST['modulos'], true) : ['todo'];
    if (empty($modulos)) {
        echo json_encode(['status' => 'error', 'message' => 'No se ha seleccionado ningún módulo para limpiar.']);
        exit;
    }

    $borrar_todo = in_array('todo', $modulos) || in_array('competicion', $modulos);

    mysqli_begin_transaction($connection);
    try {
        $total_borrado = 0;
        $detalles_ejecucion = [];

        // 1. MÚSICA (Archivos MP3 y enlaces de audio)
        if ($borrar_todo || in_array('musica', $modulos)) {
            $mp3_borrados = 0;
            foreach ($ids as $id_c) {
                $dir_music = './public/music/' . (int)$id_c;
                if (is_dir($dir_music)) {
                    $files = array_diff(scandir($dir_music), array('.', '..'));
                    foreach ($files as $f) {
                        if (@unlink($dir_music . '/' . $f)) {
                            $mp3_borrados++;
                        }
                    }
                    @rmdir($dir_music);
                }
            }
            // Resetear columnas de música en rutinas si no se van a borrar las rutinas enteras
            mysqli_query($connection, "UPDATE rutinas SET music_name = NULL, music_original_name = NULL, music_duration = NULL, musica = NULL WHERE id_competicion IN ($id_list)");
            $total_borrado += $mp3_borrados;
            $detalles_ejecucion[] = "$mp3_borrados archivos MP3 eliminados y campos de música reseteados";
        }

        // 2. DOCUMENTOS PDF (docs/)
        if ($borrar_todo || in_array('documentos', $modulos)) {
            $pdf_borrados = 0;
            $dir_docs = './docs/';
            if (is_dir($dir_docs)) {
                $files_docs = scandir($dir_docs);
                foreach ($ids as $id_c) {
                    $prefix = (int)$id_c . '_';
                    foreach ($files_docs as $f_doc) {
                        if (strpos($f_doc, $prefix) === 0 && pathinfo($f_doc, PATHINFO_EXTENSION) === 'pdf') {
                            if (@unlink($dir_docs . $f_doc)) {
                                $pdf_borrados++;
                            }
                        }
                    }
                }
            }
            $total_borrado += $pdf_borrados;
            $detalles_ejecucion[] = "$pdf_borrados documentos PDF eliminados";
        }

        // 3. PUNTUACIONES, PENALIZACIONES Y RESULTADOS
        if ($borrar_todo || in_array('puntuaciones', $modulos) || in_array('inscripciones', $modulos)) {
            $queries_punt = [
                "DELETE FROM puntuaciones_jueces WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
                "DELETE FROM puntuaciones_elementos WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
                "DELETE FROM puntuaciones_paneles WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
                "DELETE FROM penalizaciones_rutinas WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list)) OR id_inscripcion_figuras IN (SELECT id FROM inscripciones_figuras WHERE id_competicion IN ($id_list))",
                "DELETE FROM penalizaciones_artistico WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
                "DELETE FROM penalizaciones_elementos WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
                "DELETE FROM errores_sincronizacion WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))",
                "DELETE FROM resultados_figuras WHERE id_competicion IN ($id_list)",
                "DELETE FROM resultados_figuras_categorias WHERE id_competicion IN ($id_list)",
                "DELETE FROM resultados_rutinas_categorias WHERE id_competicion IN ($id_list)",
                "DELETE FROM auditoria_jueces_stats WHERE id_competicion IN ($id_list)",
                "DELETE FROM auditoria_jueces_puntos WHERE id_competicion IN ($id_list)",
                "DELETE FROM calculos_clasificacion WHERE id_competicion IN ($id_list)"
            ];
            foreach ($queries_punt as $qp) {
                if (mysqli_query($connection, $qp)) {
                    $total_borrado += mysqli_affected_rows($connection);
                } else {
                    throw new Exception("Error al purgar notas: " . mysqli_error($connection));
                }
            }

            // Si se conservan las rutinas e inscripciones, reseteamos sus notas a NULL
            if (!$borrar_todo && !in_array('inscripciones', $modulos)) {
                mysqli_query($connection, "UPDATE rutinas SET nota_panel_elementos = NULL, nota_panel_sincro = NULL, penalizaciones_elementos = NULL, nota_final_panel_elementos = NULL, nota_panel_ia = NULL, penalizaciones_ia = NULL, nota_final_panel_ia = NULL, nota_rutina = NULL, penalizaciones_rutina = NULL, nota_final = NULL, diferencia = NULL, posicion = NULL, puntos = NULL WHERE id_competicion IN ($id_list)");
                mysqli_query($connection, "UPDATE inscripciones_figuras SET nota_total = NULL, nota_media = NULL, nota_final = NULL, posicion = NULL, diferencia = NULL WHERE id_competicion IN ($id_list)");
            }
            $detalles_ejecucion[] = "Puntuaciones, penalizaciones y clasificaciones purgadas con éxito";
        }

        // 4. COACH CARDS & ELEMENTOS DD
        if ($borrar_todo || in_array('coach_cards', $modulos) || in_array('inscripciones', $modulos)) {
            $q_del_cc = "DELETE FROM hibridos_rutina WHERE id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))";
            if (mysqli_query($connection, $q_del_cc)) {
                $total_borrado += mysqli_affected_rows($connection);
            } else {
                throw new Exception("Error al purgar coach cards: " . mysqli_error($connection));
            }

            if (!$borrar_todo && !in_array('inscripciones', $modulos)) {
                mysqli_query($connection, "UPDATE rutinas SET dd_total = 0, marca_cc = NULL, id_coach_card = NULL WHERE id_competicion IN ($id_list)");
            }
            $detalles_ejecucion[] = "Coach cards y elementos de dificultad reseteados";
        }

        // 5. SORTEOS Y ÓRDENES DE ACTUACIÓN
        if ($borrar_todo || in_array('sorteos', $modulos)) {
            mysqli_query($connection, "UPDATE rutinas SET orden = 0 WHERE id_competicion IN ($id_list)");
            mysqli_query($connection, "UPDATE inscripciones_figuras SET orden = NULL WHERE id_competicion IN ($id_list)");
            $detalles_ejecucion[] = "Órdenes de salida y sorteos reseteados";
        }

        // 6. INSCRIPCIONES Y RUTINAS (DEPORTISTAS)
        if ($borrar_todo || in_array('inscripciones', $modulos)) {
            // Eliminar participantes de rutinas
            $q_del_parts = "DELETE FROM rutinas_participantes WHERE id_competicion IN ($id_list) OR id_rutina IN (SELECT id FROM rutinas WHERE id_competicion IN ($id_list))";
            if (mysqli_query($connection, $q_del_parts)) {
                $total_borrado += mysqli_affected_rows($connection);
            }

            // Eliminar rutinas
            $q_del_ruts = "DELETE FROM rutinas WHERE id_competicion IN ($id_list)";
            if (mysqli_query($connection, $q_del_ruts)) {
                $total_borrado += mysqli_affected_rows($connection);
            }

            // Eliminar inscripciones de figuras
            $q_del_fig = "DELETE FROM inscripciones_figuras WHERE id_competicion IN ($id_list)";
            if (mysqli_query($connection, $q_del_fig)) {
                $total_borrado += mysqli_affected_rows($connection);
            }

            $detalles_ejecucion[] = "Inscripciones de figuras, rutinas y deportistas participantes eliminadas";
        }

        // 7. PANELES DE JUECES
        if ($borrar_todo || in_array('paneles', $modulos)) {
            mysqli_query($connection, "DELETE FROM panel_jueces WHERE id_competicion IN ($id_list)");
            $total_borrado += mysqli_affected_rows($connection);
            mysqli_query($connection, "DELETE FROM puesto_juez WHERE id_competicion IN ($id_list)");
            $total_borrado += mysqli_affected_rows($connection);
            mysqli_query($connection, "DELETE FROM paneles WHERE id_competicion IN ($id_list)");
            $total_borrado += mysqli_affected_rows($connection);
            $detalles_ejecucion[] = "Paneles de jueces y asignaciones de mesa eliminados";
        }

        // 8. COMPETICIÓN Y FASES TÉCNICAS (SOLO SI SE SOLICITÓ BORRADO TOTAL)
        if ($borrar_todo) {
            mysqli_query($connection, "DELETE FROM fases WHERE id_competicion IN ($id_list)");
            $total_borrado += mysqli_affected_rows($connection);
            mysqli_query($connection, "DELETE FROM competiciones WHERE id IN ($id_list)");
            $total_borrado += mysqli_affected_rows($connection);
            $detalles_ejecucion[] = "Fases técnicas y competiciones eliminadas por completo del calendario";
        }

        mysqli_commit($connection);
        
        $mod_str = implode(', ', $modulos);
        $tipo_log = $borrar_todo ? 'CASCADA TOTAL' : 'LIMPIEZA SELECTIVA';
        write_log("LIMPIEZA DE COMPETICIÓN ($tipo_log): IDs [$id_list]. Módulos: [$mod_str]. Registros/Archivos afectados: $total_borrado", "SECURITY");

        echo json_encode([
            'status' => 'success',
            'total_borrado' => $total_borrado,
            'detalles' => $detalles_ejecucion
        ]);

    } catch (Exception $e) {
        mysqli_rollback($connection);
        echo json_encode(['status' => 'error', 'message' => "Fallo en la limpieza: " . $e->getMessage()]);
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