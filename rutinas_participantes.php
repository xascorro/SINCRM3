<?php
include('security.php');

$id_rutina = $_SESSION['id_rutina_usuario'] ?? 0;
$id_competicion = $_SESSION['id_competicion_usuario'] ?? 0;

if ($id_rutina == 0) {
    header('Location: rutinas.php');
    exit();
}

include('includes/header.php');
?>
<link rel="stylesheet" href="vendor/tom-select/tom-select.default.min.css">
<style>
/* Estilos Flotantes TomSelect */
.ts-wrapper.tom-select-nadadora {
    width: 100% !important;
}
.ts-wrapper.tom-select-nadadora .ts-control {
    background-color: #ffffff !important;
    border: 1.5px solid #bfdbfe !important;
    border-radius: 0.75rem !important;
    padding: 0.65rem 1rem !important;
    font-size: 0.8125rem !important;
    font-weight: 700 !important;
    color: #1e293b !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    min-height: 42px !important;
    display: flex !important;
    align-items: center !important;
    cursor: text !important;
}
.ts-wrapper.tom-select-nadadora.focus .ts-control {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
}
.ts-wrapper.tom-select-nadadora .ts-control input {
    font-size: 0.8125rem !important;
    font-weight: 700 !important;
    color: #1e293b !important;
}
.ts-wrapper.tom-select-nadadora .ts-control input::placeholder {
    color: #94a3b8 !important;
    font-weight: 500 !important;
}
.ts-wrapper.tom-select-nadadora.ts-typing .item {
    display: none !important;
}
.ts-dropdown {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 1rem !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    padding: 0.375rem !important;
    font-family: inherit !important;
    font-size: 0.8125rem !important;
    max-height: 240px !important;
    overflow-y: auto !important;
    z-index: 999999 !important;
}
.ts-dropdown .option {
    padding: 0.625rem 0.875rem !important;
    border-radius: 0.5rem !important;
    color: #1e293b !important;
    font-weight: 600 !important;
    cursor: pointer !important;
}
.ts-dropdown .option:hover,
.ts-dropdown .option.active {
    background-color: #eff6ff !important;
    color: #1d4ed8 !important;
}
.ts-dropdown .option[data-selectable="false"],
.ts-dropdown .option.disabled {
    opacity: 0.45 !important;
    background-color: #f8fafc !important;
    color: #94a3b8 !important;
    cursor: not-allowed !important;
}
</style>
<?php
include('includes/navbar.php');

if(isset($_POST['from_inscripciones'])) {
    $_SESSION['return_to_inscripciones'] = true;
} elseif(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'rutinas.php') !== false) {
    unset($_SESSION['return_to_inscripciones']);
}
$volver_url = (!empty($_SESSION['return_to_inscripciones'])) ? 'inscripciones.php?tab=rutinas' : 'rutinas.php';

// Fetch Routine Details
$query = "SELECT rutinas.id, rutinas.nombre as nombre_rutina, rutinas.id_fase, rutinas.id_club, fases.id_categoria, 
                 clubes.nombre_corto as nombre_club, modalidades.nombre as nombre_modalidad, 
                 categorias.nombre as nombre_categoria, modalidades.numero_participantes, 
                 modalidades.numero_reservas 
          FROM rutinas, fases, modalidades, categorias, clubes 
          WHERE rutinas.id=$id_rutina 
          AND rutinas.id_fase = fases.id 
          AND fases.id_modalidad = modalidades.id 
          AND fases.id_categoria = categorias.id 
          AND rutinas.id_club = clubes.id 
          AND fases.id_competicion = ".$id_competicion;

$res_rutina = mysqli_query($connection, $query);
$data = mysqli_fetch_assoc($res_rutina);

if(!$data) {
    header('Location: ' . $volver_url);
    exit();
}

// Año de referencia de temporada para validación de edades (RFEN/AQUA)
$temporada_año = 0;
if ($id_competicion > 0) {
    $q_comp = mysqli_query($connection, "SELECT temporada, fecha FROM competiciones WHERE id = '$id_competicion'");
    if ($r_comp = mysqli_fetch_assoc($q_comp)) {
        $t_raw = trim($r_comp['temporada'] ?? '');
        if (preg_match('/[\/\-](\d{4})/', $t_raw, $m_t)) {
            $temporada_año = intval($m_t[1]);
        } elseif (preg_match('/^\d{4}$/', $t_raw)) {
            $temporada_año = intval($t_raw);
        } else {
            $f_ev = new DateTime($r_comp['fecha'] ?? 'now');
            $m_ev = intval($f_ev->format('n'));
            $y_ev = intval($f_ev->format('Y'));
            $temporada_año = ($m_ev >= 9) ? ($y_ev + 1) : $y_ev;
        }
    }
}
if ($temporada_año <= 0) {
    $cur_m = intval(date('n'));
    $cur_y = intval(date('Y'));
    $temporada_año = ($cur_m >= 9) ? ($cur_y + 1) : $cur_y;
}

$nombre_modalidad = $data['nombre_modalidad'];
$nombre_categoria = $data['nombre_categoria'];
$nombre_club = $data['nombre_club'];
$nombre_rutina = $data['nombre_rutina'];
$numero_participantes = $data['numero_participantes'];
$numero_reservas = $data['numero_reservas'];
$id_club_rutina = $data['id_club'];
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-5xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header -->
        <div class="mb-6 sm:mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200/60"><?php echo $nombre_club; ?></span>
                    <span class="text-slate-300">/</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider"><?php echo $nombre_modalidad . ' · ' . $nombre_categoria; ?></span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-800 tracking-tight flex items-center gap-2.5 sm:gap-3">
                    <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200 shrink-0">
                        <i class="fas fa-users text-base sm:text-lg"></i>
                    </span>
                    <span>Participantes <span class="text-slate-300 font-light mx-1 hidden sm:inline">/</span> <span class="text-blue-600 italic text-xl sm:text-3xl block sm:inline"><?php echo $nombre_rutina ?: 'Rutina #' . $id_rutina; ?></span></span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">
                    Asignación oficial · Titulares (<span class="tabular-nums font-bold text-slate-700"><?php echo $numero_participantes; ?></span>) y Reservas (<span class="tabular-nums font-bold text-slate-700"><?php echo $numero_reservas; ?></span>)
                </p>
            </div>
            
            <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                <button onclick="saveAllParticipants()" class="flex-1 sm:flex-initial justify-center px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-black uppercase text-[11px] sm:text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    <i class="fas fa-save text-xs"></i> <span>Asignar Todo</span>
                </button>
                <a href="<?php echo $volver_url; ?>" class="px-4 sm:px-5 py-2.5 sm:py-3 bg-white border border-slate-200 text-slate-600 font-bold uppercase text-[11px] sm:text-xs tracking-wider rounded-xl sm:rounded-2xl shadow-xs hover:bg-slate-50 active:scale-[0.98] transition-all flex items-center gap-2">
                    <i class="fas fa-chevron-left text-xs"></i> <span>Volver</span>
                </a>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <!-- Mensaje de Estado Reactivo -->
        <div id="statusMessage" class="mb-4 sm:mb-6 p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-blue-50 border border-blue-100 flex items-center gap-3 text-blue-700 text-xs sm:text-sm font-medium transition-all shadow-xs">
            <i class="fas fa-info-circle text-base shrink-0"></i>
            <span id="statusText">Selecciona las nadadoras para los puestos disponibles y pulsa guardar o "Asignar Todo".</span>
        </div>

        <!-- BLOQUE TITULARES -->
        <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200/80 overflow-hidden mb-6 sm:mb-10 border-t-4 border-t-blue-500">
            <div class="p-4 sm:p-6 md:p-8 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-base sm:text-lg font-black text-slate-800 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs sm:text-sm shadow-inner"><i class="fas fa-id-badge"></i></span>
                    <span>Titulares</span>
                </h3>
                <span class="px-3 py-1 bg-blue-100/70 text-blue-700 text-[10px] sm:text-xs font-black rounded-full uppercase tracking-wider tabular-nums">
                    <?php echo $numero_participantes; ?> plazas
                </span>
            </div>
            
            <div class="p-3 sm:p-6 md:p-8 space-y-3 sm:space-y-4" id="titulares-container">
                <?php for ($x=0; $x<$numero_participantes; $x++): 
                    $query_p = "SELECT * FROM rutinas_participantes WHERE id_rutina=$id_rutina AND reserva='no' LIMIT $x,1";
                    $participante = mysqli_fetch_assoc(mysqli_query($connection, $query_p));
                    $id_nadadora = $participante['id_nadadora'] ?? 0;
                    $id_registro = $participante['id'] ?? 0;
                ?>
                    <form action="rutinas_participantes_code.php" method="POST" onsubmit="return validateIndividual(this)" class="participant-form p-3 sm:p-4 bg-slate-50/60 rounded-xl sm:rounded-2xl border border-slate-200/70 hover:border-blue-300 transition-all flex flex-col md:grid md:grid-cols-12 gap-2.5 sm:gap-4 md:items-center">
                        <input type="hidden" name="id" value="<?php echo $id_registro; ?>">
                        <input type="hidden" name="reserva" value="no">

                        <!-- Header en Móvil / Columna 1 en Desktop -->
                        <div class="flex items-center justify-between md:col-span-3">
                            <span class="px-2.5 py-1 bg-blue-600 text-white text-[9px] sm:text-[10px] font-black uppercase rounded-lg tracking-wider shadow-xs">
                                TITULAR <?php echo $x+1; ?>
                            </span>

                            <!-- Acciones Mobile (< md) -->
                            <div class="flex md:hidden items-center gap-1.5">
                                <?php if ($id_nadadora > 0): ?>
                                    <button type="submit" name="update_btn" class="h-8 px-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200/80 flex items-center justify-center text-xs font-bold active:scale-[0.97] transition-all gap-1.5 shadow-xs" title="Actualizar">
                                        <i class="fas fa-save text-[11px]"></i> <span>Guardar</span>
                                    </button>
                                    <button type="submit" name="delete_btn" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 border border-red-200/80 flex items-center justify-center active:scale-[0.97] transition-all shadow-xs" title="Quitar">
                                        <i class="fas fa-trash-alt text-[11px]"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="save_btn" class="h-8 px-3 bg-blue-600 text-white font-black uppercase text-[10px] tracking-wider rounded-lg shadow-sm active:scale-[0.97] transition-all flex items-center gap-1">
                                        <i class="fas fa-plus text-[9px]"></i> <span>Asignar</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Selector de Nadadora -->
                        <div class="w-full md:col-span-7">
                            <?php 
                            $modo_rutina = true;
                            $id_categoria_rutina = intval($data['id_categoria'] ?? 0);
                            $temporada_año = $temporada_año;
                            ob_start();
                            include('./includes/nadadoras_select_option.php');
                            $select_html = ob_get_clean();
                            $clean_select = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_html);
                            $clean_select = preg_replace(
                                '/<select[^>]*>/i', 
                                '<select name="id_nadadora" class="select-nadadora tom-select-nadadora w-full" placeholder="Buscar por apellidos o nombre...">', 
                                $clean_select
                            );
                            $clean_select = preg_replace('/<option[^>]*value=[\'"]\s*[\'"][^>]*>.*?<\/option>/i', '<option value=""></option>', $clean_select);
                            echo $clean_select;
                            ?>
                        </div>

                        <!-- Acciones Desktop (>= md) -->
                        <div class="hidden md:flex md:col-span-2 justify-end gap-2">
                            <?php if ($id_nadadora > 0): ?>
                                <button type="submit" name="update_btn" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/60 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-xs active:scale-95" title="Actualizar">
                                    <i class="fas fa-save text-xs"></i>
                                </button>
                                <button type="submit" name="delete_btn" class="w-10 h-10 rounded-xl bg-red-50 text-red-600 border border-red-200/60 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all shadow-xs active:scale-95" title="Quitar">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            <?php else: ?>
                                <button type="submit" name="save_btn" class="w-full py-2.5 bg-blue-600 text-white font-black uppercase text-[10px] tracking-widest rounded-xl shadow-md hover:bg-blue-700 active:scale-95 transition-all">
                                    Asignar
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endfor; ?>
            </div>
        </div>

        <!-- BLOQUE RESERVAS -->
        <?php if($numero_reservas > 0): ?>
        <div class="bg-white rounded-2xl sm:rounded-[2.5rem] shadow-sm border border-slate-200/80 overflow-hidden border-t-4 border-t-amber-500">
            <div class="p-4 sm:p-6 md:p-8 border-b border-slate-100 bg-amber-50/30 flex items-center justify-between">
                <h3 class="text-base sm:text-lg font-black text-slate-800 flex items-center gap-2.5 sm:gap-3">
                    <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs sm:text-sm shadow-inner"><i class="fas fa-id-badge"></i></span>
                    <span>Reservas</span>
                </h3>
                <span class="px-3 py-1 bg-amber-100/70 text-amber-700 text-[10px] sm:text-xs font-black rounded-full uppercase tracking-wider tabular-nums">
                    <?php echo $numero_reservas; ?> plazas
                </span>
            </div>
            
            <div class="p-3 sm:p-6 md:p-8 space-y-3 sm:space-y-4" id="reservas-container">
                <?php for ($x=0; $x<$numero_reservas; $x++): 
                    $query_p = "SELECT * FROM rutinas_participantes WHERE id_rutina=$id_rutina AND reserva='si' LIMIT $x,1";
                    $participante = mysqli_fetch_assoc(mysqli_query($connection, $query_p));
                    $id_nadadora = $participante['id_nadadora'] ?? 0;
                    $id_registro = $participante['id'] ?? 0;
                ?>
                    <form action="rutinas_participantes_code.php" method="POST" onsubmit="return validateIndividual(this)" class="participant-form p-3 sm:p-4 bg-slate-50/60 rounded-xl sm:rounded-2xl border border-slate-200/70 hover:border-amber-300 transition-all flex flex-col md:grid md:grid-cols-12 gap-2.5 sm:gap-4 md:items-center">
                        <input type="hidden" name="id" value="<?php echo $id_registro; ?>">
                        <input type="hidden" name="reserva" value="si">

                        <!-- Header en Móvil / Columna 1 en Desktop -->
                        <div class="flex items-center justify-between md:col-span-3">
                            <span class="px-2.5 py-1 bg-amber-500 text-white text-[9px] sm:text-[10px] font-black uppercase rounded-lg tracking-wider shadow-xs">
                                RESERVA <?php echo $x+1; ?>
                            </span>

                            <!-- Acciones Mobile (< md) -->
                            <div class="flex md:hidden items-center gap-1.5">
                                <?php if ($id_nadadora > 0): ?>
                                    <button type="submit" name="update_btn" class="h-8 px-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200/80 flex items-center justify-center text-xs font-bold active:scale-[0.97] transition-all gap-1.5 shadow-xs" title="Actualizar">
                                        <i class="fas fa-save text-[11px]"></i> <span>Guardar</span>
                                    </button>
                                    <button type="submit" name="delete_btn" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 border border-red-200/80 flex items-center justify-center active:scale-[0.97] transition-all shadow-xs" title="Quitar">
                                        <i class="fas fa-trash-alt text-[11px]"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="save_btn" class="h-8 px-3 bg-amber-500 text-white font-black uppercase text-[10px] tracking-wider rounded-lg shadow-sm active:scale-[0.97] transition-all flex items-center gap-1">
                                        <i class="fas fa-plus text-[9px]"></i> <span>Asignar</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Selector de Nadadora -->
                        <div class="w-full md:col-span-7">
                            <?php 
                            $modo_rutina = true;
                            $id_categoria_rutina = intval($data['id_categoria'] ?? 0);
                            $temporada_año = $temporada_año;
                            ob_start();
                            include('./includes/nadadoras_select_option.php');
                            $select_html = ob_get_clean();
                            $clean_select = preg_replace('/<label.*?>.*?<\/label>/i', '', $select_html);
                            $clean_select = preg_replace(
                                '/<select[^>]*>/i', 
                                '<select name="id_nadadora" class="select-nadadora tom-select-nadadora w-full" placeholder="Buscar por apellidos o nombre...">', 
                                $clean_select
                            );
                            $clean_select = preg_replace('/<option[^>]*value=[\'"]\s*[\'"][^>]*>.*?<\/option>/i', '<option value=""></option>', $clean_select);
                            echo $clean_select;
                            ?>
                        </div>

                        <!-- Acciones Desktop (>= md) -->
                        <div class="hidden md:flex md:col-span-2 justify-end gap-2">
                            <?php if ($id_nadadora > 0): ?>
                                <button type="submit" name="update_btn" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/60 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-xs active:scale-95" title="Actualizar">
                                    <i class="fas fa-save text-xs"></i>
                                </button>
                                <button type="submit" name="delete_btn" class="w-10 h-10 rounded-xl bg-red-50 text-red-600 border border-red-200/60 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all shadow-xs active:scale-95" title="Quitar">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            <?php else: ?>
                                <button type="submit" name="save_btn" class="w-full py-2.5 bg-amber-500 text-white font-black uppercase text-[10px] tracking-widest rounded-xl shadow-md hover:bg-amber-600 active:scale-95 transition-all">
                                    Asignar
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</main>

<!-- Formulario Oculto para Guardado Masivo -->
<form id="bulkAssignForm" action="rutinas_participantes_code.php" method="POST" style="display:none;">
    <input type="hidden" name="bulk_save" value="1">
    <div id="bulkInputs"></div>
</form>

<script src="vendor/tom-select/tom-select.complete.min.js"></script>
<script>
const tsInstances = [];

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tom-select-nadadora').forEach(function(el) {
        if (typeof TomSelect !== 'undefined') {
            const ts = new TomSelect(el, {
                create: false,
                maxItems: 1,
                closeAfterSelect: true,
                allowEmptyOption: false,
                dropdownParent: 'body',
                placeholder: 'Buscar por apellidos o nombre...',
                render: {
                    no_results: function(data, escape) {
                        return '<div class="no-results p-3 text-xs text-slate-400 font-bold">No se encontraron nadadoras con ese nombre</div>';
                    }
                }
            });

            // Ocultar el texto de item o selección previa en cuanto se empieza a escribir
            ts.on('type', function(str) {
                if (str && str.trim().length > 0) {
                    ts.wrapper.classList.add('ts-typing');
                } else {
                    ts.wrapper.classList.remove('ts-typing');
                }
            });
            ts.on('blur', function() {
                ts.wrapper.classList.remove('ts-typing');
            });

            // Al elegir nadadora: cerrar popup, liberar foco y enfocar el botón guardar de la fila
            ts.on('change', function(value) {
                ts.wrapper.classList.remove('ts-typing');
                if (value && value.toString().trim() !== '' && value !== '0') {
                    ts.close();
                    ts.blur();
                    const form = el.closest('form');
                    if (form) {
                        const btn = form.querySelector('button[name="save_btn"]') || form.querySelector('button[name="update_btn"]');
                        if (btn) btn.focus();
                    }
                }
            });

            tsInstances.push(ts);
        }
    });
});

function updateStatus(type, message) {
    const container = document.getElementById('statusMessage');
    const text = document.getElementById('statusText');
    const icon = container.querySelector('i');

    container.className = 'mb-4 sm:mb-6 p-3.5 sm:p-4 rounded-xl sm:rounded-2xl flex items-center gap-3 font-medium transition-all border text-xs sm:text-sm shadow-xs';
    
    if (type === 'error') {
        container.classList.add('bg-red-50', 'border-red-200', 'text-red-700');
        icon.className = 'fas fa-exclamation-circle text-base shrink-0';
    } else if (type === 'success') {
        container.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-700');
        icon.className = 'fas fa-check-circle text-base shrink-0';
    } else {
        container.classList.add('bg-blue-50', 'border-blue-200', 'text-blue-700');
        icon.className = 'fas fa-info-circle text-base shrink-0';
    }
    text.textContent = message;
}

function validateIndividual(form) {
    const selects = document.querySelectorAll('.select-nadadora');
    const currentSelect = form.querySelector('.select-nadadora');
    const val = currentSelect.value.trim();
    
    if (val === '' || val === ' ' || val === '0') {
        updateStatus('error', 'Por favor, selecciona una nadadora.');
        return false;
    }

    let duplicateFound = false;
    const swimmerIds = [];

    // Reset visuales inicial
    document.querySelectorAll('.ts-control').forEach(ctrl => {
        ctrl.style.borderColor = '';
        ctrl.style.backgroundColor = '';
        ctrl.style.boxShadow = '';
    });

    selects.forEach((select) => {
        const sVal = select.value.trim();
        if (sVal !== '' && sVal !== ' ' && sVal !== '0') {
            if (swimmerIds.includes(sVal)) {
                duplicateFound = true;
                selects.forEach(s => {
                    if (s.value.trim() === sVal) {
                        const ctrl = s.closest('.participant-form')?.querySelector('.ts-control');
                        if (ctrl) {
                            ctrl.style.borderColor = '#ef4444';
                            ctrl.style.backgroundColor = '#fef2f2';
                            ctrl.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.4)';
                        }
                    }
                });
            } else {
                swimmerIds.push(sVal);
            }
        }
    });

    if (duplicateFound) {
        updateStatus('error', 'Error: Se han detectado nadadoras duplicadas. No se puede realizar la asignación.');
        if(typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Validación Fallida',
                text: 'Esta nadadora ya está seleccionada en otro puesto de la rutina.',
                confirmButtonColor: '#3b82f6',
                customClass: {
                    popup: 'rounded-[2rem]',
                    confirmButton: 'rounded-xl px-6 py-3 font-black uppercase text-xs'
                }
            });
        }
        return false;
    }

    const submitBtn = form.querySelector('button[type="submit"]:hover') || form.querySelector('button[type="submit"]:focus') || form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.classList.add('opacity-75', 'pointer-events-none');
    }

    return true;
}

function saveAllParticipants() {
    const selects = document.querySelectorAll('.select-nadadora');
    const bulkInputs = document.getElementById('bulkInputs');
    bulkInputs.innerHTML = ''; 
    
    let duplicateFound = false;
    const swimmerIds = [];
    let count = 0;

    // Reset visuales inicial
    document.querySelectorAll('.ts-control').forEach(ctrl => {
        ctrl.style.borderColor = '';
        ctrl.style.backgroundColor = '';
        ctrl.style.boxShadow = '';
    });

    selects.forEach((select, index) => {
        const val = select.value.trim();
        const form = select.closest('.participant-form');
        const idRegistro = form.querySelector('input[name="id"]').value;
        const reserva = form.querySelector('input[name="reserva"]').value;

        if (val !== '' && val !== ' ' && val !== '0') {
            if (swimmerIds.includes(val)) {
                duplicateFound = true;
                selects.forEach(s => {
                    if (s.value.trim() === val) {
                        const ctrl = s.closest('.participant-form')?.querySelector('.ts-control');
                        if (ctrl) {
                            ctrl.style.borderColor = '#ef4444';
                            ctrl.style.backgroundColor = '#fef2f2';
                            ctrl.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.4)';
                        }
                    }
                });
            } else {
                swimmerIds.push(val);
                count++;
                bulkInputs.innerHTML += `<input type="hidden" name="participants[${index}][id_nadadora]" value="${val}">`;
                bulkInputs.innerHTML += `<input type="hidden" name="participants[${index}][id_registro]" value="${idRegistro}">`;
                bulkInputs.innerHTML += `<input type="hidden" name="participants[${index}][reserva]" value="${reserva}">`;
            }
        }
    });

    if (duplicateFound) {
        updateStatus('error', 'Error: Se han detectado nadadoras duplicadas en la misma rutina.');
        if(typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Validación Fallida',
                text: 'Una nadadora no puede ser asignada varias veces. Revisa los campos marcados.',
                confirmButtonColor: '#3b82f6',
                customClass: {
                    popup: 'rounded-[2rem]',
                    confirmButton: 'rounded-xl px-6 py-3 font-black uppercase text-xs'
                }
            });
        }
        return;
    }

    if (count === 0) {
        updateStatus('info', 'No has seleccionado ninguna nadadora para asignar.');
        return;
    }

    updateStatus('success', `Procesando asignación de ${count} nadadoras...`);
    document.getElementById('bulkAssignForm').submit();
}
</script>

<?php 
include('includes/scripts.php');
include('includes/footer.php'); 
?>
