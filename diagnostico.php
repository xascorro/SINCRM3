<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-stethoscope text-lg"></i></span>
                    Diagnóstico de Integridad
                </h1>
                <p class="text-slate-500 font-medium italic">Análisis exhaustivo para mantener la salud técnica de la base de datos.</p>
            </div>
            <div class="flex gap-3">
                <a href="mantenimiento.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm italic"><i class="fas fa-arrow-left text-xs"></i> Volver</a>
            </div>
        </div>

        <div class="space-y-12">
            
            <!-- BLOQUE 1: DEPORTISTAS -->
            <div>
                <div class="flex items-center gap-4 border-l-[8px] border-blue-500 pl-6 py-2 mb-8 uppercase tracking-widest text-blue-600 font-black italic">
                    Ámbito Deportistas
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nadadoras Duplicadas -->
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-blue-500 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-6 shadow-sm border border-blue-100"><i class="fas fa-copy text-lg"></i></div>
                            <h3 class="text-xl font-black text-slate-800 mb-2">Posibles Duplicados</h3>
                            <p class="text-xs font-medium text-slate-400 italic mb-8">Detección por coincidencia exacta de Nombre y Apellidos o Número de Licencia.</p>
                        </div>
                        <?php
                        $q_dup = "SELECT COUNT(*) as total FROM (SELECT COUNT(*) FROM nadadoras GROUP BY nombre, apellidos HAVING COUNT(*) > 1) as t";
                        $dups = mysqli_fetch_assoc(mysqli_query($connection, $q_dup))['total'];
                        ?>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-black <?php echo $dups > 0 ? 'text-red-500' : 'text-slate-400'; ?>"><?php echo $dups; ?> <span class="text-[10px] uppercase">coincidencias</span></span>
                            <button onclick="analizar('deportistas_duplicados')" class="px-6 py-2.5 bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-blue-500/20 hover:scale-105 transition-all">Analizar</button>
                        </div>
                    </div>

                    <!-- Nadadoras sin Actividad -->
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-blue-400 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mb-6 shadow-sm border border-slate-100"><i class="fas fa-user-slash text-lg"></i></div>
                            <h3 class="text-xl font-black text-slate-800 mb-2">Sin Inscripciones</h3>
                            <p class="text-xs font-medium text-slate-400 italic mb-8">Nadadoras registradas que nunca han participado en fases de figuras ni en rutinas.</p>
                        </div>
                        <?php
                        $q_no_act = "SELECT COUNT(*) as total FROM nadadoras WHERE id NOT IN (SELECT id_nadadora FROM inscripciones_figuras) AND id NOT IN (SELECT id_nadadora FROM rutinas_participantes)";
                        $no_act = mysqli_fetch_assoc(mysqli_query($connection, $q_no_act))['total'];
                        ?>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-black text-slate-400"><?php echo $no_act; ?> <span class="text-[10px] uppercase">inactivas</span></span>
                            <button onclick="analizar('deportistas_sin_inscripciones')" class="px-6 py-2.5 bg-slate-800 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg hover:scale-105 transition-all">Ver Listado</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOQUE 2: JUECES -->
            <div>
                <div class="flex items-center gap-4 border-l-[8px] border-amber-500 pl-6 py-2 mb-8 uppercase tracking-widest text-amber-600 font-black italic">
                    Ámbito Arbitral
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Jueces Duplicados -->
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-amber-500 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-6 shadow-sm border border-amber-100"><i class="fas fa-users-viewfinder text-lg"></i></div>
                            <h3 class="text-xl font-black text-slate-800 mb-2">Jueces Duplicados</h3>
                            <p class="text-xs font-medium text-slate-400 italic mb-8">Detección de fichas de jueces repetidas por nombre o licencia en el censo oficial.</p>
                        </div>
                        <?php
                        $q_j_dup = "SELECT COUNT(*) as total FROM (SELECT COUNT(*) FROM jueces GROUP BY nombre, apellidos HAVING COUNT(*) > 1) as t";
                        $j_dups = mysqli_fetch_assoc(mysqli_query($connection, $q_j_dup))['total'];
                        ?>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-black <?php echo $j_dups > 0 ? 'text-amber-600' : 'text-slate-400'; ?>"><?php echo $j_dups; ?> <span class="text-[10px] uppercase">posibles</span></span>
                            <button onclick="analizar('jueces_duplicados')" class="px-6 py-2.5 bg-amber-500 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-amber-500/20 hover:scale-105 transition-all">Analizar</button>
                        </div>
                    </div>

                    <!-- Jueces sin Cuenta de Acceso -->
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-amber-400 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mb-6 shadow-sm border border-slate-100"><i class="fas fa-link-slash text-lg"></i></div>
                            <h3 class="text-xl font-black text-slate-800 mb-2">Sin Vinculación Web</h3>
                            <p class="text-xs font-medium text-slate-400 italic mb-8">Jueces oficiales que aún no tienen una cuenta de usuario vinculada para acceder a sus datos.</p>
                        </div>
                        <?php
                        $q_j_no_link = "SELECT COUNT(*) as total FROM jueces WHERE id NOT IN (SELECT id_juez_v3 FROM usuarios WHERE id_juez_v3 IS NOT NULL) AND activo = 1";
                        $j_no_link = mysqli_fetch_assoc(mysqli_query($connection, $q_j_no_link))['total'];
                        ?>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-black text-slate-400"><?php echo $j_no_link; ?> <span class="text-[10px] uppercase">desvinculados</span></span>
                            <button onclick="analizar('jueces_sin_cuenta')" class="px-6 py-2.5 bg-slate-800 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg hover:scale-105 transition-all">Ver Listado</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOQUE 3: OPERATIVA (COMPETICIONES Y FASES) -->
            <div>
                <div class="flex items-center gap-4 border-l-[8px] border-emerald-500 pl-6 py-2 mb-8 uppercase tracking-widest text-emerald-600 font-black italic">
                    Competición & Operativa
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Fases sin Nadadoras -->
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-6 shadow-sm border border-emerald-100"><i class="fas fa-ghost text-lg"></i></div>
                            <h3 class="text-xl font-black text-slate-800 mb-2">Fases Vacías</h3>
                            <p class="text-xs font-medium text-slate-400 italic mb-8">Fases activas que no tienen ninguna inscripción de deportistas registrada.</p>
                        </div>
                        <?php
                        $q_f_vacia = "SELECT COUNT(*) as total FROM fases WHERE id NOT IN (SELECT id_fase FROM inscripciones_figuras) AND id_competicion IN (SELECT id FROM competiciones WHERE activo = 'si')";
                        $f_vacias = mysqli_fetch_assoc(mysqli_query($connection, $q_f_vacia))['total'];
                        ?>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-black <?php echo $f_vacias > 0 ? 'text-amber-500' : 'text-slate-400'; ?>"><?php echo $f_vacias; ?> <span class="text-[10px] uppercase">fases</span></span>
                            <button onclick="analizar('fases_vacias')" class="px-6 py-2.5 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-emerald-500/20 hover:scale-105 transition-all">Detectar</button>
                        </div>
                    </div>

                    <!-- Competiciones sin Fases -->
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-400 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mb-6 shadow-sm border border-slate-100"><i class="fas fa-calendar-xmark text-lg"></i></div>
                            <h3 class="text-xl font-black text-slate-800 mb-2">Eventos Huérfanos</h3>
                            <p class="text-xs font-medium text-slate-400 italic mb-8">Competiciones dadas de alta que no tienen fases técnicas asociadas.</p>
                        </div>
                        <?php
                        $q_c_huerf = "SELECT COUNT(*) as total FROM competiciones WHERE id NOT IN (SELECT id_competicion FROM fases)";
                        $c_huerf = mysqli_fetch_assoc(mysqli_query($connection, $q_c_huerf))['total'];
                        ?>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-black text-slate-400"><?php echo $c_huerf; ?> <span class="text-[10px] uppercase">eventos</span></span>
                            <button onclick="analizar('competiciones_sin_fases')" class="px-6 py-2.5 bg-slate-800 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg hover:scale-105 transition-all">Verificar</button>
                        </div>
                    </div>

                    <!-- Notas Huérfanas -->
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-red-500 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center mb-6 shadow-sm border border-red-100"><i class="fas fa-triangle-exclamation text-lg"></i></div>
                            <h3 class="text-xl font-black text-slate-800 mb-2">Integridad de Notas</h3>
                            <p class="text-xs font-medium text-slate-400 italic mb-8">Notas que han perdido su panel de jueces. Acción crítica requerida si hay detecciones.</p>
                        </div>
                        <?php
                        $q_n_huerf = "SELECT COUNT(*) as total FROM puntuaciones_jueces WHERE id_panel_juez NOT IN (SELECT id FROM panel_jueces)";
                        $n_huerf = mysqli_fetch_assoc(mysqli_query($connection, $q_n_huerf))['total'];
                        ?>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-black <?php echo $n_huerf > 0 ? 'text-red-600 animate-pulse' : 'text-slate-400'; ?>"><?php echo $n_huerf; ?> <span class="text-[10px] uppercase">críticos</span></span>
                            <button onclick="analizar('notas_huerfanas')" class="px-6 py-2.5 bg-red-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-red-500/20 hover:scale-105 transition-all">Reparar</button>
                        </div>
                    </div>

                    <!-- Limpieza en Cascada de Competiciones -->
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-slate-800 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-800 flex items-center justify-center mb-6 shadow-sm border border-slate-200"><i class="fas fa-broom text-lg"></i></div>
                            <h3 class="text-xl font-black text-slate-800 mb-2">Limpieza en Cascada</h3>
                            <p class="text-xs font-medium text-slate-400 italic mb-8">Eliminación completa de eventos (Figuras/Rutinas) y todas sus dependencias técnicas.</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase text-slate-400">Acción Destructiva</span>
                            <button onclick="abrirSelectorCascada()" class="px-6 py-2.5 bg-slate-800 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg hover:scale-105 transition-all">Iniciar Limpieza</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOQUE 4: MULTIMEDIA -->
            <div>
                <div class="flex items-center gap-4 border-l-[8px] border-indigo-500 pl-6 py-2 mb-8 uppercase tracking-widest text-indigo-600 font-black italic">
                    Ámbito Multimedia
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Archivos MP3 Huérfanos -->
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-indigo-500 flex flex-col justify-between group hover:shadow-xl transition-all">
                        <div>
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-6 shadow-sm border border-indigo-100"><i class="fas fa-file-audio text-lg"></i></div>
                            <h3 class="text-xl font-black text-slate-800 mb-2">Música Huérfana</h3>
                            <p class="text-xs font-medium text-slate-400 italic mb-8">Archivos MP3 en el servidor que no están vinculados a ninguna rutina en la base de datos.</p>
                        </div>
                        <?php
                        $huerfanos_mp3 = 0;
                        $valid_rutinas = [];
                        $q_all_rutinas = "SELECT id, id_competicion FROM rutinas";
                        $res_all = mysqli_query($connection, $q_all_rutinas);
                        if ($res_all) {
                            while($row_all = mysqli_fetch_assoc($res_all)) {
                                $valid_rutinas[$row_all['id_competicion']][$row_all['id']] = true;
                            }
                        }

                        $path_base = './public/music/';
                        if (is_dir($path_base)) {
                            $items = scandir($path_base);
                            foreach ($items as $item) {
                                if ($item === '.' || $item === '..') continue;
                                $comp_path = $path_base . $item;
                                if (is_dir($comp_path)) {
                                    $id_comp = $item;
                                    $files = scandir($comp_path);
                                    foreach ($files as $file) {
                                        if (pathinfo($file, PATHINFO_EXTENSION) === 'mp3') {
                                            $id_rutina = pathinfo($file, PATHINFO_FILENAME);
                                            if (!isset($valid_rutinas[$id_comp][$id_rutina])) {
                                                $huerfanos_mp3++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-black <?php echo $huerfanos_mp3 > 0 ? 'text-amber-500' : 'text-slate-400'; ?>"><?php echo $huerfanos_mp3; ?> <span class="text-[10px] uppercase">archivos</span></span>
                            <button onclick="analizar('archivos_musica_huerfanos')" class="px-6 py-2.5 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-500/20 hover:scale-105 transition-all">Analizar</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</main>

<script>
    function abrirSelectorCascada() {
        const formData = new FormData();
        formData.append('action', 'get_competiciones_list');

        fetch('mantenimiento_code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                let html = '<div class="text-left font-lexend"><p class="text-xs font-bold text-slate-500 mb-4 italic">Selecciona las competiciones que deseas borrar POR COMPLETO (fases, inscripciones, notas, etc.):</p>';
                html += '<div class="max-h-64 overflow-y-auto custom-scrollbar space-y-2 pr-2">';
                data.competiciones.forEach(c => {
                    html += `<label class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 hover:bg-slate-100 transition-all cursor-pointer">
                                <input type="checkbox" name="comp_ids[]" value="${c.id}" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-black text-slate-700 truncate">${c.nombre}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase italic">ID: #${c.id} | Fecha: ${c.fecha}</p>
                                </div>
                             </label>`;
                });
                html += '</div></div>';

                Swal.fire({
                    title: 'Seleccionar Competiciones',
                    html: html,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Simular Borrado',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#1e293b',
                    preConfirm: () => {
                        const selected = Array.from(document.querySelectorAll('input[name="comp_ids[]"]:checked')).map(cb => cb.value);
                        if (selected.length === 0) {
                            Swal.showValidationMessage('Debes seleccionar al menos una competición');
                            return false;
                        }
                        return selected;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        simularBorrado(result.value);
                    }
                });
            }
        });
    }

    function simularBorrado(ids) {
        const formData = new FormData();
        formData.append('action', 'simular_cascada');
        formData.append('ids', JSON.stringify(ids));

        Swal.fire({
            title: 'Calculando impacto...',
            didOpen: () => { Swal.showLoading(); }
        });

        fetch('mantenimiento_code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                let html = '<div class="text-left font-lexend space-y-6">';
                html += '<p class="text-xs font-bold text-red-500 uppercase tracking-widest bg-red-50 p-3 rounded-xl border border-red-100">Atención: Esta acción eliminará permanentemente todos estos datos:</p>';
                
                html += '<div class="grid grid-cols-2 gap-4">';
                data.conteo.forEach(item => {
                    html += `<div class="p-4 bg-slate-50 rounded-[1.5rem] border border-slate-100">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mb-1">${item.entidad}</p>
                                <p class="text-xl font-black text-slate-800">${item.total}</p>
                             </div>`;
                });
                html += '</div>';

                html += '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
                html += `<div class="p-4 bg-blue-50 rounded-2xl border border-blue-100 flex items-center gap-3">
                            <i class="fas fa-database text-blue-400"></i>
                            <div>
                                <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest leading-none mb-1">Ahorro en Base de Datos</p>
                                <p class="text-base font-black text-blue-700">${data.db_reclaimed}</p>
                            </div>
                         </div>`;
                html += `<div class="p-4 bg-indigo-50 rounded-2xl border border-indigo-100 flex items-center gap-3">
                            <i class="fas fa-hard-drive text-indigo-400"></i>
                            <div>
                                <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest leading-none mb-1">Espacio Disco Recuperado</p>
                                <p class="text-base font-black text-indigo-700">${data.disk_reclaimed}</p>
                            </div>
                         </div>`;
                html += '</div>';

                html += '<div class="p-4 bg-amber-50 rounded-2xl border border-amber-100"><p class="text-[10px] font-bold text-amber-700 italic">Total de registros afectados: <span class="font-black">' + data.total_global + '</span></p></div>';
                html += '</div>';

                Swal.fire({
                    title: 'Confirmar Eliminación',
                    html: html,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '¡SÍ, BORRAR TODO!',
                    cancelButtonText: 'Abortar',
                    confirmButtonColor: '#ef4444',
                    width: '650px'
                }).then((resConfirm) => {
                    if (resConfirm.isConfirmed) {
                        ejecutarBorrado(ids);
                    }
                });
            }
        });
    }

    function ejecutarBorrado(ids) {
        const formData = new FormData();
        formData.append('action', 'ejecutar_cascada');
        formData.append('ids', JSON.stringify(ids));

        Swal.fire({
            title: 'Ejecutando limpieza...',
            html: 'Por favor, no cierres la ventana.',
            didOpen: () => { Swal.showLoading(); }
        });

        fetch('mantenimiento_code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                Swal.fire({
                    title: '¡Limpieza Completada!',
                    text: `Se han eliminado ${data.total_borrado} registros con éxito.`,
                    icon: 'success'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    }

    function analizar(tipo) {
        const formData = new FormData();
        formData.append('action', 'analisis_especifico');
        formData.append('tipo', tipo);

        fetch('mantenimiento_code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                mostrarResultado(tipo, data);
            }
        });
    }

    function mostrarResultado(tipo, data) {
        let html = '<div class="text-left space-y-4 font-lexend">';
        
        if (data.registros.length === 0) {
            html += '<p class="p-8 text-center text-slate-400 italic font-bold uppercase tracking-widest">No se han detectado inconsistencias</p>';
        } else {
            html += `<div class="flex justify-between items-center mb-4">
                        <p class="text-xs font-bold text-slate-500">${data.mensaje}</p>
                        ${tipo === 'archivos_musica_huerfanos' ? `<button onclick="borrarTodosHuerfanos()" class="px-3 py-1.5 bg-red-50 text-red-600 text-[9px] font-black uppercase tracking-widest rounded-lg border border-red-100 hover:bg-red-600 hover:text-white transition-all">Borrar Todos</button>` : ''}
                     </div>`;
            html += '<div class="max-h-96 overflow-y-auto pr-2 space-y-2 custom-scrollbar">';
            data.registros.forEach(r => {
                html += `<div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex justify-between items-center group" id="huerfano-${r.id.replace('/', '-')}">
                            <div>
                                <p class="text-[11px] font-black text-slate-700 uppercase leading-tight">${r.nombre}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase italic">${r.detalle}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">ID: #${r.id}</span>
                                ${tipo === 'archivos_musica_huerfanos' ? `<button onclick="borrarHuerfano('${r.id}')" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-red-400 flex items-center justify-center hover:bg-red-50 hover:text-red-600 transition-all opacity-0 group-hover:opacity-100" title="Borrar Archivo"><i class="fas fa-trash-can text-xs"></i></button>` : ''}
                            </div>
                         </div>`;
            });
            html += '</div>';
        }
        html += '</div>';

        Swal.fire({
            title: data.titulo,
            html: html,
            icon: data.registros.length > 0 ? 'warning' : 'success',
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#1e293b',
            width: '650px'
        });
    }

    function borrarHuerfano(id) {
        const formData = new FormData();
        formData.append('action', 'borrar_archivo_huerfano');
        formData.append('id', id);

        const rowId = 'huerfano-' + id.replace('/', '-');
        const row = document.getElementById(rowId);
        if(row) row.style.opacity = '0.5';

        fetch('mantenimiento_code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                if(row) row.remove();
                if(data.carpeta_borrada) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Archivo y carpeta eliminados',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            } else {
                if(row) row.style.opacity = '1';
                alert(data.message);
            }
        });
    }

    async function borrarTodosHuerfanos() {
        const buttons = document.querySelectorAll('[onclick^="borrarHuerfano("]');
        if (buttons.length === 0) return;

        if(!confirm(`¿Estás seguro de que quieres borrar los ${buttons.length} archivos detectados?`)) return;

        Swal.fire({
            title: 'Limpieza masiva...',
            html: 'Borrando archivos uno a uno...',
            didOpen: () => { Swal.showLoading(); }
        });

        for (let btn of buttons) {
            const id = btn.getAttribute('onclick').match(/'([^']+)'/)[1];
            const formData = new FormData();
            formData.append('action', 'borrar_archivo_huerfano');
            formData.append('id', id);

            await fetch('mantenimiento_code.php', { method: 'POST', body: formData });
        }

        Swal.fire('Completado', 'Se han procesado todos los archivos.', 'success').then(() => {
            location.reload();
        });
    }

    // Mantener la función de reparación de notas heredada
    function simularReparacion() { analizar('notas_huerfanas'); }
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
