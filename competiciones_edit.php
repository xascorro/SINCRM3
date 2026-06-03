<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

// Función para listar imágenes de la galería
function get_gallery_images($prefix) {
    $dir = 'images/';
    $images = [];
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if (strpos($file, $prefix) === 0 && (strpos($file, '.jpg') || strpos($file, '.png'))) {
                $images[] = $dir . $file;
            }
        }
    }
    return $images;
}
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-6xl mx-auto w-full">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 font-lexend text-primary">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-calendar-check text-lg"></i></span>
                    Configuración de Evento
                </h1>
                <p class="text-slate-500 font-medium">Parámetros técnicos y personalización estética.</p>
            </div>
            <a href="competiciones.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm italic"><i class="fas fa-arrow-left text-xs"></i> Volver</a>
        </div>

        <?php
        if(isset($_POST['edit_btn'])):
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            $query = "SELECT * FROM competiciones WHERE id = '$id'";
            $query_run = mysqli_query($connection, $query);
            foreach ($query_run as $row):
        ?>
            <form action="competiciones_code.php" method="POST" enctype="multipart/form-data" class="animate-fade-in space-y-10 font-lexend">
                <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">

                <!-- BLOQUE 1: IDENTIDAD Y SEDE -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[6px] border-t-blue-600 relative overflow-hidden">
                    <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-info-circle text-blue-600"></i> Datos del Evento</h2>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                        <div class="md:col-span-8 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre del Campeonato</label>
                            <input type="text" name="edit_nombre" value="<?php echo $row['nombre']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 shadow-inner">
                        </div>
                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Fecha</label>
                            <input type="date" name="edit_fecha" value="<?php echo $row['fecha']?>" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700">
                        </div>
                        <div class="md:col-span-6 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Sede / Ciudad</label>
                            <input type="text" name="edit_lugar" value="<?php echo $row['lugar']?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700">
                        </div>
                        <div class="md:col-span-6 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Piscina / Instalación</label>
                            <input type="text" name="edit_piscina" value="<?php echo $row['piscina']?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700">
                        </div>
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Hora Inicio</label>
                            <input type="text" name="edit_hora_inicio" value="<?php echo $row['hora_inicio']?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700" placeholder="HH:MM">
                        </div>
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Hora Fin</label>
                            <input type="text" name="edit_hora_fin" value="<?php echo $row['hora_fin']?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700" placeholder="HH:MM">
                        </div>
                        <div class="md:col-span-6 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Temporada</label>
                            <input type="text" name="edit_temporada" value="<?php echo $row['temporada']?>" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700" placeholder="Ej: 2024/2025">
                        </div>
                    </div>
                </div>

                <!-- BLOQUE 2: CONFIGURACIÓN TÉCNICA -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-purple-600">
                        <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-sliders text-purple-600"></i> Reglas y Liga</h2>
                        <div class="space-y-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1">Clave de la Liga</label>
                                    <input type="text" name="edit_clave_liga" value="<?php echo $row['clave_liga']?>" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nombre Corto</label>
                                    <input type="text" name="edit_nombre_corto" value="<?php echo $row['nombre_corto']?>" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold" placeholder="Siglas">
                                </div>
                            </div>
                            
                            <!-- Selectores Excluyentes: Figuras vs Rutinas -->
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <span class="text-sm font-bold text-slate-700 italic">Competición de Figuras / Niveles</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="check_figuras" name="edit_figuras" value="si" <?php echo ($row['figuras'] == 'si') ? 'checked' : ''; ?> onchange="toggleExcluyente('figuras')" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <span class="text-sm font-bold text-slate-700 italic">Competición de Rutinas</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="check_rutinas" name="edit_rutinas" value="si" <?php echo ($row['figuras'] == 'no') ? 'checked' : ''; ?> onchange="toggleExcluyente('rutinas')" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                                </label>
                            </div>
                            <!-- Selector Independiente: Escolar -->
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <span class="text-sm font-bold text-slate-700 italic">No Federada / Escolar</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="edit_no_federado" value="si" <?php echo ($row['no_federado'] == 'si') ? 'checked' : ''; ?> class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500">
                        <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-location-dot text-emerald-600"></i> Localización y Licencias</h2>
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1">Enlace Google Maps</label>
                                <textarea name="edit_maps" rows="2" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs font-bold focus:border-emerald-500 transition-all"><?php echo $row['maps']?></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1">Color Corporativo</label>
                                    <div class="flex gap-2">
                                        <input type="text" name="edit_color" id="colorInput" value="<?php echo $row['color']?>" class="flex-1 px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-100 font-bold text-xs">
                                        <input type="color" oninput="document.getElementById('colorInput').value = this.value" value="<?php echo $row['color'] ?: '#3b82f6'; ?>" class="w-10 h-10 rounded-xl border-0 p-0 overflow-hidden cursor-pointer">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1">Mascara Licencia</label>
                                    <input type="number" name="edit_mascara_licencia" value="<?php echo $row['mascara_licencia']?>" class="w-full px-5 py-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs font-bold">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BLOQUE PLAZOS: GESTIÓN DE TIEMPOS -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[6px] border-t-amber-500">
                    <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-clock text-amber-500"></i> Plazos y Fechas Límite (Días de antelación)</h2>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase text-slate-400 px-1">Inicio Inscrip.</label>
                            <input type="number" name="edit_dias_inicio_inscripcion" value="<?php echo $row['dias_inicio_inscripcion']?>" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold text-amber-600">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase text-slate-400 px-1">Cierre Inscrip.</label>
                            <input type="number" name="edit_dias_fin_inscripcion" value="<?php echo $row['dias_fin_inscripcion']?>" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold text-red-600">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase text-slate-400 px-1">Sorteo</label>
                            <input type="number" name="edit_dias_sorteo" value="<?php echo $row['dias_sorteo']?>" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold text-emerald-600">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase text-slate-400 px-1">Coach Card</label>
                            <input type="number" name="edit_dias_coach_card" value="<?php echo $row['dias_coach_card']?>" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold text-purple-600">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase text-slate-400 px-1">Música</label>
                            <input type="number" name="edit_dias_musica" value="<?php echo $row['dias_musica']?>" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold text-blue-600">
                        </div>
                    </div>
                    <div class="mt-8 pt-6 border-t border-slate-50">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Enlace a la Reunión del Sorteo (Video Llamada)</label>
                        <input type="text" name="edit_enlace_sorteo" value="<?php echo $row['enlace_sorteo']?>" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-sm font-bold mt-2" placeholder="Enlace de Google Meet, Zoom, Teams...">
                    </div>
                </div>

                <!-- BLOQUE 3: GALERÍA E IMÁGENES -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[6px] border-t-emerald-500">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10 border-b border-slate-50 pb-6">
                        <h2 class="text-xl font-black text-slate-800 flex items-center gap-3"><i class="fas fa-images text-emerald-600"></i> Galería de Informes (Cabecera y Pie)</h2>
                        <div class="flex gap-4">
                            <div class="relative">
                                <input type="file" name="new_header" id="upload_header" class="hidden" onchange="updateFileName('header')">
                                <label for="upload_header" class="px-4 py-2 bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-500 rounded-xl transition-all text-[10px] font-black uppercase tracking-widest cursor-pointer flex items-center gap-2 shadow-sm border border-slate-200">
                                    <i class="fas fa-upload"></i> Subir Cabecera
                                </label>
                                <span id="name_header" class="absolute -bottom-5 right-0 text-[8px] font-bold text-blue-500 uppercase italic"></span>
                            </div>
                            <div class="relative">
                                <input type="file" name="new_footer" id="upload_footer" class="hidden" onchange="updateFileName('footer')">
                                <label for="upload_footer" class="px-4 py-2 bg-slate-100 hover:bg-purple-600 hover:text-white text-slate-500 rounded-xl transition-all text-[10px] font-black uppercase tracking-widest cursor-pointer flex items-center gap-2 shadow-sm border border-slate-200">
                                    <i class="fas fa-upload"></i> Subir Pie
                                </label>
                                <span id="name_footer" class="absolute -bottom-5 right-0 text-[8px] font-bold text-purple-500 uppercase italic"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                        <!-- Cabecera -->
                        <div class="space-y-4">
                            <div class="flex justify-between items-baseline">
                                <label class="text-xs font-black uppercase text-slate-400 tracking-widest px-1">Cabecera PDF</label>
                                <span id="current_header_name" class="text-[9px] font-black text-blue-500 italic uppercase"><?php echo basename($row['header_informe']); ?></span>
                            </div>
                            <div class="p-6 bg-slate-50 rounded-3xl border border-slate-200 shadow-inner">
                                <div class="w-full h-28 flex items-center justify-center bg-white rounded-xl mb-6 shadow-sm border border-slate-100 overflow-hidden">
                                    <img id="preview_header" src="<?php echo $row['header_informe'] ?: 'img/undraw_posting_photo.svg'; ?>" class="max-w-full max-h-full object-contain">
                                </div>
                                <input type="hidden" name="edit_header_informe" id="header_path" value="<?php echo $row['header_informe']; ?>">
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-64 overflow-y-auto custom-scrollbar p-2">
                                    <?php foreach(get_gallery_images('header') as $img): ?>
                                        <button type="button" onclick="selectImg('header', '<?php echo $img; ?>')" class="relative flex flex-col items-center gap-2 p-2 bg-white rounded-xl border-2 transition-all <?php echo $img == $row['header_informe'] ? 'border-blue-500 ring-2 ring-blue-50' : 'border-slate-100 hover:border-slate-300'; ?>">
                                            <div class="w-full h-10 flex items-center justify-center">
                                                <img src="<?php echo $img; ?>" class="max-w-full max-h-full object-contain">
                                            </div>
                                            <span class="text-[8px] font-black text-slate-400 truncate w-full uppercase"><?php echo basename($img); ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <!-- Pie -->
                        <div class="space-y-4">
                            <div class="flex justify-between items-baseline">
                                <label class="text-xs font-black uppercase text-slate-400 tracking-widest px-1">Pie PDF</label>
                                <span id="current_footer_name" class="text-[9px] font-black text-purple-500 italic uppercase"><?php echo basename($row['footer_informe']); ?></span>
                            </div>
                            <div class="p-6 bg-slate-50 rounded-3xl border border-slate-200 shadow-inner">
                                <div class="w-full h-28 flex items-center justify-center bg-white rounded-xl mb-6 shadow-sm border border-slate-100 overflow-hidden">
                                    <img id="preview_footer" src="<?php echo $row['footer_informe'] ?: 'img/undraw_posting_photo.svg'; ?>" class="max-w-full max-h-full object-contain">
                                </div>
                                <input type="hidden" name="edit_footer_informe" id="footer_path" value="<?php echo $row['footer_informe']; ?>">
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-64 overflow-y-auto custom-scrollbar p-2">
                                    <?php foreach(get_gallery_images('footer') as $img): ?>
                                        <button type="button" onclick="selectImg('footer', '<?php echo $img; ?>')" class="relative flex flex-col items-center gap-2 p-2 bg-white rounded-xl border-2 transition-all <?php echo $img == $row['footer_informe'] ? 'border-blue-500 ring-2 ring-blue-50' : 'border-slate-100 hover:border-slate-300'; ?>">
                                            <div class="w-full h-10 flex items-center justify-center">
                                                <img src="<?php echo $img; ?>" class="max-w-full max-h-full object-contain">
                                            </div>
                                            <span class="text-[8px] font-black text-slate-400 truncate w-full uppercase"><?php echo basename($img); ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BLOQUE NUEVO: DOCUMENTACIÓN -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[6px] border-t-emerald-500">
                    <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-file-pdf text-emerald-500"></i> Documentación Oficial (PDF)</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php
                        $docs_fields = [
                            'normativa' => ['label' => 'Normativa', 'icon' => 'fa-file-shield'],
                            'nadadoras' => ['label' => 'Nadadoras', 'icon' => 'fa-users-viewfinder'],
                            'inscripciones' => ['label' => 'Inscripciones', 'icon' => 'fa-file-pen'],
                            'orden' => ['label' => 'Orden Salida', 'icon' => 'fa-list-ol'],
                            'resultados' => ['label' => 'Resultados', 'icon' => 'fa-trophy'],
                            'liga' => ['label' => 'Ranking Liga', 'icon' => 'fa-ranking-star']
                        ];
                        foreach($docs_fields as $key => $meta):
                            $file_path = './docs/'.$row['id'].'-'.$key.'.pdf';
                            $exists = file_exists($file_path);
                        ?>
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-4">
                                <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest flex items-center gap-2"><i class="fas <?php echo $meta['icon']; ?>"></i> <?php echo $meta['label']; ?></label>
                                <?php if($exists): ?>
                                    <a href="<?php echo $file_path; ?>" target="_blank" class="text-[9px] font-bold px-2 py-1 bg-emerald-100 text-emerald-700 rounded-md">VER ACTUAL</a>
                                <?php else: ?>
                                    <span class="text-[9px] font-bold px-2 py-1 bg-slate-200 text-slate-400 rounded-md">NO SUBIDO</span>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="doc_<?php echo $key; ?>" accept=".pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- BLOQUE 4: MENSAJE -->
                <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200">
                    <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3"><i class="fas fa-comment-dots text-slate-400"></i> Comunicación</h2>
                    <textarea name="edit_mensaje" rows="4" class="w-full px-6 py-5 rounded-3xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-medium text-slate-600" placeholder="Notas para los clubes..."><?php echo $row['mensaje'];?></textarea>
                </div>

                <!-- BOTONES -->
                <div class="pt-10 flex justify-end gap-4">
                    <a href="competiciones.php" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Cancelar</a>
                    <button type="submit" name="update_btn" class="px-12 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> Guardar Cambios
                    </button>
                </div>

            </form>
        <?php 
            endforeach; 
        endif;
        ?>
    </div>
</main>

<script>
function toggleExcluyente(tipo) {
    const figuras = document.getElementById('check_figuras');
    const rutinas = document.getElementById('check_rutinas');
    
    if (tipo === 'figuras' && figuras.checked) {
        rutinas.checked = false;
    } else if (tipo === 'rutinas' && rutinas.checked) {
        figuras.checked = false;
    }
}

function selectImg(type, path) {
    document.getElementById(type + '_path').value = path;
    document.getElementById('preview_' + type).src = path;
    
    // Actualizar nombre mostrado
    const fileName = path.split('/').pop();
    document.getElementById('current_' + type + '_name').textContent = fileName;
    
    // Actualizar estilo visual de los botones
    const container = document.getElementById(type + '_path').parentElement;
    const buttons = container.querySelectorAll('button');
    buttons.forEach(b => b.classList.remove('border-blue-500', 'ring-2', 'ring-blue-50'));
    
    // Encontrar el botón clickeado (el event.currentTarget a veces falla si se dispara desde el hijo)
    event.currentTarget.classList.add('border-blue-500', 'ring-2', 'ring-blue-50');
}

function updateFileName(type) {
    const input = document.getElementById('upload_' + type);
    const label = document.getElementById('name_' + type);
    if (input.files && input.files[0]) {
        label.textContent = "Listo para subir: " + input.files[0].name;
    }
}
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
