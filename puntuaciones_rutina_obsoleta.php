<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_rutina = $_POST['id_rutina'] ?? $_GET['id_rutina'] ?? 0;
if (!$id_rutina) {
    header("Location: puntuaciones_lista_rutinas.php");
    exit();
}

$q_rutina = "SELECT r.*, c.nombre_corto as nombre_club, m.nombre as nombre_modalidad, cat.nombre as nombre_categoria, f.id_competicion 
             FROM rutinas r 
             JOIN fases f ON r.id_fase = f.id 
             JOIN modalidades m ON f.id_modalidad = m.id 
             JOIN categorias cat ON f.id_categoria = cat.id 
             JOIN clubes c ON r.id_club = c.id 
             WHERE r.id = $id_rutina";
$rutina = mysqli_fetch_assoc(mysqli_query($connection, $q_rutina));
$id_fase = $rutina['id_fase'];
$id_competicion = $rutina['id_competicion'];

$q_nombres = "SELECT GROUP_CONCAT(n.nombre SEPARATOR ', ') as nombres 
              FROM rutinas_participantes rp 
              JOIN nadadoras n ON n.id = rp.id_nadadora 
              WHERE rp.reserva = 'no' AND rp.id_rutina = $id_rutina";
$nombres = mysqli_fetch_assoc(mysqli_query($connection, $q_nombres))['nombres'] ?? '';

$orden_display = ($rutina['orden'] == -1) ? 'PS' : $rutina['orden'];

// Obtener Paneles de Reglamento Anterior
$q_paneles = "SELECT * FROM paneles WHERE id_competicion = $id_competicion AND obsoleto = 'si' AND puntua = 'si' ORDER BY id ASC";
$res_paneles = mysqli_query($connection, $q_paneles);
$paneles = [];
while ($p = mysqli_fetch_assoc($res_paneles)) {
    $p['jueces'] = [];
    $q_jueces = "SELECT pj.*, j.nombre as nombre_juez 
                 FROM panel_jueces pj 
                 JOIN jueces j ON pj.id_juez = j.id 
                 WHERE pj.id_fase = $id_fase AND pj.id_panel = " . $p['id'] . " 
                 ORDER BY pj.numero_juez ASC";
    $res_jueces = mysqli_query($connection, $q_jueces);
    while ($j = mysqli_fetch_assoc($res_jueces)) {
        // Cargar nota actual si existe
        $q_nota = "SELECT * FROM puntuaciones_jueces WHERE id_rutina = $id_rutina AND id_panel_juez = " . $j['id'];
        $nota_data = mysqli_fetch_assoc(mysqli_query($connection, $q_nota));
        $j['nota_actual'] = $nota_data['nota'] ?? '';
        $p['jueces'][] = $j;
    }
    $paneles[] = $p;
}
?>

<style>
    @keyframes fast-blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    .animate-error-blink {
        animation: fast-blink 0.4s ease-in-out infinite;
    }
</style>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2 animate-fade-in">
                    <span class="px-3 py-1 bg-slate-200 text-slate-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-slate-300 shadow-sm"><i class="fas fa-clock-rotate-left"></i> Sistema OBSOLETO</span>
                </div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-calculator text-lg"></i></span>
                    Puntuación Tradicional
                </h1>
                <p class="text-slate-500 font-medium text-lg uppercase tracking-tight italic">
                    <?php echo $rutina['nombre_modalidad']." ".$rutina['nombre_categoria']; ?>
                </p>
            </div>
            
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 text-right min-w-[250px]">
                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Nota Final Estimada</p>
                <p id="totalScore" class="text-3xl font-black text-blue-600"><?php echo number_format($rutina['nota_final'], 4); ?></p>
            </div>
            <div class="flex gap-3">
                <div class="bg-white p-1.5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-1">
                    <button type="button" onclick="setVista('cards')" id="btn-vista-cards" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-slate-900 text-white shadow-lg">
                        <i class="fas fa-th-large mr-2"></i> Tarjetas
                    </button>
                    <button type="button" onclick="setVista('table')" id="btn-vista-table" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-400 hover:bg-slate-50">
                        <i class="fas fa-table mr-2"></i> Tabla
                    </button>
                </div>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <!-- Rutina Info Card -->
        <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-200 mb-10 flex flex-col md:flex-row gap-8 items-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-slate-50 rounded-bl-full -z-0"></div>
            <div class="w-20 h-20 rounded-3xl bg-blue-50 text-blue-500 flex flex-col items-center justify-center border border-blue-100 shadow-inner flex-shrink-0 z-10">
                <span class="text-[10px] font-black uppercase opacity-40 leading-none mb-1">Orden</span>
                <span class="text-3xl font-black leading-none"><?php echo $orden_display; ?></span>
            </div>
            <div class="flex-1 z-10">
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 bg-slate-100 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-lg border border-slate-200"><?php echo $rutina['nombre_club']; ?></span>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">ID: #<?php echo $id_rutina; ?></span>
                </div>
                <?php if($rutina['nombre_rutina']): ?>
                    <h3 class="text-xl font-black text-slate-800 italic tracking-tighter mb-1">"<?php echo $rutina['nombre_rutina']; ?>"</h3>
                <?php endif; ?>
                <p class="text-sm font-bold text-slate-500 leading-relaxed"><i class="fas fa-users text-slate-300 mr-2"></i><?php echo $nombres; ?></p>
            </div>
        </div>

        <form action="puntuaciones_rutina_obsoleta_code.php" enctype="multipart/form-data" method="post" id="formulario">
            <input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
            <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
            <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">

        <form action="puntuaciones_rutina_obsoleta_code.php" enctype="multipart/form-data" method="post" id="formulario">
            <input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
            <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
            <input type="hidden" name="id_competicion" value="<?php echo $id_competicion; ?>">

            <!-- VISTA DE TARJETAS -->
            <div id="view-cards" class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
                <?php 
                $ti_counter = 1;
                foreach ($paneles as $p_idx => $p): 
                    $id_panel = $p['id'];
                    $peso = $p['peso'];
                ?>
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden flex flex-col panel-card" data-panel-id="<?php echo $id_panel; ?>" data-peso="<?php echo $peso; ?>">
                    <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center" style="background-color: <?php echo $p['color']; ?>20;">
                        <h2 class="text-lg font-black text-slate-800 uppercase tracking-tighter flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white shadow-sm" style="background-color: <?php echo $p['color']; ?>;">
                                <i class="fas fa-users-cog text-xs"></i>
                            </div>
                            <?php echo $p['nombre']; ?>
                        </h2>
                        <span class="px-2 py-1 bg-white/50 backdrop-blur-sm rounded-lg text-[9px] font-black text-slate-500 border border-slate-200 uppercase tracking-widest">Peso: <?php echo $peso; ?>%</span>
                    </div>

                    <div class="p-8 space-y-4 flex-1">
                        <div class="grid grid-cols-1 gap-3">
                            <?php foreach ($p['jueces'] as $j_idx => $j): 
                                $media_class = ($j['id_juez'] == '108') ? 'juez-media bg-amber-50 border-amber-200' : '';
                                $ti = $ti_counter++;
                            ?>
                            <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-2xl border border-slate-100 group hover:border-blue-300 hover:bg-white transition-all <?php echo $media_class; ?>">
                                <span class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-400 group-hover:text-blue-500 transition-colors shadow-sm">J<?php echo $j['numero_juez']; ?></span>
                                <div class="flex-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate"><?php echo $j['nombre_juez']; ?></p>
                                    <input type="text" 
                                           inputmode="decimal" 
                                           name="nota_p<?php echo $id_panel; ?>_j<?php echo $j['id']; ?>" 
                                           id="nota_p<?php echo $id_panel; ?>_j<?php echo $j['id']; ?>" 
                                           value="<?php echo $j['nota_actual']; ?>" 
                                           tabindex="<?php echo $ti; ?>" 
                                           oninput="syncTableInput(this)"
                                           class="w-full mt-1 bg-transparent border-none p-0 text-xl font-black text-slate-800 focus:ring-0 placeholder:text-slate-200 score-input form-control <?php echo $media_class; ?>" 
                                           placeholder="0.0"
                                           data-panel-id="<?php echo $id_panel; ?>"
                                           data-juez-id="<?php echo $j['id']; ?>">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Media Panel</span>
                            <span class="text-xl font-black text-slate-800 panel-mean" id="mean_p<?php echo $id_panel; ?>">0.0000</span>
                        </div>
                        <div class="flex justify-between items-center opacity-60">
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest italic">Contribución Final</span>
                            <span class="text-xs font-black text-slate-500 panel-contribution" id="contrib_p<?php echo $id_panel; ?>">0.0000</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- VISTA DE TABLA -->
            <div id="view-table" class="hidden mb-10">
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 bg-slate-50">
                                    <th class="px-8 py-6">Panel de Jueces</th>
                                    <th class="px-4 py-6 text-center w-16">Peso</th>
                                    <?php 
                                    $max_jueces = 0;
                                    foreach($paneles as $p) if(count($p['jueces']) > $max_jueces) $max_jueces = count($p['jueces']);
                                    for($i=1; $i<=$max_jueces; $i++): 
                                    ?>
                                        <th class="px-4 py-6 text-center">J<?php echo $i; ?></th>
                                    <?php endfor; ?>
                                    <th class="px-6 py-6 text-center">Media</th>
                                    <th class="px-8 py-6 text-right">Contribución</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php 
                                $ti_counter_table = 1;
                                foreach($paneles as $p_idx => $p): 
                                    $id_panel = $p['id'];
                                ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-6 h-6 rounded-md flex items-center justify-center text-[10px] text-white" style="background-color: <?php echo $p['color']; ?>;"><i class="fas fa-users-cog"></i></div>
                                            <span class="text-xs font-black text-slate-700 uppercase tracking-tight"><?php echo $p['nombre']; ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-5 text-center"><span class="px-2 py-1 bg-slate-100 rounded-lg text-[9px] font-black text-slate-400"><?php echo $p['peso']; ?>%</span></td>
                                    <?php 
                                    for($i=0; $i<$max_jueces; $i++):
                                        $j = $p['jueces'][$i] ?? null;
                                        $ti = $j ? $ti_counter_table++ : 0;
                                    ?>
                                        <td class="px-2 py-5 text-center">
                                            <?php if($j): ?>
                                                <input type="text" 
                                                       inputmode="decimal" 
                                                       id="table_nota_p<?php echo $id_panel; ?>_j<?php echo $j['id']; ?>"
                                                       value="<?php echo $j['nota_actual']; ?>" 
                                                       tabindex="<?php echo $ti; ?>"
                                                       oninput="syncInput(this, 'nota_p<?php echo $id_panel; ?>_j<?php echo $j['id']; ?>')"
                                                       class="w-16 h-10 text-center text-sm font-black rounded-xl border-slate-200 shadow-inner focus:ring-blue-500 focus:border-blue-500 table-score-input"
                                                       placeholder="0.0">
                                            <?php else: ?>
                                                <span class="text-slate-200">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                    <td class="px-6 py-5 text-center font-black text-slate-700 panel-mean-table-<?php echo $id_panel; ?>">0.0000</td>
                                    <td class="px-8 py-5 text-right font-black text-blue-600 panel-contrib-table-<?php echo $id_panel; ?>">0.0000</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- BLOQUE DE RESUMEN DE CÁLCULO -->
            <div class="bg-slate-900 rounded-[2.5rem] p-10 text-white mb-10 relative overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h3 class="text-2xl font-black tracking-tighter uppercase italic mb-6 flex items-center gap-3">
                            <i class="fas fa-microchip text-blue-400"></i> Auditoría de Cálculo
                        </h3>
                        <div id="calculationLog" class="space-y-3 text-xs font-medium text-slate-400">
                            <!-- Inyectado por JS -->
                            <p>Esperando entrada de notas...</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black uppercase text-blue-400 tracking-[0.3em] mb-2">Puntuación Final Generada</p>
                        <div class="flex items-end justify-end gap-4">
                            <span id="finalScoreDisplay" class="text-7xl font-black tracking-tighter leading-none">0.0000</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOQUE: PENALIZACIONES -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 mb-10 p-8 md:p-10">
                <div class="flex items-center justify-between mb-8 pb-6 border-b border-slate-100">
                    <h2 class="text-xl font-black text-slate-800 uppercase tracking-tighter flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center"><i class="fas fa-gavel text-sm"></i></div>
                        Gestión de Penalizaciones
                    </h2>
                    <div class="flex items-center gap-4 px-6 py-3 bg-red-50 rounded-2xl border border-red-100">
                        <span class="text-[10px] font-black text-red-400 uppercase tracking-widest leading-none">Total Deducción</span>
                        <span class="text-2xl font-black text-red-600">-<?php echo number_format(abs($total_pen), 4); ?></span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Listado Penalizaciones Aplicadas -->
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4">Penalizaciones Activas</p>
                        <div class="space-y-3">
                            <?php
                            $q_pen = "SELECT pr.id as id_pr, p.codigo, p.resumen, p.puntos, pt.nombre as tipo 
                                      FROM penalizaciones_rutinas pr 
                                      JOIN penalizaciones p ON pr.id_penalizacion = p.id 
                                      LEFT JOIN paneles_tipo pt ON p.id_paneles_tipo = pt.id 
                                      WHERE pr.id_rutina = $id_rutina";
                            $res_pen = mysqli_query($connection, $q_pen);
                            if (mysqli_num_rows($res_pen) > 0) {
                                while($pen = mysqli_fetch_assoc($res_pen)) {
                            ?>
                                <div class="p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center justify-between group">
                                    <div class="flex-1 pr-4">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 bg-red-100 text-red-600 text-[9px] font-black rounded uppercase"><?php echo $pen['codigo']; ?></span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase italic"><?php echo $pen['tipo'] ?? 'General'; ?></span>
                                        </div>
                                        <p class="text-xs font-bold text-slate-700 leading-tight"><?php echo $pen['resumen']; ?></p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="text-lg font-black text-red-600"><?php echo abs($pen['puntos']); ?></span>
                                        <a href="./puntuaciones_rutina_obsoleta_code.php?id_rutina=<?php echo $id_rutina; ?>&id_fase=<?php echo $id_fase; ?>&id_penalizaciones_rutinas=<?php echo $pen['id_pr']; ?>" class="w-8 h-8 rounded-lg bg-white text-slate-300 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all flex items-center justify-center border border-slate-200 shadow-sm"><i class="fas fa-trash-alt text-xs"></i></a>
                                    </div>
                                </div>
                            <?php 
                                }
                            } else {
                                echo "<p class='text-sm text-slate-400 italic font-bold'>No hay penalizaciones registradas.</p>";
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Añadir Penalización -->
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4">Añadir Penalización</p>
                        <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100">
                            <?php include('./includes/penalizaciones_select_option.php'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTÓN DE GUARDADO FINAL (No flotante) -->
            <div class="flex items-center justify-end p-10 bg-white rounded-[2.5rem] border border-slate-200 shadow-sm mb-20">
                <?php 
                $total_inputs = 0;
                foreach($paneles as $p) $total_inputs += count($p['jueces']);
                $ti_pen = $total_inputs + 1;
                $ti_save = $total_inputs + 2;
                ?>
                <button type="submit" name="save_btn" id="save_btn" tabindex="<?php echo $ti_save; ?>" class="px-16 py-5 bg-blue-600 text-white font-black uppercase text-sm tracking-widest rounded-2xl shadow-xl shadow-blue-500/20 hover:bg-blue-500 hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-3">
                    <i class="fas fa-check-double text-lg"></i> Guardar
                </button>
            </div>

        </form>
    </div>
</main>

<?php include('includes/scripts.php'); include('includes/footer.php'); ?>

<script>
// envios automáticos tras borrar penalización
$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('reload') === 'si'){
        setTimeout(() => { $('#save_btn').click(); }, 100);
    }
});

function setVista(tipo) {
    const d = new Date();
    d.setTime(d.getTime() + (30*24*60*60*1000));
    document.cookie = "pref_vista_obsoleta=" + tipo + ";expires=" + d.toUTCString() + ";path=/";

    const vCards = document.getElementById('view-cards');
    const vTable = document.getElementById('view-table');
    const bCards = document.getElementById('btn-vista-cards');
    const bTable = document.getElementById('btn-vista-table');

    if(tipo === 'cards') {
        vCards.classList.remove('hidden');
        vTable.classList.add('hidden');
        bCards.classList.add('bg-slate-900', 'text-white', 'shadow-lg');
        bCards.classList.remove('text-slate-400', 'hover:bg-slate-50');
        bTable.classList.remove('bg-slate-900', 'text-white', 'shadow-lg');
        bTable.classList.add('text-slate-400', 'hover:bg-slate-50');
    } else {
        vCards.classList.add('hidden');
        vTable.classList.remove('hidden');
        bTable.classList.add('bg-slate-900', 'text-white', 'shadow-lg');
        bTable.classList.remove('text-slate-400', 'hover:bg-slate-50');
        bCards.classList.remove('bg-slate-900', 'text-white', 'shadow-lg');
        bCards.classList.add('text-slate-400', 'hover:bg-slate-50');
    }
}

// Cargar vista preferida
document.addEventListener('DOMContentLoaded', () => {
    const pref = document.cookie.match(/pref_vista_obsoleta=([^;]+)/);
    if(pref && pref[1] === 'table') setVista('table');

    // Ajustar tabindex de penalizaciones dinámicamente
    const penSelect = document.getElementById('id_penalizacion');
    if(penSelect) {
        const totalInputs = document.querySelectorAll('.score-input').length;
        penSelect.setAttribute('tabindex', totalInputs + 1);
    }
});

function syncInput(source, targetId) {
    const target = document.getElementById(targetId);
    target.value = source.value;
    // Disparar blur para recalcular
    target.dispatchEvent(new Event('blur'));
}

function syncTableInput(source) {
    const tableInp = document.getElementById('table_' + source.id);
    if(tableInp) {
        tableInp.value = source.value;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.score-input, .table-score-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.backgroundColor = '#fef3c7'; // amber-100
            this.select();
        });

        input.addEventListener('blur', function() {
            this.style.backgroundColor = '';
            formatAndCalculate(this);
        });
        
        input.addEventListener('input', () => {
            input.value = input.value.replace(/,/g, '.');
        });

        // Navegación con Enter (Tabindex Vertical)
        input.addEventListener('keydown', function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                const currentTI = parseInt(this.getAttribute('tabindex')) || 0;
                let nextElement = null;
                let nextTI = Infinity;

                document.querySelectorAll('[tabindex]').forEach(el => {
                    const ti = parseInt(el.getAttribute('tabindex'));
                    if (ti > currentTI && ti < nextTI && !el.disabled) {
                        nextTI = ti;
                        nextElement = el;
                    }
                });

                if (nextElement) nextElement.focus();
                else document.getElementById('save_btn').focus();
            }
        });
    });

    calcularTodo();
});

function formatAndCalculate(input) {
    let val = input.value.trim().replace(/,/g, '.');
    const group = input.closest('.group') || input;
    
    // 0. Lógica de Media automática para Juez 108 (solo si está vacío)
    if(input.classList.contains('juez-media') && (val === '' || val === '0.0')) {
        const panelCard = input.closest('.panel-card');
        if(panelCard) {
            const otherInputs = Array.from(panelCard.querySelectorAll('.score-input')).filter(i => i !== input);
            let sum = 0;
            let count = 0;
            otherInputs.forEach(oi => {
                const v = parseFloat(oi.value);
                if(!isNaN(v)) { sum += v; count++; }
            });
            if(count > 0) {
                let media = sum / count;
                media = Math.round(media * 10) / 10; // Redondear a décima
                input.value = media.toFixed(1);
                val = input.value;
            }
        }
    }

    // Limpiar estados previos
    group.classList.remove('ring-4', 'ring-slate-400', 'bg-slate-200', 'ring-red-500', 'bg-red-100', 'animate-error-blink');
    input.classList.remove('is-invalid');

    if(val === '') {
        calcularTodo();
        return;
    }

    let num = parseFloat(val);
    const isNumeric = !isNaN(num) && isFinite(num);

    if (isNumeric) {
        // Lógica de conversión inteligente
        if (num > 10.0 && num <= 100.0) {
            num = num / 10.0;
        }
        
        // VALIDACIÓN DE RANGO: Si después de intentar convertir sigue fuera de 0-10
        if (num < 0 || num > 10.0) {
            group.classList.add('ring-4', 'ring-red-500', 'bg-red-100', 'animate-error-blink');
            input.classList.add('is-invalid');
        } else {
            // Redondear a la décima (0.1)
            num = Math.round(num * 10) / 10;
            input.value = num.toFixed(1);
        }
    } else {
        // ERROR: No es un número (ej: l3, 5ñ, etc)
        group.classList.add('ring-4', 'ring-red-500', 'bg-red-100', 'animate-error-blink');
        input.classList.add('is-invalid');
    }
    calcularTodo();
    checkFormValidity();
}

function checkFormValidity() {
    const hasErrors = document.querySelectorAll('.is-invalid').length > 0;
    const saveBtn = document.getElementById('save_btn');
    if (hasErrors) {
        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
        saveBtn.innerText = '⚠️ Error en notas';
    } else {
        saveBtn.disabled = false;
        saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        saveBtn.innerHTML = '<i class="fas fa-check-double text-lg"></i> Guardar Puntuación Oficial';
    }
}

function calcularTodo() {
    const panels = document.querySelectorAll('.panel-card');
    let totalScore = 0;
    let totalWeights = 0;
    let logHtml = '';

    panels.forEach(panel => {
        const panelId = panel.dataset.panelId;
        const peso = parseFloat(panel.dataset.peso);
        const name = panel.querySelector('h2').innerText.trim();
        const inputs = Array.from(panel.querySelectorAll('.score-input'));
        
        let notesMap = []; // [{id, val}]
        inputs.forEach(input => {
            let val = parseFloat(input.value);
            if (!isNaN(val)) notesMap.push({ id: input.id, val: val });
        });

        const result = calcularPanel(notesMap);
        
        // Reset estilos extremos (Cards y Tabla)
        inputs.forEach(inp => {
            // Card
            const cardGroup = inp.closest('.group');
            if(cardGroup) {
                cardGroup.classList.remove('border-red-400', 'bg-red-50', 'border-emerald-400', 'bg-emerald-50');
            }
            inp.classList.remove('text-red-600', 'text-emerald-600');

            // Tabla
            const tableInp = document.getElementById('table_' + inp.id);
            if(tableInp) {
                tableInp.classList.remove('bg-red-50', 'text-red-600', 'border-red-400', 'bg-emerald-50', 'text-emerald-600', 'border-emerald-400');
            }
        });

        // Marcar Extremos en UI (Cards y Tabla)
        result.eliminadas.forEach(el => {
            const inp = document.getElementById(el.id);
            const isMin = (el.val === result.minVal);
            const colorClass = isMin ? 'red' : 'emerald';

            if(inp) {
                const cardGroup = inp.closest('.group');
                if(cardGroup) {
                    cardGroup.classList.add(`border-${colorClass}-400`, `bg-${colorClass}-50`);
                }
                inp.classList.add(`text-${colorClass}-600`);
            }

            const tableInp = document.getElementById('table_' + el.id);
            if(tableInp) {
                tableInp.classList.add(`bg-${colorClass}-50`, `text-${colorClass}-600`, `border-${colorClass}-400`);
            }
        });

        // Actualizar UI
        document.getElementById(`mean_p${panelId}`).innerText = result.media.toFixed(4);
        const cellTableMean = document.querySelector(`.panel-mean-table-${panelId}`);
        if(cellTableMean) cellTableMean.innerText = result.media.toFixed(4);

        const contrib = result.media * (peso / 100) * 10;
        document.getElementById(`contrib_p${panelId}`).innerText = contrib.toFixed(4);
        const cellTableContrib = document.querySelector(`.panel-contrib-table-${panelId}`);
        if(cellTableContrib) cellTableContrib.innerText = contrib.toFixed(4);

        totalScore += contrib;
        totalWeights += peso;

        // Log
        logHtml += `<div class="flex items-center gap-4">
                        <span class="w-20 text-blue-400 font-black uppercase tracking-widest">${name}</span>
                        <div class="flex-1 bg-white/5 rounded-lg px-3 py-1 flex items-center justify-between">
                            <span class="text-slate-300">Notas: [${result.usadas.map(n => n.val).join(', ')}]</span>
                            ${result.eliminadas.length > 0 ? `<span class="text-slate-400 text-[10px]">Excluidas: <span class="text-red-400">${result.minVal}</span> y <span class="text-emerald-400">${result.maxVal}</span></span>` : ''}
                            <span class="font-black text-white ml-4">${result.media.toFixed(4)}</span>
                        </div>
                    </div>`;
    });

    if (totalWeights > 0 && totalWeights !== 100) {
        totalScore = (totalScore / totalWeights) * 100;
        logHtml += `<p class="text-[9px] text-amber-400 uppercase font-black tracking-widest mt-4">⚠️ Pesos normalizados (${totalWeights}%)</p>`;
    }

    document.getElementById('totalScore').innerText = totalScore.toFixed(4);
    document.getElementById('finalScoreDisplay').innerText = totalScore.toFixed(4);
    document.getElementById('calculationLog').innerHTML = logHtml;
}

function calcularPanel(notesMap) {
    let validas = notesMap.filter(n => n.val >= 0 && n.val <= 10);
    let eliminadas = [];
    let usadas = [...validas];
    let minVal = null;
    let maxVal = null;

    if (validas.length >= 5) {
        usadas.sort((a, b) => a.val - b.val);
        const minObj = usadas.shift();
        const maxObj = usadas.pop();
        eliminadas.push(minObj, maxObj);
        minVal = minObj.val;
        maxVal = maxObj.val;
    }

    let suma = usadas.reduce((a, b) => a + b.val, 0);
    let media = usadas.length > 0 ? (suma / usadas.length) : 0;

    return { media, usadas, eliminadas, minVal, maxVal };
}
</script>
