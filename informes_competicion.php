<?php
include('security.php');

// SEGURIDAD: Solo Administradores y Secretarios (roles 1, 2, 3) tienen acceso a este módulo
$roles_permitidos = [1, 2, 3];
if (!in_array($_SESSION['id_rol'] ?? 0, $roles_permitidos)) {
    $_SESSION['no_acceso'] = "Acceso restringido a Administradores y Secretarios.";
    header('Location: index.php');
    exit();
}

include('includes/header.php');
include('includes/navbar.php');

// Obtener datos de la competición (soporte para $_GET['id_competicion'] o $_GET['id'] con persistencia en sesión)
$id_comp_param = isset($_GET['id_competicion']) ? (int)$_GET['id_competicion'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if ($id_comp_param > 0) {
    $_SESSION['id_competicion_usuario'] = $id_comp_param;
    $id_competicion = $id_comp_param;
} else {
    $id_competicion = (int)($_SESSION['id_competicion_usuario'] ?? ($_SESSION['id_competicion_activa'] ?? 0));
}

$comp_data = null;
$total_nadadoras = 0;
$total_rutinas = 0;
$fases_puntuadas = 0;
$total_fases = 0;
$porcentaje_fases = 0;
$clave_liga_comp = '';

if ($id_competicion > 0) {
    // 0. Datos competición
    $q_comp = "SELECT * FROM competiciones WHERE id = '$id_competicion' LIMIT 1";
    $res_comp = mysqli_query($connection, $q_comp);
    if ($res_comp && $row_c = mysqli_fetch_assoc($res_comp)) {
        $comp_data = $row_c;
        $clave_liga_comp = trim($row_c['clave_liga'] ?? '');
    }

    // 1. Contar nadadoras únicas en esta competición (Figuras o Rutinas)
    $q1 = "SELECT COUNT(DISTINCT id_nadadora) as total FROM (
            SELECT id_nadadora FROM inscripciones_figuras WHERE id_competicion = '$id_competicion'
            UNION
            SELECT rp.id_nadadora FROM rutinas_participantes rp JOIN rutinas r ON rp.id_rutina = r.id WHERE r.id_competicion = '$id_competicion'
          ) as atletas";
    $res1 = mysqli_query($connection, $q1);
    $total_nadadoras = ($res1) ? (int)mysqli_fetch_assoc($res1)['total'] : 0;

    // 2. Contar total de rutinas
    $q2 = "SELECT COUNT(*) as total FROM rutinas WHERE id_competicion = '$id_competicion'";
    $res2 = mysqli_query($connection, $q2);
    $total_rutinas = ($res2) ? (int)mysqli_fetch_assoc($res2)['total'] : 0;

    // 3. Contar fases totales configuradas
    $q_total = "SELECT COUNT(*) as total FROM fases WHERE id_competicion = '$id_competicion'";
    $res_total = mysqli_query($connection, $q_total);
    $total_fases = ($res_total) ? (int)mysqli_fetch_assoc($res_total)['total'] : 0;

    // 4. Contar fases con al menos una puntuación registrada
    $q3 = "SELECT COUNT(DISTINCT id_fase) as total FROM puntuaciones_jueces pj 
           JOIN rutinas r ON pj.id_rutina = r.id 
           WHERE r.id_competicion = '$id_competicion'";
    $res3 = mysqli_query($connection, $q3);
    $fases_puntuadas = ($res3) ? (int)mysqli_fetch_assoc($res3)['total'] : 0;

    $porcentaje_fases = ($total_fases > 0) ? round(($fases_puntuadas / $total_fases) * 100) : 0;
}

// Catálogo de Documentos Publicables en la Web (docs/)
$cache_buster = time();
$docs_publicacion = [
    'inscripciones' => [
        'titulo' => 'Listado de Inscripciones',
        'subtitulo' => 'Listado con todas las inscripciones confirmadas de los clubes participantes.',
        'icono' => 'fa-file-pen',
        'color' => 'blue',
        'auto' => true,
        'preview_url' => "informes/informe_figuras.php?id_competicion={$id_competicion}&titulo=Inscripciones&v={$cache_buster}"
    ],
    'orden' => [
        'titulo' => 'Orden de Salida',
        'subtitulo' => 'Orden de competición con sorteo aplicado para figuras y rutinas.',
        'icono' => 'fa-list-ol',
        'color' => 'purple',
        'auto' => true,
        'preview_url' => "informes/informe_figuras.php?id_competicion={$id_competicion}&titulo=orden&v={$cache_buster}"
    ],
    'clasificacion' => [
        'titulo' => 'Clasificación Detallada',
        'subtitulo' => 'Acta de clasificación con notas definitivas de figuras y rutinas.',
        'icono' => 'fa-trophy',
        'color' => 'emerald',
        'auto' => true,
        'preview_url' => "informes/informe_puntuaciones_global.php?id_competicion={$id_competicion}&titulo=Clasificaci%C3%B3n%20Detallada&v={$cache_buster}"
    ],
    'normativa' => [
        'titulo' => 'Normativa de Competición',
        'subtitulo' => 'Convocatoria, horarios y reglamento técnico de la jornada.',
        'icono' => 'fa-file-shield',
        'color' => 'indigo',
        'auto' => false,
        'preview_url' => null
    ]
];

if (!empty($clave_liga_comp)) {
    $docs_publicacion['liga'] = [
        'titulo' => 'Clasificación de Liga',
        'subtitulo' => 'Ranking acumulado de la liga con las puntuaciones de la temporada.',
        'icono' => 'fa-ranking-star',
        'color' => 'amber',
        'auto' => true,
        'preview_url' => "informes/informe_figuras_liga_categorias.php?id_competicion={$id_competicion}&v={$cache_buster}"
    ];
}
?>

<main class="flex-1 flex flex-col min-w-0 bg-[#f8fafc]">
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-8 max-w-7xl mx-auto w-full font-lexend space-y-6 sm:space-y-8">
        
        <!-- HEADER DE SECCIÓN -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200/80">
                        <i class="fas fa-file-pdf text-[9px]"></i> Documentación
                    </span>
                    <span class="text-[11px] text-slate-400 font-semibold">• Secretarios y Administradores</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm shadow-sm">
                        <i class="fas fa-file-pdf"></i>
                    </span>
                    Informes y Documentación
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">
                    <?php if ($comp_data): ?>
                        Competición activa: <span class="font-bold text-slate-700"><?php echo htmlspecialchars($comp_data['nombre']); ?></span> (ID #<?php echo $id_competicion; ?>)
                    <?php else: ?>
                        Selecciona una competición para gestionar su documentación.
                    <?php endif; ?>
                </p>
            </div>

            <!-- BOTONES DE NAVEGACIÓN -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="inscripciones.php" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200/90 text-slate-700 text-xs font-bold shadow-sm hover:bg-slate-50 hover:border-slate-300 active:scale-[0.98] transition-all no-underline">
                    <i class="fas fa-users-line text-blue-600 text-xs"></i>
                    Ir a Inscripciones
                </a>
                <?php if ($comp_data): ?>
                    <a href="competiciones_edit.php?id=<?php echo $id_competicion; ?>" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200/90 text-slate-700 text-xs font-bold shadow-sm hover:bg-slate-50 hover:border-slate-300 active:scale-[0.98] transition-all no-underline">
                        <i class="fas fa-sliders text-slate-600 text-xs"></i>
                        Ajustes Competición
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- KPIS PRINCIPALES -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6">
            
            <!-- Atletas Registradas -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 border-l-4 sm:border-l-[6px] border-l-blue-600 card-hover">
                <div class="flex justify-between items-center mb-3">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm">
                        <i class="fas fa-users-line text-lg"></i>
                    </div>
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider bg-slate-50 px-2.5 py-1 rounded-full border border-slate-100">Censo Oficial</span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mb-0.5 leading-none tabular-nums"><?php echo $total_nadadoras; ?></h3>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-tight">Atletas Únicas Inscritas</p>
            </div>

            <!-- Rutinas en Sorteo -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 border-l-4 sm:border-l-[6px] border-l-purple-500 card-hover">
                <div class="flex justify-between items-center mb-3">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 shadow-sm">
                        <i class="fas fa-list-ol text-lg"></i>
                    </div>
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider bg-slate-50 px-2.5 py-1 rounded-full border border-slate-100">Sorteo</span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mb-0.5 leading-none tabular-nums"><?php echo $total_rutinas; ?></h3>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-tight">Rutinas Configuradas</p>
            </div>

            <!-- Fases Puntuadas -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 border-l-4 sm:border-l-[6px] border-l-emerald-500 card-hover">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm mb-3">
                            <i class="fas fa-medal text-lg"></i>
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mb-0.5 leading-none tabular-nums">
                            <?php echo $fases_puntuadas; ?> <span class="text-sm sm:text-base text-slate-300 font-bold italic tabular-nums">/ <?php echo $total_fases; ?></span>
                        </h3>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-tight">Fases Puntuadas</p>
                    </div>
                    
                    <div class="relative w-14 h-14 sm:w-16 sm:h-16 shrink-0">
                        <svg viewBox="0 0 80 80" class="w-full h-full transform -rotate-90">
                            <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="7" fill="transparent" class="text-slate-100" />
                            <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="7" fill="transparent" 
                                    stroke-dasharray="213.6" 
                                    stroke-dashoffset="<?php echo 213.6 - (213.6 * $porcentaje_fases / 100); ?>" 
                                    class="text-emerald-500 transition-all duration-1000 ease-out" 
                                    stroke-linecap="round" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center text-[10px] sm:text-[11px] font-black text-emerald-600 tabular-nums">
                            <?php echo $porcentaje_fases; ?>%
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- PANEL DE PUBLICACIÓN OFICIAL EN LA WEB Y CONTROL DE SUBIDAS (docs/)       -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl p-5 sm:p-7 border border-slate-200/90 shadow-sm space-y-5">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-sm">
                        <i class="fas fa-cloud-arrow-up text-base"></i>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight leading-tight">
                            Publicación de Documentos y Notificaciones
                        </h2>
                        <p class="text-xs text-slate-500">
                            Publicación en el Dashboard (<span class="font-bold text-slate-600">carpeta docs/</span>) y envío de avisos con PDF adjunto.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
                        <i class="fas fa-flask text-[9px] mr-1"></i> Modo Pruebas Activo
                    </span>
                </div>
            </div>

            <!-- LISTADO DE DOCUMENTOS PUBLICABLES -->
            <div class="space-y-3.5">
                <?php 
                foreach ($docs_publicacion as $key => $doc): 
                    $file_path = "./docs/{$id_competicion}-{$key}.pdf";
                    if ($key === 'clasificacion' && !file_exists($file_path) && file_exists("./docs/{$id_competicion}-resultados.pdf")) {
                        $file_path = "./docs/{$id_competicion}-resultados.pdf";
                    }
                    $is_published = file_exists($file_path);
                    $file_size = $is_published ? round(filesize($file_path) / 1024, 1) . ' KB' : '';
                    $file_mtime = $is_published ? date('d/m/Y H:i', filemtime($file_path)) : '';
                ?>
                    <div id="doc-card-<?php echo $key; ?>" class="p-4 sm:p-5 rounded-2xl border <?php echo $is_published ? 'border-emerald-200/90 bg-emerald-50/20' : 'border-slate-200/80 bg-slate-50/40'; ?> flex flex-col lg:flex-row lg:items-center justify-between gap-4 transition-all">
                        
                        <!-- INFO DEL DOCUMENTO -->
                        <div class="flex items-start gap-3.5 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-center text-slate-700 shrink-0 mt-0.5">
                                <i class="fas <?php echo $doc['icono']; ?> text-base text-<?php echo $doc['color']; ?>-600"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                    <h3 class="text-sm font-black text-slate-900 tracking-tight leading-tight">
                                        <?php echo htmlspecialchars($doc['titulo']); ?>
                                    </h3>
                                    <?php if ($is_published): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                            Publicado en la Web
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-500 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            No Publicado
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-slate-500 leading-relaxed mb-1">
                                    <?php echo htmlspecialchars($doc['subtitulo']); ?>
                                </p>
                                <div class="flex items-center gap-3 text-[11px] text-slate-400 font-medium">
                                    <?php if ($is_published): ?>
                                        <span class="text-emerald-700 font-semibold flex items-center gap-1">
                                            <i class="fas fa-check-circle text-[10px]"></i> Archivo: <code class="text-[10px] font-mono bg-white px-1.5 py-0.5 rounded border border-emerald-200"><?php echo "{$id_competicion}-{$key}.pdf"; ?></code>
                                        </span>
                                        <span>•</span>
                                        <span class="tabular-nums font-semibold text-slate-600"><?php echo $file_size; ?></span>
                                        <span>•</span>
                                        <span class="tabular-nums text-slate-500">Actualizado: <?php echo $file_mtime; ?></span>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic">Visible únicamente para secretarios en borrador. No aparece en el Dashboard de clubes.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- ACCIONES Y CONTROLES -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-200/60">
                            
                            <?php if ($is_published): ?>
                                <!-- BOTÓN VER ACTUAL -->
                                <a href="<?php echo $file_path; ?>?v=<?php echo time(); ?>" target="_blank" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-200/90 hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs active:scale-[0.98] transition-all no-underline" title="Ver documento publicado actualmente">
                                    <i class="fas fa-eye text-blue-600 text-[11px]"></i> Ver PDF
                                </a>

                                <!-- BOTÓN BORRAR SUBIDA / DESPUBLICAR -->
                                <button type="button" onclick="confirmarBorrado('<?php echo $key; ?>', '<?php echo htmlspecialchars($doc['titulo']); ?>')" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold active:scale-[0.98] transition-all" title="Eliminar archivo del servidor y retirar de la web">
                                    <i class="fas fa-trash-alt text-[11px]"></i> Borrar Subida
                                </button>

                                <?php if ($doc['auto']): ?>
                                    <!-- BOTÓN RE-PUBLICAR / ACTUALIZAR -->
                                    <button type="button" onclick="abrirModalPublicar('<?php echo $key; ?>', '<?php echo htmlspecialchars($doc['titulo']); ?>', true)" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm active:scale-[0.98] transition-all" title="Regenerar PDF y notificar">
                                        <i class="fas fa-arrows-rotate text-[11px]"></i> Actualizar
                                    </button>
                                <?php endif; ?>

                            <?php else: ?>
                                
                                <?php if ($doc['auto']): ?>
                                    <!-- PREVISUALIZAR BORRADOR -->
                                    <?php if (!empty($doc['preview_url'])): ?>
                                        <a href="<?php echo $doc['preview_url']; ?>" target="_blank" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-200/90 hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs active:scale-[0.98] transition-all no-underline" title="Previsualizar informe antes de publicarlo">
                                            <i class="fas fa-magnifying-glass text-slate-500 text-[11px]"></i> Previsualizar
                                        </a>
                                    <?php endif; ?>

                                    <!-- BOTÓN PUBLICAR EN LA WEB -->
                                    <button type="button" onclick="abrirModalPublicar('<?php echo $key; ?>', '<?php echo htmlspecialchars($doc['titulo']); ?>', false)" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black shadow-sm active:scale-[0.98] transition-all">
                                        <i class="fas fa-cloud-arrow-up text-xs"></i> Publicar en la Web
                                    </button>
                                <?php else: ?>
                                    <!-- SUBIDA MANUAL DE PDF (Normativa) -->
                                    <form action="informes_publicar_code.php" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                                        <input type="hidden" name="action" value="subir_manual">
                                        <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">
                                        <input type="hidden" name="tipo" value="<?php echo $key; ?>">
                                        
                                        <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-200/90 hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs active:scale-[0.98] transition-all">
                                            <i class="fas fa-paperclip text-slate-500 text-xs"></i> Seleccionar PDF
                                            <input type="file" name="archivo_pdf" accept=".pdf" class="hidden" onchange="this.form.submit()">
                                        </label>
                                    </form>
                                <?php endif; ?>

                            <?php endif; ?>

                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

            <?php
            // Escaneo de documentos adicionales/personalizados en docs/{id}-*.pdf
            $all_comp_files = glob("./docs/{$id_competicion}-*.pdf");
            $custom_files = [];
            $standard_keys = ['inscripciones', 'orden', 'clasificacion', 'resultados', 'normativa', 'liga', 'nadadoras', 'nota-media-liga'];
            
            if (!empty($all_comp_files)) {
                foreach ($all_comp_files as $cp_path) {
                    $cp_basename = basename($cp_path);
                    if (preg_match('/^' . $id_competicion . '-(.+)\.pdf$/i', $cp_basename, $m_custom)) {
                        $suf_clean = strtolower($m_custom[1]);
                        if (!in_array($suf_clean, $standard_keys)) {
                            $custom_files[] = [
                                'filename' => $cp_basename,
                                'path' => $cp_path,
                                'slug' => $suf_clean,
                                'label' => ucwords(str_replace(['_', '-'], ' ', $suf_clean)),
                                'size' => round(filesize($cp_path) / 1024, 1) . ' KB',
                                'mtime' => date('d/m/Y H:i', filemtime($cp_path))
                            ];
                        }
                    }
                }
            }
            ?>

            <!-- ========================================================================= -->
            <!-- SUBIDA Y GESTIÓN DE DOCUMENTOS PERSONALIZADOS / ADICIONALES               -->
            <!-- ========================================================================= -->
            <div class="pt-6 border-t border-slate-200/80 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm sm:text-base font-black text-slate-900 tracking-tight flex items-center gap-2">
                            <i class="fas fa-folder-plus text-indigo-600"></i>
                            Documentación Adicional y Archivos Personalizados
                        </h3>
                        <p class="text-xs text-slate-500">
                            Sube normativas específicas, pases de nivel, horarios o anexos con nomenclatura <code class="font-mono bg-slate-100 text-slate-700 px-1 py-0.5 rounded text-[11px]"><?php echo $id_competicion; ?>-nombre.pdf</code>.
                        </p>
                    </div>
                    
                    <button type="button" onclick="abrirModalCustomDoc()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs font-black shadow-sm active:scale-[0.98] transition-all shrink-0">
                        <i class="fas fa-cloud-arrow-up text-xs"></i>
                        Subir Documento
                    </button>
                </div>

                <!-- Listado de documentos personalizados -->
                <?php if (empty($custom_files)): ?>
                    <div id="emptyCustomDocsNotice" class="p-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 text-center">
                        <p class="text-xs text-slate-400 font-medium italic">
                            No hay documentos personalizados adicionales subidos para esta competición.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="listaCustomDocs">
                        <?php foreach ($custom_files as $cf): ?>
                            <div class="p-3.5 sm:p-4 rounded-2xl border border-indigo-100 bg-indigo-50/20 flex items-center justify-between gap-3 hover:border-indigo-200 transition-all" id="custom-doc-card-<?php echo htmlspecialchars($cf['filename']); ?>">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-white border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 shadow-2xs">
                                        <i class="fas fa-file-pdf text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-black text-slate-900 truncate"><?php echo htmlspecialchars($cf['label']); ?></h4>
                                        <div class="flex items-center gap-2 text-[10px] text-slate-400 font-medium">
                                            <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-indigo-100 text-slate-600"><?php echo htmlspecialchars($cf['filename']); ?></code>
                                            <span>•</span>
                                            <span class="tabular-nums"><?php echo $cf['size']; ?></span>
                                            <span>•</span>
                                            <span class="tabular-nums"><?php echo $cf['mtime']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <a href="docs/<?php echo htmlspecialchars($cf['filename']); ?>?v=<?php echo time(); ?>" target="_blank" class="w-8 h-8 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-blue-600 flex items-center justify-center text-xs shadow-2xs active:scale-95 transition-all" title="Ver documento">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <button type="button" onclick="confirmarBorradoCustom('<?php echo htmlspecialchars($cf['filename']); ?>', '<?php echo htmlspecialchars($cf['label']); ?>')" class="w-8 h-8 rounded-xl bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-600 flex items-center justify-center text-xs shadow-2xs active:scale-95 transition-all" title="Eliminar archivo">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- AVISO INFORMATIVO -->
            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 flex items-start gap-3">
                <i class="fas fa-circle-info text-blue-600 text-sm mt-0.5"></i>
                <div class="text-xs text-slate-600 leading-relaxed">
                    <strong>Comportamiento del sistema:</strong> Los clubes únicamente visualizan sus propias atletas en la pestaña de <em>Inscripciones</em> y nunca pueden publicar documentos en la web. La publicación oficial se gestiona exclusivamente desde este panel por secretarios y administradores. Cuando se publica un listado, queda disponible en el Dashboard y se remite el archivo oficial por email.
                </div>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- SECCIÓN INFERIOR: INFORMES Y HOJAS TÉCNICAS (DOCUMENTACIÓN OPERATIVA)     -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-8">
            
            <!-- COLUMNA IZQUIERDA: RUTINAS -->
            <div class="lg:col-span-7 space-y-4 sm:space-y-6">
                <div class="bg-white rounded-3xl p-5 sm:p-7 shadow-sm border border-slate-200/80 border-t-4 sm:border-t-[6px] border-t-blue-600">
                    <h2 class="text-base sm:text-lg font-black text-slate-900 mb-4 flex items-center gap-2.5">
                        <i class="fa-solid fa-water text-blue-600"></i> Documentación Operativa de Rutinas
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                        <a href="informes/inscripciones_numericas_rutinas.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Rutinas" target="_blank" class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 group hover:border-blue-200 hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-3 no-underline">
                            <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-blue-600 shadow-2xs transition-colors shrink-0">
                                <i class="fas fa-clipboard-list text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Inscripciones Numéricas</span>
                        </a>

                        <a href="informes/informe_preinscripciones.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Nadadoras%20inscritas" target="_blank" class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 group hover:border-blue-200 hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-3 no-underline">
                            <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-blue-600 shadow-2xs transition-colors shrink-0">
                                <i class="fas fa-person-swimming text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Censo Nadadoras Inscritas</span>
                        </a>

                        <a href="informes/inscripciones_numericas_rutinas.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Orden%20de%20salida" target="_blank" class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 group hover:border-blue-200 hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-3 no-underline">
                            <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-blue-600 shadow-2xs transition-colors shrink-0">
                                <i class="fas fa-sort-amount-down text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Orden de Salida Rutinas</span>
                        </a>

                        <a href="informes/informe_puntuaciones.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Clasificación%20por%20Clubes&memorial=si&id_fase=0" target="_blank" class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 group hover:border-blue-200 hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-3 no-underline">
                            <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-blue-600 shadow-2xs transition-colors shrink-0">
                                <i class="fas fa-trophy text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Clasificación por Clubes</span>
                        </a>

                        <a href="informes/informe_puntuaciones.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Clasificación%20detallada&hoja_tecnica=si&memorial=si" target="_blank" class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 group hover:border-blue-200 hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-3 sm:col-span-2 no-underline">
                            <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-blue-600 shadow-2xs transition-colors shrink-0">
                                <i class="fas fa-medal text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Resultados Detallados con Hoja Técnica</span>
                        </a>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-wider mb-3 px-1">Hojas de Puntuación PDF para Paneles</h4>
                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                            <a href="informes/hojas_puntuacion_rutinas_elementos.php?id_competicion=<?php echo $id_competicion; ?>&_v=<?php echo time(); ?>" target="_blank" class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl hover:bg-slate-50 active:scale-[0.98] border border-transparent hover:border-slate-100 transition-all text-center no-underline" title="Hojas de Puntuación para Panel de Elementos (A4 para guillotina)">
                                <i class="fas fa-shapes text-indigo-600 text-xl"></i>
                                <span class="text-[9px] font-black uppercase text-slate-600 truncate w-full">Elementos</span>
                            </a>
                            <a href="informes/hojas_puntuacion_rutinas_sincronizacion.php?id_competicion=<?php echo $id_competicion; ?>&_v=<?php echo time(); ?>" target="_blank" class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl hover:bg-slate-50 active:scale-[0.98] border border-transparent hover:border-slate-100 transition-all text-center no-underline" title="Hojas de Puntuación para Panel de Sincronización (A4 para guillotina)">
                                <i class="fas fa-water text-cyan-600 text-xl"></i>
                                <span class="text-[9px] font-black uppercase text-slate-600 truncate w-full">Sincro</span>
                            </a>
                            <a href="informes/hojas_puntuacion_rutinas_ia.php?id_competicion=<?php echo $id_competicion; ?>&_v=<?php echo time(); ?>" target="_blank" class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl hover:bg-slate-50 active:scale-[0.98] border border-transparent hover:border-slate-100 transition-all text-center no-underline" title="Hojas de Puntuación para Panel de Impresión Artística (A4 para guillotina)">
                                <i class="fas fa-palette text-purple-600 text-xl"></i>
                                <span class="text-[9px] font-black uppercase text-slate-600 truncate w-full">Artística</span>
                            </a>
                            <a href="informes/informe_puntuaciones.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Árbitros&hoja_tecnica=si&id_fase=0" target="_blank" class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl hover:bg-slate-50 active:scale-[0.98] border border-transparent hover:border-slate-100 transition-all text-center no-underline">
                                <i class="fas fa-gavel text-slate-400 text-xl"></i>
                                <span class="text-[9px] font-black uppercase text-slate-600 truncate w-full">Árbitros</span>
                            </a>
                            <a href="informes/informe_rutinas_notas_anotadores.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Anotadores&_v=<?php echo time(); ?>" target="_blank" class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl hover:bg-slate-50 active:scale-[0.98] border border-transparent hover:border-slate-100 transition-all text-center no-underline" title="Hojas para Anotadores de Rutinas">
                                <i class="fas fa-user-pen text-blue-500 text-xl"></i>
                                <span class="text-[9px] font-black uppercase text-slate-600 truncate w-full">Anot. Rutinas</span>
                            </a>
                            <a href="informes/hojas_puntuacion_rutinas_no_cc.php?id_competicion=<?php echo $id_competicion; ?>" target="_blank" class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl hover:bg-slate-50 active:scale-[0.98] border border-transparent hover:border-slate-100 transition-all text-center no-underline">
                                <i class="fas fa-file-pdf text-amber-600 text-xl"></i>
                                <span class="text-[9px] font-black uppercase text-slate-600 truncate w-full">No CC</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: FIGURAS -->
            <div class="lg:col-span-5 space-y-4 sm:space-y-6">
                <div class="bg-white rounded-3xl p-5 sm:p-7 shadow-sm border border-slate-200/80 border-t-4 sm:border-t-[6px] border-t-emerald-500 h-full">
                    <h2 class="text-base sm:text-lg font-black text-slate-900 mb-4 flex items-center gap-2.5">
                        <i class="fa-solid fa-shapes text-emerald-500"></i> Informes de Figuras y Liga
                    </h2>
                    
                    <div class="space-y-2.5">
                        <a href="informes/informe_figuras.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Inscripciones&v=<?php echo time(); ?>" target="_blank" class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 flex items-center justify-between group hover:border-emerald-200 hover:bg-slate-50 active:scale-[0.98] transition-all no-underline">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 transition-colors shadow-2xs shrink-0">
                                    <i class="fas fa-signature text-sm"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800 truncate">Inscripciones Figuras (Borrador)</span>
                            </div>
                            <i class="fas fa-arrow-up-right-from-square text-[10px] text-slate-400 group-hover:text-emerald-600 shrink-0"></i>
                        </a>

                        <a href="informes/informe_figuras.php?id_competicion=<?php echo $id_competicion; ?>&titulo=orden&v=<?php echo time(); ?>" target="_blank" class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 flex items-center justify-between group hover:border-emerald-200 hover:bg-slate-50 active:scale-[0.98] transition-all no-underline">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 transition-colors shadow-2xs shrink-0">
                                    <i class="fas fa-list-ol text-sm"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800 truncate">Orden de Actuación Figuras</span>
                            </div>
                            <i class="fas fa-arrow-up-right-from-square text-[10px] text-slate-400 group-hover:text-emerald-600 shrink-0"></i>
                        </a>

                        <a href="informes/informe_figuras_resultados_categorias.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Resultados por categorias&hoja_tecnica=si" target="_blank" class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 flex items-center justify-between group hover:border-emerald-200 hover:bg-slate-50 active:scale-[0.98] transition-all no-underline">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 transition-colors shadow-2xs shrink-0">
                                    <i class="fas fa-trophy text-sm"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800 truncate">Resultados Finales por Categorías</span>
                            </div>
                            <i class="fas fa-arrow-up-right-from-square text-[10px] text-slate-400 group-hover:text-emerald-600 shrink-0"></i>
                        </a>

                        <a href="informes/informe_figuras_notas_anotadores.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Anotadores&_v=<?php echo time(); ?>" target="_blank" class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 flex items-center justify-between group hover:border-emerald-200 hover:bg-slate-50 active:scale-[0.98] transition-all no-underline">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 transition-colors shadow-2xs shrink-0">
                                    <i class="fas fa-user-pen text-sm"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800 truncate">Hojas para Anotadores de Figuras</span>
                            </div>
                            <i class="fas fa-arrow-up-right-from-square text-[10px] text-slate-400 group-hover:text-emerald-600 shrink-0"></i>
                        </a>

                        <a href="informes/informe_figuras_notas_figuras_junior.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Notas%20Rutina%20Técnica" target="_blank" class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-100 flex items-center justify-between group hover:border-emerald-200 hover:bg-slate-50 active:scale-[0.98] transition-all no-underline">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 transition-colors shadow-2xs shrink-0">
                                    <i class="fas fa-file-waveform text-sm"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800 truncate">Notas de Rutina Técnica</span>
                            </div>
                            <i class="fas fa-arrow-up-right-from-square text-[10px] text-slate-400 group-hover:text-emerald-600 shrink-0"></i>
                        </a>

                        <?php if (($comp['niveles'] ?? '') == 'si' || stripos($comp['nombre'] ?? '', 'nivel') !== false || ($_SESSION['id_rol'] ?? 0) == 1): ?>
                        <a href="informes/informe_convalidaciones.php?comp_pase=<?php echo $id_competicion; ?>&censo=inscritas&comps_ref[]=81&comps_ref[]=82" target="_blank" class="p-3.5 rounded-2xl bg-rose-50/60 border border-rose-200 flex items-center justify-between group hover:border-rose-300 hover:bg-rose-50 active:scale-[0.98] transition-all no-underline">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-rose-500 group-hover:text-rose-600 transition-colors shadow-2xs shrink-0">
                                    <i class="fas fa-stamp text-sm"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800 truncate">Convalidaciones Oficiales Pase de Nivel</span>
                            </div>
                            <i class="fas fa-arrow-up-right-from-square text-[10px] text-slate-400 group-hover:text-rose-600 shrink-0"></i>
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($clave_liga_comp)): ?>
                            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-xs shrink-0">
                                        <i class="fas fa-ranking-star text-base"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-xs font-black text-slate-900 block truncate">Ranking Acumulado de Liga</span>
                                        <span class="text-[10px] text-amber-700 font-bold uppercase tracking-wider block truncate">Liga <?php echo htmlspecialchars($clave_liga_comp); ?></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" onclick="calcularLiga(<?php echo (int)$id_competicion; ?>)" id="btn_calcular_liga_informes" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-500 active:scale-95 text-white font-black uppercase text-[10px] tracking-wider rounded-xl shadow-sm transition-all flex items-center gap-1.5 cursor-pointer tap-press">
                                        <i class="fas fa-calculator text-xs"></i> Calcular Liga
                                    </button>
                                    <a href="informes/informe_figuras_liga_categorias.php?id_competicion=<?php echo $id_competicion; ?>&titulo=Clasificación General Liga" target="_blank" class="px-3.5 py-2 bg-white hover:bg-slate-50 active:scale-95 text-slate-700 border border-amber-300 font-black uppercase text-[10px] tracking-wider rounded-xl shadow-2xs transition-all flex items-center gap-1.5 no-underline">
                                        <i class="fas fa-file-pdf text-red-500"></i> Ver PDF
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>
</main>

<!-- ========================================================================= -->
<!-- MODAL: SUBIR DOCUMENTO PERSONALIZADO CON OPCIONES TELEGRAM / EMAIL        -->
<!-- ========================================================================= -->
<div id="modalSubirCustomDoc" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 hidden animate-fade-in" onclick="cerrarModalPorBackdrop(event)">
    <div class="bg-white w-full max-w-lg rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all max-h-[90vh] flex flex-col" onclick="event.stopPropagation()">
        <!-- Encabezado Modal (Fijo arriba) -->
        <div class="px-5 py-4 sm:px-6 sm:py-4.5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-slate-50 to-indigo-50/40 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-xs shrink-0">
                    <i class="fas fa-cloud-arrow-up text-sm"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm sm:text-base font-black text-slate-900 truncate">Subir Documentos Personalizados</h3>
                    <p class="text-[11px] text-slate-500 truncate">Competición #<?php echo $id_competicion; ?>: <?php echo htmlspecialchars($comp_data['nombre'] ?? ''); ?></p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalCustomDoc()" class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center text-sm transition-colors shrink-0 ml-2" title="Cerrar modal (Esc)">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Formulario con Contenido Desplazable y Footer Fijo -->
        <form id="formCustomDoc" onsubmit="enviarCustomDoc(event)" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            <input type="hidden" name="action" value="subir_custom">
            <input type="hidden" name="ajax" value="1">
            <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">

            <!-- Contenido desplazable (scroll interno suave) -->
            <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0 overscroll-contain">
                <!-- Título / Nombre del Documento -->
                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-600 tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Título o Prefijo del Documento</span>
                        <span class="text-[10px] text-slate-400 font-normal lowercase">(opcional al subir varios)</span>
                    </label>
                    <input type="text" name="nombre_doc" id="custom_nombre_doc" placeholder="Ej: Normativa Pases de Nivel (opcional si seleccionas varios)..." 
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all shadow-2xs"
                           oninput="actualizarPreviewSlug(this.value)">
                    <p class="text-[10px] text-slate-400 mt-1 italic">
                        Nombre final en servidor: <code class="font-mono text-indigo-600 font-semibold" id="previewFilename"><?php echo $id_competicion; ?>-documento.pdf</code>
                    </p>
                </div>

                <!-- Selector de Archivo PDF (Multiarchivo) -->
                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-600 tracking-wider mb-1.5 flex items-center justify-between">
                        <span>Archivos PDF <span class="text-rose-500">*</span></span>
                        <span class="text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded-full"><i class="fas fa-layer-group text-[9px] mr-1"></i>Multiarchivo</span>
                    </label>
                    <div class="relative border-2 border-dashed border-slate-200 hover:border-indigo-400 rounded-2xl p-4 text-center transition-colors bg-slate-50/50 cursor-pointer" onclick="document.getElementById('custom_archivo_pdf').click()">
                        <i class="fas fa-file-pdf text-2xl text-rose-500 mb-1.5 block"></i>
                        <span class="text-xs font-bold text-slate-700 block" id="customFileNameDisplay">Haz clic para seleccionar uno o varios archivos PDF</span>
                        <span class="text-[10px] text-slate-400">Puedes seleccionar varios PDF a la vez (máx. 50 MB en total)</span>
                        <input type="file" name="archivos_pdf[]" id="custom_archivo_pdf" accept=".pdf" multiple required class="hidden" onchange="actualizarFileDisplay(this)">
                    </div>
                    <!-- Lista de archivos seleccionados -->
                    <div id="customFilesListContainer" class="mt-2.5 hidden space-y-1.5 max-h-36 overflow-y-auto"></div>
                </div>

                <!-- Nota o Mensaje Adicional (Opcional) -->
                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-600 tracking-wider mb-1.5">
                        Nota o Mensaje Informativo (Opcional)
                    </label>
                    <textarea name="texto_adicional" rows="2" placeholder="Nota que se incluirá en el aviso de Telegram / Email..." class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-700 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all shadow-2xs resize-none"></textarea>
                </div>

                <!-- Canales de Notificación (Checkboxes) -->
                <div class="pt-2 border-t border-slate-100 space-y-2.5">
                    <span class="block text-[10px] font-black uppercase text-slate-400 tracking-wider">Canales de Notificación</span>
                    
                    <!-- Checkbox Telegram -->
                    <label class="flex items-start gap-3 p-2.5 rounded-xl border border-blue-100 bg-blue-50/40 hover:bg-blue-50/70 transition-colors cursor-pointer">
                        <input type="checkbox" name="notif_telegram" value="1" checked class="mt-0.5 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <div class="min-w-0 flex-1">
                            <span class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                                <i class="fab fa-telegram text-blue-500"></i> Notificar por Telegram
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-blue-100 text-blue-700">Recomendado</span>
                            </span>
                            <p class="text-[10px] text-slate-500 mt-0.5">Envía el aviso oficial con el archivo PDF adjunto a través del bot @sincrm_bot.</p>
                        </div>
                    </label>

                    <!-- Checkbox Email Superadmin (Prueba) -->
                    <label class="flex items-start gap-3 p-2.5 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-slate-100/60 transition-colors cursor-pointer">
                        <input type="checkbox" name="notif_email_prueba" value="1" checked class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                        <div class="min-w-0 flex-1">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="fas fa-envelope text-indigo-500"></i> Enviar copia de prueba por Email (Superadmin)
                            </span>
                            <p class="text-[10px] text-slate-500 mt-0.5">Envía el correo oficial con el PDF a <code>xascorro@gmail.com</code> para comprobar la recepción.</p>
                        </div>
                    </label>

                    <!-- Checkbox Email Clubes -->
                    <label class="flex items-start gap-3 p-2.5 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-slate-100/60 transition-colors cursor-pointer">
                        <input type="checkbox" name="notif_email_clubes" value="1" class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        <div class="min-w-0 flex-1">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="fas fa-users text-emerald-500"></i> Enviar por Email a los Clubes participantes
                            </span>
                            <p class="text-[10px] text-slate-500 mt-0.5">Difunde el documento a los emails de los clubes inscritos en esta competición.</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Botones (Footer Fijo siempre visible) -->
            <div class="px-5 py-3.5 sm:px-6 bg-slate-50/90 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0">
                <button type="button" onclick="cerrarModalCustomDoc()" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="btnSubmitCustomDoc" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 text-white text-xs font-black shadow-md hover:from-indigo-700 hover:to-blue-700 active:scale-[0.98] transition-all">
                    <i class="fas fa-cloud-arrow-up text-xs"></i>
                    Subir y Notificar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const idCompeticionActiva = <?php echo (int)$id_competicion; ?>;

// --- GESTIÓN DE MODAL DOCUMENTO PERSONALIZADO ---
function abrirModalCustomDoc() {
    const modal = document.getElementById('modalSubirCustomDoc');
    if (modal) modal.classList.remove('hidden');
}

function cerrarModalCustomDoc() {
    const modal = document.getElementById('modalSubirCustomDoc');
    if (modal) modal.classList.add('hidden');
    document.getElementById('formCustomDoc').reset();
    document.getElementById('customFileNameDisplay').textContent = 'Haz clic para seleccionar uno o varios archivos PDF';
    const listCont = document.getElementById('customFilesListContainer');
    if (listCont) {
        listCont.innerHTML = '';
        listCont.classList.add('hidden');
    }
    actualizarPreviewSlug('');
}

function cerrarModalPorBackdrop(e) {
    if (e.target && e.target.id === 'modalSubirCustomDoc') {
        cerrarModalCustomDoc();
    }
}

// Cerrar con la tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' || e.key === 'Esc') {
        const modal = document.getElementById('modalSubirCustomDoc');
        if (modal && !modal.classList.contains('hidden')) {
            cerrarModalCustomDoc();
        }
    }
});

function actualizarPreviewSlug(val) {
    const preview = document.getElementById('previewFilename');
    if (!preview) return;
    const inputFiles = document.getElementById('custom_archivo_pdf');
    if (inputFiles && inputFiles.files && inputFiles.files.length > 1) {
        let prefix = val && val.trim() ? val.toLowerCase().trim().replace(/[^a-z0-9_-]/g, '-').replace(/-+/g, '-').replace(/^[-_]+|[-_]+$/g, '') + '-' : '';
        preview.textContent = idCompeticionActiva + '-' + prefix + '[archivo].pdf (' + inputFiles.files.length + ' archivos seleccionados)';
        return;
    }
    if (!val || !val.trim()) {
        preview.textContent = idCompeticionActiva + '-documento.pdf';
        return;
    }
    let slug = val.toLowerCase().trim()
        .replace(/[^a-z0-9_-]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^[-_]+|[-_]+$/g, '');
    slug = slug.replace(new RegExp('^' + idCompeticionActiva + '[-_]*'), '');
    preview.textContent = idCompeticionActiva + '-' + (slug || 'documento') + '.pdf';
}

function actualizarFileDisplay(input) {
    const display = document.getElementById('customFileNameDisplay');
    const inputNombre = document.getElementById('custom_nombre_doc');
    const listCont = document.getElementById('customFilesListContainer');
    const preview = document.getElementById('previewFilename');

    if (!input.files || input.files.length === 0) {
        display.textContent = 'Haz clic para seleccionar uno o varios archivos PDF';
        if (listCont) {
            listCont.innerHTML = '';
            listCont.classList.add('hidden');
        }
        actualizarPreviewSlug('');
        return;
    }

    const files = Array.from(input.files);

    if (files.length === 1) {
        const file = files[0];
        display.innerHTML = `<span class="text-indigo-600 font-black">${file.name}</span> (${(file.size/1024).toFixed(1)} KB)`;
        if (listCont) {
            listCont.innerHTML = '';
            listCont.classList.add('hidden');
        }
        if (!inputNombre.value.trim()) {
            let rawName = file.name.replace(/\.[^/.]+$/, "");
            rawName = rawName.replace(new RegExp('^' + idCompeticionActiva + '[-_]*'), '');
            inputNombre.value = rawName;
            actualizarPreviewSlug(rawName);
        } else {
            actualizarPreviewSlug(inputNombre.value);
        }
    } else {
        const totalMb = (files.reduce((acc, f) => acc + f.size, 0) / (1024 * 1024)).toFixed(2);
        display.innerHTML = `<span class="text-indigo-600 font-black">${files.length} archivos seleccionados</span> (${totalMb} MB total)`;

        if (listCont) {
            listCont.classList.remove('hidden');
            listCont.innerHTML = files.map(f => {
                let cleanBase = f.name.replace(/\.[^/.]+$/, "").replace(new RegExp('^' + idCompeticionActiva + '[-_]*'), '');
                let slug = cleanBase.toLowerCase().trim().replace(/[^a-z0-9_-]/g, '-').replace(/-+/g, '-').replace(/^[-_]+|[-_]+$/g, '');
                let finalName = idCompeticionActiva + '-' + slug + '.pdf';
                return `
                    <div class="flex items-center justify-between px-2.5 py-1.5 bg-indigo-50/60 border border-indigo-100 rounded-lg text-[11px]">
                        <div class="flex items-center gap-2 truncate">
                            <i class="fas fa-file-pdf text-rose-500 text-xs"></i>
                            <span class="font-bold text-slate-700 truncate">${f.name}</span>
                            <span class="text-slate-400 font-mono text-[10px]">(${(f.size/1024).toFixed(1)} KB)</span>
                        </div>
                        <span class="font-mono text-indigo-700 font-semibold text-[10px] shrink-0 ml-2">➔ ${finalName}</span>
                    </div>
                `;
            }).join('');
        }

        if (preview) {
            let prefix = inputNombre.value.trim() ? inputNombre.value.toLowerCase().trim().replace(/[^a-z0-9_-]/g, '-').replace(/-+/g, '-').replace(/^[-_]+|[-_]+$/g, '') + '-' : '';
            preview.textContent = idCompeticionActiva + '-' + prefix + '[archivo].pdf (' + files.length + ' archivos)';
        }
    }
}

function enviarCustomDoc(e) {
    e.preventDefault();
    const form = document.getElementById('formCustomDoc');
    const btn = document.getElementById('btnSubmitCustomDoc');
    const fileInput = document.getElementById('custom_archivo_pdf');

    if (!fileInput.files || fileInput.files.length === 0) {
        Swal.fire({
            title: 'Archivo requerido',
            text: 'Por favor, selecciona al menos un archivo PDF para subir.',
            icon: 'warning',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin text-xs"></i> Subiendo y Notificando...';

    fetch('informes_publicar_code.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-cloud-arrow-up text-xs"></i> Subir y Notificar';
        
        if (data.success) {
            cerrarModalCustomDoc();
            let listaHtml = '';
            if (data.archivos && data.archivos.length > 0) {
                listaHtml = '<div class="space-y-1.5 max-h-48 overflow-y-auto text-left mt-3 p-2 bg-slate-50 rounded-xl border border-slate-200">' + 
                    data.archivos.map(a => `<div class="flex items-center justify-between text-xs font-mono text-slate-700 py-1 border-b border-slate-100 last:border-0"><span class="flex items-center gap-1.5 truncate"><i class="fas fa-file-pdf text-rose-500"></i> ${a.filename}</span><span class="text-slate-400 text-[10px] shrink-0 ml-2">${a.size}</span></div>`).join('') +
                    '</div>';
            }
            Swal.fire({
                title: (data.total && data.total > 1) ? '¡Documentos Subidos!' : '¡Documento Subido!',
                html: `<p class="text-xs text-slate-600 mb-2">${data.message}</p>${listaHtml}`,
                icon: 'success',
                confirmButtonColor: '#2563eb'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                title: 'Error en la subida',
                text: data.error || 'No se pudo procesar la subida del documento.',
                icon: 'error',
                confirmButtonColor: '#e11d48'
            });
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-cloud-arrow-up text-xs"></i> Subir y Notificar';
        Swal.fire({
            title: 'Error de Red',
            text: err.message || 'Error de conexión con el servidor.',
            icon: 'error',
            confirmButtonColor: '#e11d48'
        });
    });
}

function confirmarBorradoCustom(filename, label) {
    Swal.fire({
        title: '¿Eliminar ' + label + '?',
        html: `
            <p class="text-xs text-slate-600 mb-2">
                Se eliminará permanentemente el archivo <code>docs/${filename}</code>.
            </p>
            <p class="text-xs font-bold text-rose-600">
                Los clubes ya no podrán descargarlo.
            </p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-trash-alt mr-1.5"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const formData = new FormData();
            formData.append('action', 'borrar_custom');
            formData.append('ajax', '1');
            formData.append('id_competicion', idCompeticionActiva);
            formData.append('filename', filename);

            return fetch('informes_publicar_code.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Error al eliminar el archivo.');
                }
                return data;
            })
            .catch(err => {
                Swal.showValidationMessage(`Error: ${err.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Eliminado',
                text: 'El documento ha sido eliminado con éxito.',
                icon: 'success',
                confirmButtonColor: '#2563eb'
            }).then(() => {
                window.location.reload();
            });
        }
    });
}
</script>
<script src="js/publicar_modal.js?v=<?php echo time(); ?>"></script>
<script>
// --- FUNCIÓN PARA CONFIRMAR BORRADO / DESPUBLICACIÓN ---
function confirmarBorrado(tipo, tituloDoc) {
    Swal.fire({
        title: '¿Retirar ' + tituloDoc + '?',
        html: `
            <p class="text-xs text-slate-600 mb-2">
                El archivo físico se eliminará de la carpeta <code>docs/</code> del servidor.
            </p>
            <p class="text-xs font-bold text-rose-600">
                Los clubes ya no podrán descargarlo desde el Dashboard.
            </p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-trash-alt mr-1.5"></i> Sí, borrar subida',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const formData = new FormData();
            formData.append('action', 'borrar');
            formData.append('ajax', '1');
            formData.append('id_competicion', '<?php echo $id_competicion; ?>');
            formData.append('tipo', tipo);

            return fetch('informes_publicar_code.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Error al eliminar el archivo.');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`Error: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Subida Eliminada',
                text: 'El documento ha sido despublicado y eliminado del servidor.',
                icon: 'success',
                confirmButtonColor: '#2563eb'
            }).then(() => {
                window.location.reload();
            });
        }
    });
}

<style>
.card-hover {
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
}
.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -4px rgba(0, 0, 0, 0.06), 0 4px 8px -2px rgba(0, 0, 0, 0.03);
}
</style>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>
