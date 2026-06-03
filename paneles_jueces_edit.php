<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página - max-w-6xl como en competiciones_edit -->
    <div class="p-6 md:p-10 max-w-6xl mx-auto w-full font-lexend">
        
        <!-- Header de Sección - Estilo competiciones_edit -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 font-lexend text-primary">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200">
                        <i class="fas fa-edit text-lg"></i>
                    </span>
                    Configuración de Panel
                </h1>
                <p class="text-slate-500 font-medium">Parámetros técnicos y asignación de jueces.</p>
            </div>
            <a href="paneles_jueces.php" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm italic">
                <i class="fas fa-arrow-left text-xs"></i> Volver
            </a>
        </div>

        <?php
        // CASO 1: Editar Vínculo de Juez en Dirección
        if(isset($_POST['edit_btn'])){
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            $query = "SELECT * FROM puesto_juez WHERE id = '$id'";
            $query_run = mysqli_query($connection, $query);
            
            if(mysqli_num_rows($query_run) > 0){
                foreach ($query_run as $row) {
                    $_POST['id_juez'] = $row['id_juez'];
                    $_POST['id_puestos_juez'] = $row['id_puestos_juez'];
                    ?>
                    <form action="paneles_jueces_code.php" method="POST" class="animate-fade-in space-y-10">
                        <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">

                        <!-- Tarjeta Estilo competiciones_edit (Bloque Dirección) -->
                        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[6px] border-t-blue-600 relative overflow-hidden">
                            <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                                <i class="fas fa-user-tie text-blue-600"></i> Datos de Dirección
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Puesto en Competición</label>
                                    <?php 
                                    ob_start();
                                    include('./includes/puestos_select_option.php');
                                    $select = ob_get_clean();
                                    $select = preg_replace('/<label.*?>.*?<\/label>/i', '', $select);
                                    echo str_replace(["class='form-control'", 'class="form-control"'], 'class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 shadow-inner appearance-none"', $select);
                                    ?>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Juez Asignado</label>
                                    <?php 
                                    ob_start();
                                    include('./includes/juez_select_option.php');
                                    $select = ob_get_clean();
                                    echo str_replace(["class='form-control'", 'class="form-control"'], 'class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold text-slate-700 shadow-inner appearance-none"', $select);
                                    ?>
                                </div>
                            </div>

                            <div class="pt-10 flex justify-end gap-4 border-t border-slate-50 mt-10">
                                <a href="paneles_jueces.php" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Cancelar</a>
                                <button type="submit" name="update_btn" class="px-12 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                                    <i class="fas fa-check-circle"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php
                }
            }
        }

        // CASO 2: Editar Panel Técnico
        if(isset($_POST['edit_btn_panel'])){
            $id = mysqli_real_escape_string($connection, $_POST['edit_id']);
            $query = "SELECT * FROM paneles WHERE id = '$id'";
            $query_run = mysqli_query($connection, $query);
            
            if(mysqli_num_rows($query_run) > 0){
                foreach ($query_run as $row) {
                    $_POST['id_paneles_tipo'] = $row['id_paneles_tipo'];
                    ?>
                    <form action="paneles_jueces_code.php" method="POST" class="animate-fade-in space-y-10">
                        <input type="hidden" name="edit_id" value="<?php echo $row['id']?>">

                        <!-- BLOQUE 1: IDENTIDAD Y CONFIGURACIÓN (Estilo competiciones_edit) -->
                        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[6px] border-t-purple-600 relative overflow-hidden">
                            <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                                <i class="fas fa-layer-group text-purple-600"></i> Identidad del Panel
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                                <div class="md:col-span-8 space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre del Panel</label>
                                    <input type="text" name="edit_nombre" value="<?php echo $row['nombre'];?>" required 
                                           class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-purple-500 transition-all text-sm font-bold text-slate-700 shadow-inner">
                                </div>
                                <div class="md:col-span-4 space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Tipo de Panel</label>
                                    <?php 
                                    ob_start();
                                    include('includes/paneles_tipo_select_option.php');
                                    $select = ob_get_clean();
                                    $select = preg_replace('/<label.*?>.*?<\/label>/i', '', $select);
                                    echo str_replace(["class='form-control'", 'class="form-control"'], 'class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-purple-500 transition-all text-sm font-bold text-slate-700 shadow-inner appearance-none"', $select);
                                    ?>
                                </div>

                                <div class="md:col-span-3 space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nº Jueces</label>
                                    <input type="number" name="edit_numero_jueces" value="<?php echo $row['numero_jueces'];?>" min="1" 
                                           class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-purple-500 transition-all text-sm font-bold text-center shadow-inner">
                                </div>
                                <div class="md:col-span-3 space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">% Peso Nota</label>
                                    <div class="relative">
                                        <input type="number" name="edit_peso" value="<?php echo $row['peso'];?>" min="0" max="100" 
                                               class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-purple-500 transition-all text-sm font-bold text-center shadow-inner">
                                        <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-slate-400 font-bold">%</div>
                                    </div>
                                </div>
                                <div class="md:col-span-6 space-y-2">
                                    <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Color Corporativo</label>
                                    <div class="flex gap-2">
                                        <input type="text" id="colorText" name="edit_color" value="<?php echo $row['color'];?>" 
                                               class="flex-1 px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 font-bold text-sm shadow-inner">
                                        <input type="color" value="<?php echo $row['color'];?>" 
                                               oninput="document.getElementById('colorText').value = this.value"
                                               class="w-14 h-[58px] rounded-2xl border-0 p-0 overflow-hidden cursor-pointer shadow-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BLOQUE 2: REGLAS Y PUNTUACIÓN (Estilo competiciones_edit) -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-blue-600">
                                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                                    <i class="fas fa-gavel text-blue-600"></i> Tipo de Puntuación <span class="text-blue-500 text-xs font-bold">(obsoleto: no/si)</span>
                                </h2>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
                                        <span class="text-sm font-bold text-slate-700 italic">AQUA (Reglamento Actual)</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" id="check_aqua" name="edit_puntuacion_aqua" value="si" <?php echo ($row['obsoleto'] == 'no') ? 'checked' : ''; ?> onchange="toggleExcluyente('aqua')" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        </label>
                                    </div>
                                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
                                        <span class="text-sm font-bold text-slate-700 italic">Sincronizada (Reglamento Anterior)</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" id="check_sincro" name="edit_puntuacion_sincro" value="si" <?php echo ($row['obsoleto'] == 'si') ? 'checked' : ''; ?> onchange="toggleExcluyente('sincro')" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-500"></div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-200 border-l-[6px] border-l-emerald-500">
                                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                                    <i class="fas fa-calculator text-emerald-600"></i> Contabilización <span class="text-emerald-500 text-xs font-bold">(puntua: si/no)</span>
                                </h2>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
                                        <span class="text-sm font-bold text-slate-700 italic">Puntúa (Suma nota)</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" id="check_puntua" name="edit_contabilizacion_puntua" value="si" <?php echo ($row['puntua'] == 'si') ? 'checked' : ''; ?> onchange="toggleExcluyente('puntua')" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                        </label>
                                    </div>
                                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
                                        <span class="text-sm font-bold text-slate-700 italic">DTC / Sincronización</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" id="check_dtc" name="edit_contabilizacion_dtc" value="si" <?php echo ($row['puntua'] == 'no') ? 'checked' : ''; ?> onchange="toggleExcluyente('dtc')" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BLOQUE 3: NOTAS -->
                        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[6px] border-t-amber-500">
                            <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                                <i class="fas fa-comment-dots text-amber-500"></i> Descripción y Notas
                            </h2>
                            <textarea name="edit_descripcion" rows="4" class="w-full px-6 py-5 rounded-3xl bg-slate-50 border border-slate-100 focus:border-amber-500 transition-all text-sm font-medium text-slate-600 shadow-inner" placeholder="Notas internas para el panel..."><?php echo $row['descripcion'];?></textarea>
                            
                            <!-- BOTONES FINALES -->
                            <div class="pt-10 flex justify-end gap-4 border-t border-slate-50 mt-10">
                                <a href="paneles_jueces.php" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Cancelar</a>
                                <button type="submit" name="update_btn_panel" class="px-12 py-4 bg-slate-800 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php
                }
            }
        }

        if(!isset($_POST['edit_btn']) && !isset($_POST['edit_btn_panel'])):
        ?>
            <div class="p-20 bg-white rounded-[3rem] border border-slate-100 text-center shadow-sm">
                <div class="w-24 h-24 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mx-auto mb-8">
                    <i class="fas fa-mouse-pointer text-4xl"></i>
                </div>
                <p class="text-slate-400 font-black italic uppercase tracking-widest text-base">Selecciona un elemento para editar</p>
                <div class="mt-8">
                    <a href="paneles_jueces.php" class="px-10 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-blue-700 transition-all">Volver al Listado</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function toggleExcluyente(tipo) {
    // Tipo de Puntuación
    const aqua = document.getElementById('check_aqua');
    const sincro = document.getElementById('check_sincro');
    
    // Contabilización
    const puntua = document.getElementById('check_puntua');
    const dtc = document.getElementById('check_dtc');
    
    if (tipo === 'aqua' && aqua.checked) {
        sincro.checked = false;
    } else if (tipo === 'sincro' && sincro.checked) {
        aqua.checked = false;
    }
    
    if (tipo === 'puntua' && puntua.checked) {
        dtc.checked = false;
    } else if (tipo === 'dtc' && dtc.checked) {
        puntua.checked = false;
    }
}
</script>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>
