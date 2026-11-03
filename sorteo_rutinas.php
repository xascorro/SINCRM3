<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header de Sección -->
        <div class="mb-6 sm:mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tight mb-1 sm:mb-2 flex items-center gap-2.5 sm:gap-3 italic">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fa-solid fa-wand-magic-sparkles text-base sm:text-lg"></i></span>
                    Orden de Salida <span class="text-slate-300 font-light mx-1 sm:mx-2">/</span> Rutinas
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium">Generación aleatoria y gestión de turnos de actuación.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
                <button onclick="toggleAddSorteoPanel()" class="flex-1 sm:flex-initial justify-center px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-[11px] sm:text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <i class="fas fa-random text-xs"></i> Nuevo Sorteo
                </button>
                <button onclick="toggleAnularPanel()" class="px-3.5 sm:px-5 py-2.5 sm:py-3 bg-white border border-red-200 text-red-500 font-bold text-[11px] sm:text-xs uppercase tracking-wider rounded-xl sm:rounded-2xl hover:bg-red-50 active:scale-[0.98] transition-all flex items-center gap-2 shadow-sm">
                    <i class="fas fa-trash-can text-xs"></i> Anular Todo
                </button>
                <a href="./informes/inscripciones_numericas_rutinas.php?id_competicion=<?=$_SESSION['id_competicion_activa']?>&titulo=Orden de salida" target="_blank" class="px-3.5 sm:px-5 py-2.5 sm:py-3 bg-emerald-600 text-white font-bold text-[11px] sm:text-xs uppercase tracking-wider rounded-xl sm:rounded-2xl hover:bg-emerald-700 active:scale-[0.98] transition-all flex items-center gap-2 shadow-md">
                    <i class="fas fa-download text-xs"></i> PDF Oficial
                </a>
            </div>
        </div>

        <!-- Panel Nuevo Sorteo (Colapsable) -->
        <div id="addSorteoPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <h2 class="text-lg sm:text-xl font-black text-slate-800 mb-4 sm:mb-8 flex items-center gap-2.5 sm:gap-3"><i class="fas fa-dice text-blue-600"></i> Configurar Sorteo por Fase</h2>
                <form id="sortearForm" action="sorteo_rutinas_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-6">
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Seleccionar Fase de Competición</label>
                        <?php 
                        ob_start();
                        include('includes/fases_competicion_select_option.php');
                        $select_html = ob_get_clean();
                        $select_html = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_html);
                        $select_html = str_replace("name='fase'", "name='id_fase'", $select_html);
                        $select_html = str_replace("class='form-control'", "class='v3-select-fix'", $select_html);
                        echo $select_html;
                        ?>
                    </div>
                    <div class="md:col-span-4 flex items-end">
                        <input type="hidden" name="save_btn" value="1">
                        <button id="sortearBtn" type="submit" class="w-full py-3.5 sm:py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg hover:bg-blue-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2.5 group">
                            <i class="fa-solid fa-wand-sparkles group-hover:rotate-12 transition-transform"></i> Ejecutar Sorteo Mágico
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel Anular (Colapsable) -->
        <div id="anularPanel" class="hidden mb-6 sm:mb-10 animate-fade-in-down">
            <div class="bg-red-50 rounded-2xl sm:rounded-[2.5rem] p-4 sm:p-8 border border-red-100 flex flex-col md:flex-row items-center justify-between gap-4 sm:gap-8">
                <div class="flex items-center gap-4 sm:gap-6 w-full md:w-auto">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-white flex items-center justify-center text-red-500 shadow-sm border border-red-100 flex-shrink-0"><i class="fas fa-triangle-exclamation text-lg sm:text-2xl"></i></div>
                    <div>
                        <h3 class="text-lg font-black text-red-800 leading-tight">¿Anular todos los sorteos?</h3>
                        <p class="text-sm text-red-600 font-medium">Esta acción eliminará el orden de salida de TODAS las fases. No se puede deshacer.</p>
                    </div>
                </div>
                <form action="sorteo_rutinas_code.php" method="POST" class="w-full md:w-auto flex items-center justify-end gap-3 sm:gap-4">
                    <button type="button" onclick="toggleAnularPanel()" class="px-4 py-2.5 text-xs font-black uppercase text-slate-400 hover:text-slate-600">Cancelar</button>
                    <button type="submit" name="delete_btn" class="px-6 sm:px-8 py-3 bg-red-600 text-white font-black uppercase text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-md active:scale-[0.98] transition-all">Confirmar Borrado</button>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        <?php include('includes/alertas_v4.php'); ?>


        <!-- LISTADO POR CARRILES -->
        <div class="space-y-10 sm:space-y-16">
            <?php
            $query = "SELECT DISTINCT f.id as id_fase, f.id_categoria, c.nombre as categoria, m.nombre as modalidad 
                      FROM fases f, categorias c, modalidades m 
                      WHERE f.id_modalidad=m.id AND f.id_categoria = c.id AND f.id_competicion = ".$_SESSION['id_competicion_activa']." 
                        AND m.id != 8 AND (f.id_figura IS NULL OR f.id_figura = 0)
                      ORDER BY f.orden";
            $query_fases = mysqli_query($connection, $query);
            while ($row_fases = mysqli_fetch_assoc($query_fases)):
            ?>
            <div class="relative">
                <!-- Título de Fase (Sticky Header) -->
                <div class="sticky top-16 sm:top-20 z-20 bg-surface/90 backdrop-blur-md py-3 sm:py-4 mb-4 sm:mb-6 border-b border-slate-200/80 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-slate-800 flex items-center justify-center text-white font-black text-[11px] sm:text-xs shadow-xs flex-shrink-0">
                            <?php echo strtoupper(substr($row_fases['modalidad'],0,1)); ?>
                        </div>
                        <h2 class="text-base sm:text-2xl font-black text-slate-800 tracking-tight truncate">
                            <?php echo $row_fases['modalidad'];?> <span class="text-blue-600 ml-1.5 sm:ml-2 font-medium"><?php echo $row_fases['categoria'];?></span>
                        </h2>
                    </div>
                    <?php
                    $q_count = "SELECT COUNT(*) as total FROM rutinas WHERE id_fase = ".$row_fases['id_fase'];
                    $num_r = mysqli_fetch_assoc(mysqli_query($connection, $q_count))['total'];
                    ?>
                    <span class="px-3 sm:px-4 py-1 sm:py-1.5 bg-white text-slate-500 text-[9px] sm:text-[10px] font-black uppercase tracking-wider rounded-full border border-slate-200 shadow-xs flex-shrink-0 tabular-nums"><?php echo $num_r; ?> Rutinas</span>
                </div>

                <!-- Carril de Salida -->
                <div class="grid grid-cols-1 gap-2.5 sm:gap-3 relative">
                    <!-- Línea de Conexión Visual (Desktop) -->
                    <div class="absolute left-10 top-0 bottom-0 w-px bg-slate-200 hidden md:block"></div>

                    <?php
                    $query_r = "SELECT r.id as id_rutina, r.orden, cl.nombre_corto as nombre_club 
                                FROM rutinas r, clubes cl 
                                WHERE r.id_fase = ".$row_fases['id_fase']." AND r.id_club = cl.id 
                                ORDER BY orden";
                    $res_r = mysqli_query($connection, $query_r);
                    if(mysqli_num_rows($res_r) > 0):
                        while ($row = mysqli_fetch_assoc($res_r)):
                            $nombres_q = "SELECT group_concat(n.nombre, ' ', n.apellidos SEPARATOR ', ') as atletas 
                                          FROM rutinas_participantes rp 
                                          JOIN nadadoras n ON rp.id_nadadora = n.id 
                                          WHERE rp.reserva = 'no' AND rp.id_rutina = ".$row['id_rutina'];
                            $nombres = mysqli_fetch_assoc(mysqli_query($connection, $nombres_q))['atletas'];
                            
                            // Lógica de color de carril (Cortes)
                            $accent_color = "border-l-slate-200";
                            $bg_color = "bg-white";
                            $orden_label = $row['orden'];

                            if($row['orden'] <= -1 && $row['orden'] >= -9) {
                                $orden_label = "PS";
                                $accent_color = "border-l-amber-400";
                                $bg_color = "bg-amber-50/30";
                            } else if($row['orden'] <= -10) {
                                $orden_label = "E";
                                $accent_color = "border-l-purple-400";
                                $bg_color = "bg-purple-50/30";
                            } else if($row['orden'] == '1') {
                                $accent_color = "border-l-emerald-500";
                                $bg_color = "bg-emerald-50/30";
                            }
                    ?>
                    <!-- Tarjeta de Salida: Vista Móvil + Desktop integradas -->
                    <div class="<?php echo $bg_color; ?> rounded-xl sm:rounded-2xl p-3 sm:p-5 md:p-6 shadow-xs sm:shadow-sm border border-slate-100/90 border-l-4 sm:border-l-[8px] <?php echo $accent_color; ?> group hover:shadow-md transition-all relative z-10 ml-0 md:ml-4">
                        
                        <!-- Vista Móvil (< md) -->
                        <div class="md:hidden flex items-start gap-3">
                            <!-- Dorsal Móvil -->
                            <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col items-center justify-center shadow-xs">
                                <span class="text-[8px] font-black uppercase text-slate-400 leading-none">Ord</span>
                                <span class="text-sm font-black text-slate-800 leading-none mt-0.5 tabular-nums"><?php echo $orden_label; ?></span>
                            </div>
                            
                            <!-- Contenido Móvil -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="text-[9px] font-black text-blue-700 uppercase bg-blue-50 px-2 py-0.5 rounded border border-blue-100/60 truncate"><?php echo $row['nombre_club']; ?></span>
                                    <span class="text-[9px] font-bold text-slate-300 tabular-nums">#<?php echo $row['id_rutina']; ?></span>
                                </div>
                                <p class="text-xs font-bold text-slate-700 leading-snug"><?php echo $nombres; ?></p>
                            </div>
                        </div>

                        <!-- Vista Desktop (>= md) -->
                        <div class="hidden md:flex items-center gap-6">
                            <!-- Dorsal / Orden -->
                            <div class="flex-shrink-0 w-20 text-center">
                                <p class="text-[9px] font-black uppercase text-slate-400 mb-0.5">Orden</p>
                                <span class="text-xl font-black text-slate-800 tabular-nums"><?php echo $orden_label; ?></span>
                            </div>

                            <!-- Club -->
                            <div class="flex-shrink-0 w-32 border-x border-slate-100 px-4 text-center">
                                <p class="text-[9px] font-black uppercase text-slate-400 mb-0.5">Entidad</p>
                                <span class="text-xs font-black text-blue-600 uppercase tracking-wider"><?php echo $row['nombre_club']; ?></span>
                            </div>

                            <!-- Nadadoras -->
                            <div class="flex-1 text-left">
                                <p class="text-[9px] font-black uppercase text-slate-400 mb-0.5">Participantes</p>
                                <p class="text-sm font-bold text-slate-600 italic leading-snug"><?php echo $nombres; ?></p>
                            </div>

                            <!-- ID Interno -->
                            <div class="flex-shrink-0 text-right opacity-30 group-hover:opacity-100 transition-opacity">
                                <span class="text-[10px] font-black text-slate-300 tabular-nums">ID #<?php echo $row['id_rutina']; ?></span>
                            </div>
                        </div>

                    </div>
                    <?php endwhile; else: ?>
                        <div class="p-6 sm:p-10 bg-slate-50/50 rounded-2xl sm:rounded-[2.5rem] border-2 border-dashed border-slate-200 text-center">
                            <p class="text-slate-400 italic text-xs sm:text-sm font-medium">Pendiente de sorteo</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

    </div>
</main>

<!-- Modal de Animación Mágica (v3.0 Refined) -->
<div id="animacionModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-slate-900/90 backdrop-blur-xl transition-all duration-500 opacity-0">
    <div class="text-center">
        <div class="relative inline-block mb-12">
            <!-- Partículas rotando (CSS puro) -->
            <div class="absolute inset-0 animate-spin-slow">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-3 h-3 bg-blue-400 rounded-full blur-sm"></div>
                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-3 h-3 bg-emerald-400 rounded-full blur-sm"></div>
            </div>
            <i id="iconoAnimacion" class="fa-solid fa-wand-magic-sparkles text-7xl text-white drop-shadow-[0_0_20px_rgba(255,255,255,0.5)]"></i>
        </div>
        <h2 id="animationText" class="text-3xl font-black text-white tracking-tighter mb-4">Invocando el azar...</h2>
        <p class="text-slate-400 font-medium uppercase tracking-[0.3em] text-xs">Sistema de Sorteo SINCRM v3.0</p>
    </div>
</div>

<script>
function toggleAddSorteoPanel() { document.getElementById('addSorteoPanel').classList.toggle('hidden'); }
function toggleAnularPanel() { document.getElementById('anularPanel').classList.toggle('hidden'); }

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('sortearForm');
    const modal = document.getElementById('animacionModal');
    const text = document.getElementById('animationText');
    const icon = document.getElementById('iconoAnimacion');

    if(form) {
        form.addEventListener('submit', function(e) {
            const faseSelect = document.querySelector('select[name="id_fase"]');
            const selectedOption = faseSelect.options[faseSelect.selectedIndex];
            const isAll = faseSelect.value === '0';
            
            // Si es una fase concreta, comprobamos si ya está sorteada mediante un atributo de datos
            // (Necesitamos actualizar el include de fases para que nos dé esta info o hacerlo vía JS)
            // Por simplicidad y UX, usaremos un enfoque de "confirmación siempre que sea individual"
            // o mejor aún, si el usuario elige una fase, lanzamos el aviso.
            
            if(!isAll) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Confirmar Sorteo?',
                    text: "Se generará un nuevo orden de salida para esta fase. Si ya existía uno, será sobreescrito.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Sí, Sortear',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        popup: 'rounded-[2rem]',
                        confirmButton: 'rounded-xl px-6 py-3 font-black uppercase text-xs tracking-widest',
                        cancelButton: 'rounded-xl px-6 py-3 font-black uppercase text-xs tracking-widest'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        ejecutarAnimacion(form);
                    }
                });
            } else {
                e.preventDefault();
                ejecutarAnimacion(form);
            }
        });

        function ejecutarAnimacion(formElement) {
            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.add('opacity-100'), 10);
            
            const frases = [
                { t: "Consultando al oráculo de Delfos...", c: "text-blue-400", i: "fa-spaghetti-monster-flying" },
                { t: "Mezclando los dorsales con elegancia...", c: "text-purple-400", i: "fa-shuffle" },
                { t: "Sacudiendo la chistera mágica...", c: "text-amber-400", i: "fa-hat-wizard" },
                { t: "Invocando el espíritu de la competición...", c: "text-red-400", i: "fa-ghost" },
                { t: "Alineando los astros de la piscina...", c: "text-indigo-400", i: "fa-star-and-crescent" },
                { t: "Batiendo récords de aleatoriedad...", c: "text-emerald-400", i: "fa-bolt" },
                { t: "¡Habemus Sorteo!", c: "text-white", i: "fa-check-double" }
            ];

            let step = 0;
            const interval = setInterval(() => {
                if(step < frases.length) {
                    text.textContent = frases[step].t;
                    icon.className = `fa-solid ${frases[step].i} text-7xl ${frases[step].c} transition-all duration-500`;
                    step++;
                } else {
                    clearInterval(interval);
                    formElement.submit();
                }
            }, 800);
        }
    }
});
</script>

<style>
@keyframes spin-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.animate-spin-slow { animation: spin-slow 3s linear infinite; }
</style>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>
