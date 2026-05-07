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
                </div>
            </div>

        </div>

    </div>
</main>

<script>
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
            html += `<p class="text-xs font-bold text-slate-500 mb-4">${data.mensaje}</p>`;
            html += '<div class="max-h-64 overflow-y-auto pr-2 space-y-2 custom-scrollbar">';
            data.registros.forEach(r => {
                html += `<div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex justify-between items-center">
                            <div>
                                <p class="text-[11px] font-black text-slate-700 uppercase leading-tight">${r.nombre}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase italic">${r.detalle}</p>
                            </div>
                            <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">ID: #${r.id}</span>
                         </div>`;
            });
            html += '</div>';
        }
        html += '</div>';

        Swal.fire({
            title: data.titulo,
            html: html,
            icon: data.registros.length > 0 ? 'warning' : 'success',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#1e293b',
            width: '600px'
        });
    }

    // Mantener la función de reparación de notas heredada
    function simularReparacion() { analizar('notas_huerfanas'); }
</script>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
