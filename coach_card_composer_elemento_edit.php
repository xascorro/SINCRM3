<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_rutina = (int)($_POST['id_rutina'] ?? ($_GET['id_rutina'] ?? 0));
$elemento  = (int)($_POST['edit_elemento'] ?? ($_GET['edit_elemento'] ?? ($_POST['elemento'] ?? 1)));
$id_fase   = (int)($_POST['id_fase'] ?? ($_GET['id_fase'] ?? 0));
$id_tipo_hibrido = (int)($_POST['id_tipo_hibrido'] ?? ($_GET['id_tipo_hibrido'] ?? 1));
$tipo_acro = $_POST['tipo_acro'] ?? ($_GET['tipo_acro'] ?? '');

// Si falta id_fase, obtenerlo de la rutina
if ($id_rutina > 0 && $id_fase == 0) {
    $q_fase_chk = mysqli_query($connection, "SELECT id_fase, id_competicion FROM rutinas WHERE id = '$id_rutina' LIMIT 1");
    if ($q_fase_chk && $r_f = mysqli_fetch_assoc($q_fase_chk)) {
        $id_fase = (int)$r_f['id_fase'];
        if (!isset($id_competicion) || empty($id_competicion)) {
            $id_competicion = (int)$r_f['id_competicion'];
        }
    }
}

if (!isset($id_competicion) || empty($id_competicion)) {
    $id_competicion = (int)($_SESSION['id_competicion_usuario'] ?? ($_SESSION['id_competicion_activa'] ?? 0));
}

// Obtener detalles de la rutina
$routine_data = null;
$nombres_atletas = '';
$max_elementos = 10;

if ($id_rutina > 0) {
    $q_rot = mysqli_query($connection, "SELECT r.*, f.elementos_coach_card, f.genero,
                                               m.nombre as nombre_modalidad, c.nombre as nombre_categoria,
                                               cl.nombre_corto as nombre_club
                                        FROM rutinas r
                                        JOIN fases f ON r.id_fase = f.id
                                        LEFT JOIN modalidades m ON f.id_modalidad = m.id
                                        LEFT JOIN categorias c ON f.id_categoria = c.id
                                        LEFT JOIN clubes cl ON r.id_club = cl.id
                                        WHERE r.id = '$id_rutina' LIMIT 1");
    if ($q_rot && mysqli_num_rows($q_rot) > 0) {
        $routine_data = mysqli_fetch_assoc($q_rot);
        $max_elementos = (int)($routine_data['elementos_coach_card'] ?? 10);

        // Nadadoras
        $q_sw = mysqli_query($connection, "SELECT concat(n.nombre, ' ', n.apellidos) as nom_completo, rp.reserva 
                                           FROM rutinas_participantes rp 
                                           JOIN nadadoras n ON rp.id_nadadora = n.id 
                                           WHERE rp.id_rutina = '$id_rutina' 
                                           ORDER BY rp.reserva ASC, n.apellidos ASC");
        $parts = [];
        while ($sw = mysqli_fetch_assoc($q_sw)) {
            $parts[] = $sw['nom_completo'] . ($sw['reserva'] === 'si' ? ' (R)' : '');
        }
        $nombres_atletas = implode(', ', $parts);
    }
}

// 1. Cargar Time Inicio y Fin
$q_ti = mysqli_query($connection, "SELECT texto FROM hibridos_rutina WHERE id_rutina = '$id_rutina' AND tipo = 'time_inicio' AND elemento = '$elemento' LIMIT 1");
$time_inicio = ($q_ti && $r = mysqli_fetch_assoc($q_ti)) ? trim($r['texto']) : '';

$q_tf = mysqli_query($connection, "SELECT texto FROM hibridos_rutina WHERE id_rutina = '$id_rutina' AND tipo = 'time_fin' AND elemento = '$elemento' LIMIT 1");
$time_fin = ($q_tf && $r = mysqli_fetch_assoc($q_tf)) ? trim($r['texto']) : '';

// 2. Cargar Part (Tipo de híbrido guardado si existe)
$q_part = mysqli_query($connection, "SELECT texto FROM hibridos_rutina WHERE id_rutina = '$id_rutina' AND tipo = 'part' AND texto != '3' AND elemento = '$elemento' LIMIT 1");
if ($q_part && $r = mysqli_fetch_assoc($q_part)) {
    if (!empty($r['texto'])) {
        $id_tipo_hibrido = (int)$r['texto'];
    }
}
$_POST['id_tipo_hibrido'] = $id_tipo_hibrido;

// 3. Cargar Basemark actual
$q_bm = mysqli_query($connection, "SELECT id_dificultad, texto, valor FROM hibridos_rutina WHERE id_rutina = '$id_rutina' AND tipo = 'basemark' AND elemento = '$elemento' LIMIT 1");
$bm_row = ($q_bm && $r = mysqli_fetch_assoc($q_bm)) ? $r : null;
$id_dificultad_bm = $bm_row['id_dificultad'] ?? '';
$texto_basemark = $bm_row['texto'] ?? '';

// 4. Cargar DDs existentes
$q_dds = mysqli_query($connection, "SELECT id_dificultad, texto, valor FROM hibridos_rutina WHERE id_rutina = '$id_rutina' AND tipo = 'dd' AND elemento = '$elemento' ORDER BY id ASC");
$existing_dds = [];
while ($d_row = mysqli_fetch_assoc($q_dds)) {
    $existing_dds[] = $d_row;
}

// 5. Cargar Total actual del elemento
$q_tot = mysqli_query($connection, "SELECT valor FROM hibridos_rutina WHERE id_rutina = '$id_rutina' AND tipo = 'total' AND elemento = '$elemento' LIMIT 1");
$elem_total = ($q_tot && $r = mysqli_fetch_assoc($q_tot)) ? (float)$r['valor'] : 0.0;

$back_url = "coach_card_composer.php?id_rutina={$id_rutina}&id_fase={$id_fase}";
?>

<style>
/* Estilos unificados para los selects dinámicos generados por PHP y AJAX */
select.form-control, .id_tipo_hibrido, .tipo_basemark {
    width: 100% !important;
    padding: 0.625rem 0.875rem !important;
    font-size: 0.75rem !important;
    font-weight: 700 !important;
    color: #1e293b !important;
    background-color: #f8fafc !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 0.75rem !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    cursor: pointer !important;
}
select.form-control:focus, .id_tipo_hibrido:focus, .tipo_basemark:focus {
    outline: none !important;
    background-color: #ffffff !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
}
select.form-control optgroup, .id_tipo_hibrido optgroup, .tipo_basemark optgroup {
    font-weight: 900 !important;
    color: #475569 !important;
    background-color: #f1f5f9 !important;
    padding: 4px 6px !important;
}
select.form-control option, .id_tipo_hibrido option, .tipo_basemark option {
    font-weight: 600 !important;
    color: #0f172a !important;
    background-color: #ffffff !important;
    padding: 6px 8px !important;
}
select:disabled {
    background-color: #f1f5f9 !important;
    color: #94a3b8 !important;
    cursor: not-allowed !important;
    opacity: 0.7 !important;
}
</style>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="px-3 py-4 sm:p-6 md:p-10 max-w-5xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header & Navegación -->
        <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">
                    <a href="<?php echo $back_url; ?>" class="hover:text-blue-600 transition-colors">Coach Card</a>
                    <span>/</span>
                    <span class="text-blue-600 font-black">Edición Técnica</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shadow-xs border border-blue-100">
                        <i class="fas fa-sliders-h"></i>
                    </span>
                    Editar Elemento #<?php echo $elemento; ?>
                </h1>
            </div>

            <!-- Botones Navegación Rápida Entre Elementos -->
            <div class="flex items-center gap-2">
                <?php if ($elemento > 1): ?>
                    <form action="coach_card_composer_elemento_edit.php" method="POST" class="inline">
                        <input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
                        <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
                        <input type="hidden" name="edit_elemento" value="<?php echo $elemento - 1; ?>">
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-black uppercase tracking-wider shadow-xs active:scale-[0.98] transition-all flex items-center gap-1.5" title="Elemento Anterior">
                            <i class="fas fa-chevron-left text-[10px]"></i> #<?php echo $elemento - 1; ?>
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($elemento < $max_elementos): ?>
                    <form action="coach_card_composer_elemento_edit.php" method="POST" class="inline">
                        <input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
                        <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
                        <input type="hidden" name="edit_elemento" value="<?php echo $elemento + 1; ?>">
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-black uppercase tracking-wider shadow-xs active:scale-[0.98] transition-all flex items-center gap-1.5" title="Elemento Siguiente">
                            #<?php echo $elemento + 1; ?> <i class="fas fa-chevron-right text-[10px]"></i>
                        </button>
                    </form>
                <?php endif; ?>

                <a href="<?php echo $back_url; ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-black uppercase tracking-wider active:scale-[0.98] transition-all flex items-center gap-1.5">
                    <i class="fas fa-times"></i> Salir
                </a>
            </div>
        </div>

        <!-- Banner de Contexto de la Rutina -->
        <?php if ($routine_data): ?>
            <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 p-4 sm:p-6 mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200 flex flex-col items-center justify-center shrink-0">
                        <span class="text-[8px] font-black uppercase text-slate-400">Rutina</span>
                        <span class="text-sm font-black text-slate-800 tabular-nums">#<?php echo $id_rutina; ?></span>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-1.5 mb-1">
                            <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-wider border border-blue-100">
                                <?php echo htmlspecialchars($routine_data['nombre_modalidad'] . (($routine_data['genero'] ?? '') === 'masculino' ? ' Masc' : '')); ?>
                            </span>
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-wider border border-slate-200">
                                <?php echo htmlspecialchars($routine_data['nombre_categoria']); ?>
                            </span>
                            <?php if (!empty($routine_data['nombre_club'])): ?>
                                <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-wider border border-emerald-100">
                                    <?php echo htmlspecialchars($routine_data['nombre_club']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs font-bold text-slate-700 truncate max-w-xl">
                            <?php echo !empty($nombres_atletas) ? htmlspecialchars($nombres_atletas) : 'Sin deportistas asignadas'; ?>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 self-end md:self-auto">
                    <div class="text-right">
                        <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider">DD Actual Elemento</p>
                        <p class="text-lg font-black text-blue-600 tabular-nums"><?php echo number_format($elem_total, 3); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulario Principal -->
        <form action="coach_card_composer_code.php" method="POST" id="formElemento" class="space-y-6">
            <input type="hidden" name="id_rutina" value="<?php echo $id_rutina; ?>">
            <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
            <input type="hidden" name="elemento" value="<?php echo $elemento; ?>">

            <!-- 1. Tiempos y Tipo de Híbrido -->
            <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 p-5 sm:p-7">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-[11px]"><i class="fas fa-clock"></i></span>
                    Parámetros Temporales & Categoría Técnica
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Time Inicio -->
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider mb-1.5 block">Tiempo Inicio (MM:SS)</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"><i class="fas fa-play"></i></span>
                            <input type="text" name="time_inicio" value="<?php echo htmlspecialchars($time_inicio); ?>" 
                                   placeholder="00:15" maxlength="5"
                                   class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-black text-slate-800 placeholder-slate-400 font-mono tabular-nums focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all">
                        </div>
                    </div>

                    <!-- Time Fin -->
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider mb-1.5 block">Tiempo Fin (MM:SS)</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"><i class="fas fa-stop"></i></span>
                            <input type="text" name="time_fin" value="<?php echo htmlspecialchars($time_fin); ?>" 
                                   placeholder="00:30" maxlength="5"
                                   class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-black text-slate-800 placeholder-slate-400 font-mono tabular-nums focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 transition-all">
                        </div>
                    </div>

                    <!-- Tipo Híbrido -->
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider mb-1.5 block">Tipo de Híbrido (Part)</label>
                        <?php 
                        // Cargar selector de tipos de híbridos
                        ob_start();
                        include('includes/tipo_hibridos_select_option.php');
                        $tipo_h_html = ob_get_clean();
                        // Limpiar label legacy si existe
                        echo preg_replace('/<label.*?>.*?<\/label>/i', '', $tipo_h_html);
                        ?>
                    </div>
                </div>
            </div>

            <!-- 2. Basemark -->
            <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 p-5 sm:p-7">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-[11px]"><i class="fas fa-layer-group"></i></span>
                    Nivel Base (Basemark)
                </h3>

                <div class="max-w-xl">
                    <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider mb-1.5 block">Selección de Basemark Oficial</label>
                    <?php
                    $tipo_elemento = 'Basemark';
                    $id_dificultad = $id_dificultad_bm;
                    $texto = $texto_basemark;
                    $x = 0;
                    ob_start();
                    include('includes/dificultad_hibridos_select_option.php');
                    $bm_html = ob_get_clean();
                    echo preg_replace('/<label.*?>.*?<\/label>/i', '', $bm_html);
                    ?>
                    <p class="text-[10px] text-slate-400 font-medium mt-2">Define el valor mínimo de dificultad garantizado si no se completa la dificultad declarada.</p>
                </div>
            </div>

            <!-- 3. Declaración de Dificultad (DD) - 15 Slots Oficiales -->
            <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-slate-200/80 p-5 sm:p-7">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-100">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-[11px]"><i class="fas fa-cubes"></i></span>
                        Componentes de Dificultad Declarada (DD)
                    </h3>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">15 Ranuras Máximas World Aquatics</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <?php
                    $tipo_elemento = 'dd';
                    $dd_max = 15;
                    for ($x = 0; $x < $dd_max; $x++):
                        $id_dificultad = $existing_dds[$x]['id_dificultad'] ?? '';
                        $texto = $existing_dds[$x]['texto'] ?? '';
                        $valor = (float)($existing_dds[$x]['valor'] ?? 0);
                    ?>
                        <div class="p-3 rounded-2xl bg-slate-50/70 border border-slate-200/70 hover:border-blue-200 transition-colors">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">Movimiento #<?php echo $x + 1; ?></span>
                                <?php if ($valor > 0): ?>
                                    <span class="px-1.5 py-0.5 rounded bg-blue-100/70 text-blue-700 text-[9px] font-black tabular-nums">+<?php echo number_format($valor, 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php
                            ob_start();
                            include('includes/dificultad_hibridos_select_option.php');
                            $dd_html = ob_get_clean();
                            echo preg_replace('/<label.*?>.*?<\/label>/i', '', $dd_html);
                            ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- 4. Barra Inferior de Guardado -->
            <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md p-4 sm:p-5 rounded-2xl sm:rounded-3xl shadow-xl border border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500 font-medium text-center sm:text-left">
                    Los cambios recalcularán automáticamente el sumatorio de <span class="font-black text-slate-800">DD Total</span> de la rutina.
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="<?php echo $back_url; ?>" class="flex-1 sm:flex-initial text-center px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-black uppercase tracking-wider active:scale-[0.98] transition-all">
                        Cancelar
                    </a>
                    <button type="submit" name="update_btn" class="flex-1 sm:flex-initial px-8 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black uppercase tracking-wider shadow-lg shadow-blue-500/25 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> Guardar Elemento
                    </button>
                </div>
            </div>

        </form>

    </div>
</main>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>

<script>
$(document).ready(function() {
    // Cuando cambia el tipo de híbrido
    $('#id_tipo_hibrido').on('change', function() {
        var val = $(this).val();
        
        // Recargar selectores de DD y Basemark mediante AJAX
        $(".id_tipo_hibrido").load("./includes/dificultad_hibridos_select_option.php?tipo_elemento=dd&id_tipo_hibrido=" + val);
        $(".tipo_basemark").load("./includes/dificultad_hibridos_select_option.php?tipo_basemark=" + val);
        
        if (val == 1 || val == 2) {
            $(".tipo_basemark").prop("disabled", false);
            $(".id_tipo_hibrido").prop("disabled", false);
        } else if (val == 4) {
            // Acrobacias: los slots de DD se habilitan tras seleccionar el grupo en Basemark
            $(".tipo_basemark").prop("disabled", false);
            $(".id_tipo_hibrido").prop("disabled", true);
        }
    });

    // Cuando cambia el Basemark (especialmente para Acrobacias)
    $(document).on('change', '#Basemark0', function() {
        var val = $(this).val();
        if ($('#id_tipo_hibrido').val() == 4) {
            $(".id_tipo_hibrido").prop("disabled", false);
            $(".id_tipo_hibrido").load("./includes/dificultad_hibridos_select_option.php?tipo_elemento=dd&tipo_acro=" + val);
        }
    });

    // Auto-formato sencillo para timecodes (MM:SS)
    $('input[name="time_inicio"], input[name="time_fin"]').on('input', function() {
        var val = $(this).val().replace(/[^0-9:]/g, '');
        if (val.length === 2 && !val.includes(':')) {
            val += ':';
        }
        $(this).val(val);
    });
});
</script>
