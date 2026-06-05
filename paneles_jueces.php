<?php
include('security.php');
//control de acceso
$allowedRoles = ['Administrador','Secretario'];
if (!array_key_exists('rol', $_SESSION) || !in_array($_SESSION['rol'], $allowedRoles)) {
   header('Location: '.$_SESSION['startPage']);
   die;
}
include('includes/header.php');
include('includes/navbar.php');

$id_competicion = $_SESSION['id_competicion_activa'];

// Obtener todas las fases de esta competición para el modal de clonar
$todas_las_fases = [];
if($_SESSION['figuras'] == 'si'){
    $q_fases_all = "SELECT fs.id, c.nombre as cat, f.nombre as fig, f.numero FROM fases fs, categorias c, figuras f WHERE fs.id_categoria = c.id and fs.id_figura = f.id and fs.id_competicion = $id_competicion ORDER BY fs.orden";
} else {
    $q_fases_all = "SELECT fs.id, c.nombre as cat, m.nombre as modali FROM fases fs, categorias c, modalidades m WHERE fs.id_categoria = c.id and fs.id_modalidad = m.id and fs.id_competicion = $id_competicion ORDER BY fs.orden";
}
$res_fases_all = mysqli_query($connection, $q_fases_all);
while($f = mysqli_fetch_assoc($res_fases_all)) {
    $todas_las_fases[] = $f;
}
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <!-- Contenido de la Página -->
    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend">
        
        <!-- Header de Sección -->
        <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="space-y-2">
                <h1 class="text-5xl font-black text-slate-800 tracking-tighter italic text-primary flex items-center gap-4">
                    <span class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-gavel text-2xl"></i></span>
                    Paneles y Jueces
                </h1>
                <p class="text-lg text-slate-500 font-medium">Configuración de la dirección y paneles técnicos de la competición.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button onclick="togglePanel('addJuezPanel')" class="px-6 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-[0.2em] rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> Añadir Juez
                </button>
                <button onclick="togglePanel('addPanelPanel')" class="px-6 py-4 bg-slate-800 text-white font-black uppercase text-xs tracking-[0.2em] rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-layer-group"></i> Nuevo Panel
                </button>
            </div>
        </div>

        <!-- Alertas de Sesión -->
        <?php include('includes/alertas_v4.php'); ?>

        <!-- Formulario Añadir Juez (Estilo competiciones_edit) -->
        <div id="addJuezPanel" class="hidden mb-12 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[10px] border-t-blue-600 relative overflow-hidden">
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                    <i class="fas fa-id-card text-blue-600"></i> Asignar Juez a Dirección
                </h2>
                <form action="paneles_jueces_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-8 items-end">
                    <div class="md:col-span-5 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Puesto en Competición</label>
                        <div class="relative">
                            <?php 
                            ob_start();
                            include('./includes/puestos_select_option.php');
                            $select = ob_get_clean();
                            $select = preg_replace('/<label.*?>.*?<\/label>/i', '', $select);
                            $select = preg_replace('/class=["\'].*?["\']/', 'class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold appearance-none shadow-inner"', $select);
                            echo $select;
                            ?>
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-5 space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Seleccionar Juez</label>
                        <div class="relative">
                            <?php 
                            ob_start();
                            include('./includes/juez_select_option.php');
                            $select = ob_get_clean();
                            $select = preg_replace('/class=["\'].*?["\']/', 'class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 transition-all text-sm font-bold appearance-none shadow-inner"', $select);
                            echo $select;
                            ?>
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" name="save_btn" class="w-full py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all">
                            <i class="fas fa-plus"></i> Añadir
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Formulario Añadir Panel (Estilo competiciones_edit) -->
        <div id="addPanelPanel" class="hidden mb-12 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-200 border-t-[10px] border-t-purple-600 relative overflow-hidden">
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                    <i class="fas fa-layer-group text-purple-600"></i> Definir Nuevo Panel Técnico
                </h2>
                <form action="paneles_jueces_code.php" method="POST" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                        <div class="md:col-span-8 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nombre del Panel</label>
                            <input type="text" name="nombre" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-purple-500 transition-all text-sm font-bold text-slate-700 shadow-inner" placeholder="Ej: Panel 1 - Elementos">
                        </div>
                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Tipo de Panel</label>
                            <div class="relative">
                                <?php 
                                ob_start();
                                include("./includes/paneles_tipo_select_option.php");
                                $select = ob_get_clean();
                                $select = preg_replace('/<label.*?>.*?<\/label>/i', '', $select);
                                $select = preg_replace('/class=["\'].*?["\']/', 'class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-purple-500 transition-all text-sm font-bold appearance-none shadow-inner"', $select);
                                echo $select;
                                ?>
                                <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Nº Jueces</label>
                            <input type="number" name="numero_jueces" value="5" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-purple-500 transition-all text-sm font-bold text-center shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">% Peso Nota</label>
                            <div class="relative">
                                <input type="number" name="peso" value="100" required class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-purple-500 transition-all text-sm font-bold text-center shadow-inner">
                                <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-slate-400 font-bold">%</div>
                            </div>
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Color</label>
                            <div class="flex gap-2">
                                <input type="text" id="newColorText" name="color" value="#3b82f6" class="flex-1 px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 font-bold text-sm shadow-inner">
                                <input type="color" value="#3b82f6" oninput="document.getElementById('newColorText').value = this.value" class="w-14 h-[58px] rounded-2xl border-0 p-0 overflow-hidden cursor-pointer shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Tipo de Puntuación <span class="text-blue-500">(obsoleto: no/si)</span></label>
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
                                <span class="text-sm font-bold text-slate-700 italic">AQUA (Reglamento Actual)</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="add_check_aqua" name="add_puntuacion_aqua" value="si" checked onchange="toggleExcluyenteAdd('aqua')" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
                                <span class="text-sm font-bold text-slate-700 italic">Sincronizada (Reglamento Anterior)</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="add_check_sincro" name="add_puntuacion_sincro" value="si" onchange="toggleExcluyenteAdd('sincro')" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-slate-500"></div>
                                </label>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Contabilización <span class="text-emerald-500">(puntua: si/no)</span></label>
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
                                <span class="text-sm font-bold text-slate-700 italic">Puntúa (Suma nota)</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="add_check_puntua" name="add_contabilizacion_puntua" value="si" checked onchange="toggleExcluyenteAdd('puntua')" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
                                <span class="text-sm font-bold text-slate-700 italic">DTC / Sincronización</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="add_check_dtc" name="add_contabilizacion_dtc" value="si" onchange="toggleExcluyenteAdd('dtc')" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1 tracking-widest">Descripción / Notas internas</label>
                        <input type="text" name="descripcion" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-100 focus:border-purple-500 transition-all text-sm font-bold shadow-inner" placeholder="Opcional...">
                    </div>

                    <div class="flex justify-end gap-4 pt-8 border-t border-slate-50">
                        <button type="button" onclick="togglePanel('addPanelPanel')" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">Cerrar</button>
                        <button type="submit" name="save_btn_panel" class="px-12 py-4 bg-slate-800 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                            <i class="fas fa-save"></i> Crear Panel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="flex flex-col gap-10 mb-16">
            
            <!-- SECCIÓN: DIRECCIÓN -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[10px] border-t-blue-600 overflow-hidden flex flex-col transition-all">
                <div class="px-10 py-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tighter italic">Dirección de la Competición</h2>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-user-tie"></i></div>
                </div>
                <div class="overflow-x-auto no-scrollbar flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 text-[11px] font-black uppercase tracking-[0.2em]">
                                <th class="px-10 py-5">Puesto</th>
                                <th class="px-4 py-5">Nombre y Licencia</th>
                                <th class="px-4 py-5">Federación</th>
                                <th class="px-10 py-5 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php
                            $query = "select puesto_juez.id, jueces.nombre, jueces.apellidos, jueces.licencia, puestos_juez.id as id_puestos_juez, puestos_juez.nombre as nombre_puestos_juez, federaciones.nombre_corto 
                                      from puesto_juez, jueces, puestos_juez, federaciones 
                                      where puesto_juez.id_juez = jueces.id and puesto_juez.id_puestos_juez = puestos_juez.id and jueces.federacion = federaciones.id 
                                      and puesto_juez.id_competicion ='".$id_competicion."'";
                            $query_run = mysqli_query($connection,$query);
                            if(mysqli_num_rows($query_run) > 0){
                                while ($row = mysqli_fetch_assoc($query_run)):
                            ?>
                            <tr class="hover:bg-blue-50/20 transition-colors group">
                                <td class="px-10 py-6">
                                    <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg text-[10px] font-black uppercase tracking-widest border border-blue-100"><?php echo $row['nombre_puestos_juez']; ?></span>
                                </td>
                                <td class="px-4 py-6">
                                    <p class="text-sm font-black text-slate-700 leading-tight group-hover:text-blue-700 transition-colors uppercase tracking-tighter"><?php echo $row['nombre'].' '.$row['apellidos']; ?></p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase italic mt-1 leading-none tracking-widest"><?php echo $row['licencia']; ?></p>
                                </td>
                                <td class="px-4 py-6">
                                    <span class="text-xs font-bold text-slate-500 italic"><?php echo $row['nombre_corto']; ?></span>
                                </td>
                                <td class="px-10 py-6 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <form action="paneles_jueces_edit.php" method="POST">
                                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="id_puestos_juez" value="<?php echo $row['id_puestos_juez']; ?>">
                                            <button type="submit" name="edit_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-300 hover:text-blue-600 hover:bg-blue-50 transition-all border border-transparent hover:border-blue-100" title="Editar"><i class="fas fa-edit text-sm"></i></button>
                                        </form>
                                        <form action="paneles_jueces_code.php" method="POST" onsubmit="return confirm('¿Eliminar juez de la dirección?');">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_btn" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all border border-transparent hover:border-red-100" title="Eliminar"><i class="fas fa-trash text-sm"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; } else { echo "<tr><td colspan='4' class='px-10 py-16 text-center text-slate-400 italic font-bold uppercase tracking-widest opacity-50'>No hay jueces asignados</td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECCIÓN: PANELES TÉCNICOS -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 border-t-[10px] border-t-purple-600 overflow-hidden flex flex-col transition-all">
                <div class="px-10 py-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tighter italic">Paneles de Calificación</h2>
                    <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600"><i class="fas fa-layer-group"></i></div>
                </div>
                <div class="overflow-x-auto no-scrollbar flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 text-[11px] font-black uppercase tracking-[0.2em]">
                                <th class="px-10 py-5">Nombre del Panel</th>
                                <th class="px-4 py-5">Parámetros</th>
                                <th class="px-4 py-5 text-center">Config</th>
                                <th class="px-10 py-5 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php
                            $query = "SELECT paneles.color, paneles.nombre, paneles.id, paneles.peso, paneles.descripcion, paneles.numero_jueces, paneles_tipo.nombre as tipo_panel, paneles_tipo.id as id_tipo, paneles.obsoleto, paneles.puntua 
                                      from paneles, paneles_tipo 
                                      where paneles.id_paneles_tipo = paneles_tipo.id and id_competicion = '".$id_competicion."'";
                            $query_run = mysqli_query($connection,$query);
                            if(mysqli_num_rows($query_run) > 0){
                                while ($row = mysqli_fetch_assoc($query_run)):
                                    $is_aqua = ($row['obsoleto'] == 'no');
                                    $sum_nota = ($row['puntua'] == 'si');
                            ?>
                            <tr class="hover:bg-purple-50/20 transition-colors group">
                                <td class="px-10 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-4 h-4 rounded-full shadow-md border-2 border-white ring-1 ring-slate-100" style="background-color: <?php echo $row['color']; ?>;"></div>
                                        <div>
                                            <p class="text-sm font-black text-slate-700 leading-tight group-hover:text-purple-700 transition-colors uppercase tracking-tighter"><?php echo $row['nombre']; ?></p>
                                            <?php if($row['descripcion']): ?>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase italic mt-1 truncate max-w-[180px] leading-none tracking-widest"><?php echo $row['descripcion']; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-6">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded-lg w-fit"><?php echo $row['tipo_panel']; ?></span>
                                        <div class="flex gap-2">
                                            <?php if($is_aqua): ?>
                                                <span class="text-[8px] font-black text-blue-600 uppercase border border-blue-100 px-1.5 rounded bg-blue-50/50">AQUA</span>
                                            <?php else: ?>
                                                <span class="text-[8px] font-black text-slate-500 uppercase border border-slate-200 px-1.5 rounded bg-slate-100/50">SINCRO</span>
                                            <?php endif; ?>
                                            
                                            <?php if($sum_nota): ?>
                                                <span class="text-[8px] font-black text-emerald-600 uppercase border border-emerald-100 px-1.5 rounded bg-emerald-50/50">SUMA</span>
                                            <?php else: ?>
                                                <span class="text-[8px] font-black text-rose-500 uppercase border border-rose-100 px-1.5 rounded bg-rose-50/50">DTC</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-6 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-black text-slate-700 italic"><?php echo $row['numero_jueces']; ?> Jueces</span>
                                        <span class="text-[10px] font-black text-purple-500 tracking-widest"><?php echo $row['peso']; ?>% Nota</span>
                                    </div>
                                </td>
                                <td class="px-10 py-6 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <form action="paneles_jueces_edit.php" method="POST">
                                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="id_paneles_tipo" value="<?php echo $row['id_tipo']; ?>">
                                            <button type="submit" name="edit_btn_panel" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-300 hover:text-purple-600 hover:bg-purple-50 transition-all border border-transparent hover:border-purple-100" title="Editar"><i class="fas fa-edit text-sm"></i></button>
                                        </form>
                                        <button type="button" onclick="confirmDeletePanel('<?php echo $row['id']; ?>', '<?php echo $row['nombre']; ?>')" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all border border-transparent hover:border-red-100" title="Eliminar"><i class="fas fa-trash text-sm"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; } else { echo "<tr><td colspan='4' class='px-10 py-16 text-center text-slate-400 italic font-bold uppercase tracking-widest opacity-50'>No hay paneles definidos</td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECCIÓN: COMPOSICIÓN DE PANELES POR FASE -->
        <div class="space-y-20">
            <div class="flex items-center gap-6 mb-12">
                <div class="h-1 flex-1 bg-slate-200 rounded-full opacity-50"></div>
                <div class="flex flex-col items-center">
                    <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tighter italic">Configuración por Fases</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-1">Asignación de jueces a paneles técnicos</p>
                </div>
                <div class="h-1 flex-1 bg-slate-200 rounded-full opacity-50"></div>
            </div>

            <?php
            if($_SESSION['figuras'] == 'si'){
                $query = "SELECT fases.id as id, id_categoria, categorias.nombre as nombre_categoria, edad_minima, edad_maxima, id_figura, figuras.nombre as nombre_figura, numero, fases.orden, fases.obsoleto 
                          FROM fases, categorias, figuras 
                          WHERE fases.id_categoria = categorias.id and fases.id_figura = figuras.id and fases.id_competicion = ".$id_competicion." 
                          ORDER BY fases.orden, fases.id";
            } else {
                $query = "SELECT fases.id as id, fases.elementos_coach_card, id_categoria, categorias.nombre as nombre_categoria, id_modalidad, modalidades.nombre as nombre, fases.orden, fases.obsoleto 
                          FROM fases, categorias, modalidades 
                          WHERE fases.id_categoria = categorias.id and fases.id_modalidad = modalidades.id and fases.id_competicion = ".$id_competicion." 
                          ORDER BY orden, fases.id";
            }
            $query_run = mysqli_query($connection,$query);

            if(mysqli_num_rows($query_run) > 0):
                while ($row = mysqli_fetch_assoc($query_run)):
                    $nombre_fase = ($_SESSION['figuras'] == 'si') ? $row['nombre_figura'] : $row['nombre'];
                    $categoria_fase = $row['nombre_categoria'];
                    $id_fase = $row['id'];
                    $fase_label_origin = $categoria_fase . " - " . (($_SESSION['figuras'] == 'si') ? "#".$row['numero']." " : "") . $nombre_fase;
            ?>
            <div class="animate-fade-in group">
                <!-- Header de Fase Minimalista -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8 pb-4 border-b-2 border-slate-100 group-hover:border-blue-500/20 transition-all">
                    <div class="flex items-center gap-5">
                        <div class="w-2 h-14 rounded-full oceanic-gradient shadow-lg shadow-blue-500/10 transition-all group-hover:h-16"></div>
                        <div>
                            <h3 class="text-3xl font-black italic tracking-tighter uppercase text-slate-800 leading-none"><?php echo $fase_label_origin; ?></h3>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase tracking-widest border border-slate-200">Fase ID: #<?php echo $id_fase; ?></span>
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-sm animate-pulse"></div>
                                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest italic">Panel Disponible</span>
                                <?php if($row['obsoleto'] == 'si'): ?>
                                    <span class="px-2 py-0.5 bg-slate-200 text-slate-500 rounded text-[9px] font-black uppercase tracking-widest border border-slate-300">OBSOLETO</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[9px] font-black uppercase tracking-widest border border-blue-100">AQUA</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <span class="px-4 py-2 bg-white text-slate-400 text-[10px] font-black rounded-2xl border border-slate-100 shadow-sm uppercase tracking-widest italic"><?php echo $categoria_fase; ?></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <?php
                    $obsoleto_fase = $row['obsoleto'] ?? 'no';
                    $cond_paneles = "paneles.obsoleto = '$obsoleto_fase'";

                    $query_p = "SELECT paneles.id, numero_jueces, paneles.nombre, paneles.color, paneles_tipo.nombre as panel_tipo 
                                from paneles, paneles_tipo 
                                where id_paneles_tipo=paneles_tipo.id and $cond_paneles and id_competicion = '".$id_competicion."'";
                    $query_run_p = mysqli_query($connection,$query_p);

                    while ($p = mysqli_fetch_assoc($query_run_p)):
                        $id_panel = $p['id'];
                    ?>
                    <div class="bg-white rounded-[2.5rem] p-8 border border-slate-200 flex flex-col shadow-sm hover:shadow-xl transition-all border-t-[10px]" style="border-top-color: <?php echo $p['color']; ?>;">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-white shadow-lg" style="background-color: <?php echo $p['color']; ?>;"><i class="fas fa-users-cog text-sm"></i></div>
                                <div>
                                    <h4 class="text-lg font-black text-slate-800 uppercase italic tracking-tighter leading-none"><?php echo $p['nombre']; ?></h4>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic"><?php echo $p['panel_tipo']; ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        onclick="confirmEmptyPanel(<?php echo $id_panel; ?>, <?php echo $id_fase; ?>, '<?php echo addslashes($p['nombre']); ?>')" 
                                        class="w-10 h-10 bg-slate-50 text-slate-400 rounded-2xl hover:bg-red-500 hover:text-white transition-all flex items-center justify-center shadow-sm border border-slate-100 group/btn" title="Vaciar Jueces de este Panel">
                                    <i class="fas fa-eraser text-sm group-hover/btn:scale-110 transition-transform"></i>
                                </button>
                                <button type="button" 
                                        onclick="openCloneModal(<?php echo $id_panel; ?>, <?php echo $id_fase; ?>, '<?php echo addslashes($p['nombre']); ?>', '<?php echo addslashes($fase_label_origin); ?>')" 
                                        class="w-10 h-10 bg-slate-50 text-slate-400 rounded-2xl hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-slate-100 group/btn" title="Clonar Composición">
                                    <i class="fas fa-copy text-sm group-hover/btn:scale-110 transition-transform"></i>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-4 flex-1">
                            <div class="grid grid-cols-1 gap-2.5">
                                <?php
                                for($x=1; $x <= $p['numero_jueces']; $x++):
                                    $q_pj = "SELECT id, id_juez from panel_jueces WHERE id_panel = $id_panel and numero_juez = $x and id_fase = $id_fase";
                                    $res_pj = mysqli_query($connection, $q_pj);
                                    $pj_data = mysqli_fetch_assoc($res_pj);
                                    $id_registro = $pj_data['id'] ?? '';
                                    $id_juez_actual = $pj_data['id_juez'] ?? 0;
                                ?>
                                <form action="paneles_jueces_code.php" method="POST" class="flex items-center gap-3 group bg-slate-50/30 p-1.5 rounded-2xl border border-slate-100 focus-within:border-blue-400 focus-within:bg-white focus-within:shadow-md transition-all">
                                    <input type="hidden" name="id_panel" value="<?php echo $id_panel; ?>">
                                    <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
                                    <input type="hidden" name="bulk_num[]" value="<?php echo $x; ?>">
                                    <input type="hidden" name="bulk_id[]" value="<?php echo $id_registro; ?>">
                                    
                                    <div class="flex-shrink-0 w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-400 group-focus-within:bg-slate-800 group-focus-within:text-white group-focus-within:border-slate-800 transition-all shadow-sm">
                                        <?php echo $x; ?>
                                    </div>
                                    <div class="flex-1 relative">
                                        <?php 
                                        $_POST['id_juez'] = $id_juez_actual;
                                        ob_start();
                                        include('./includes/juez_select_option.php');
                                        $select_j = ob_get_clean();
                                        // Robust replacement: swap name and override ALL classes
                                        $select_j = str_replace("name='id_juez'", "name='bulk_id_juez[]'", $select_j);
                                        $select_j = preg_replace('/class=["\'].*?["\']/', 'class="w-full pl-4 pr-10 py-2.5 rounded-xl bg-white border border-slate-100 focus:border-blue-500 text-[11px] font-black appearance-none shadow-sm"', $select_j);
                                        echo $select_j;
                                        ?>
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300">
                                            <i class="fas fa-chevron-down text-[9px]"></i>
                                        </div>
                                    </div>
                                    <button type="submit" name="panel_jueces_bulk_save_btn" class="w-9 h-9 rounded-xl bg-white text-slate-200 hover:text-emerald-500 border border-slate-100 hover:border-emerald-200 transition-all flex items-center justify-center shadow-sm hover:shadow-md" title="Guardar este juez">
                                        <i class="fas fa-check text-xs"></i>
                                    </button>
                                </form>
                                <?php endfor; ?>
                            </div>

                            <form action="paneles_jueces_code.php" method="POST" class="pt-6 mt-4 border-t border-slate-100">
                                <input type="hidden" name="id_panel" value="<?php echo $id_panel; ?>">
                                <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
                                <?php
                                // Campos ocultos para el guardado masivo
                                for($x=1; $x <= $p['numero_jueces']; $x++):
                                    $q_pj = "SELECT id, id_juez from panel_jueces WHERE id_panel = $id_panel and numero_juez = $x and id_fase = $id_fase";
                                    $res_pj = mysqli_query($connection, $q_pj);
                                    $pj_data = mysqli_fetch_assoc($res_pj);
                                    $id_registro = $pj_data['id'] ?? '';
                                    $id_juez_actual = $pj_data['id_juez'] ?? 0;
                                    // Nota: Para el masivo real necesitaríamos que los selects estuvieran dentro, 
                                    // pero como el usuario quiere independencia, el botón grande actuará como un "Guardar todos los cambios realizados" 
                                    // si implementamos una lógica JS, o simplemente lo mantenemos para coherencia visual si el código ya lo maneja.
                                    // Por ahora, para cumplir estrictamente el 'individual save', cada fila es su propio form.
                                endfor;
                                ?>
                                <button type="button" onclick="saveFullPanel(this)" class="w-full py-4 bg-slate-900 text-white font-black uppercase text-[10px] tracking-[0.2em] rounded-2xl shadow-lg hover:bg-black hover:scale-[1.01] active:scale-95 transition-all flex items-center justify-center gap-3">
                                    <i class="fas fa-save"></i> Guardar Panel Completo
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endwhile; 
            else: ?>
                <div class="p-20 bg-white rounded-[3rem] border border-slate-100 text-center shadow-sm">
                    <div class="w-24 h-24 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mx-auto mb-8"><i class="fas fa-calendar-times text-4xl"></i></div>
                    <p class="text-slate-400 font-black italic uppercase tracking-widest text-base">No hay fases definidas para configurar paneles</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<!-- FORMULARIO OCULTO BORRADO -->
<form id="deletePanelForm" action="paneles_jueces_code.php" method="POST">
    <input type="hidden" name="delete_id" id="delete_panel_id_val">
    <input type="hidden" name="delete_btn_panel" value="1">
</form>

<!-- FORMULARIO OCULTO VACIAR PANEL -->
<form id="emptyPanelForm" action="paneles_jueces_code.php" method="POST">
    <input type="hidden" name="empty_id_panel" id="empty_id_panel_val">
    <input type="hidden" name="empty_id_fase" id="empty_id_fase_val">
    <input type="hidden" name="empty_panel_btn" value="1">
</form>

<!-- MODAL CLONAR PANEL -->
<div id="cloneModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="toggleCloneModal()"></div>
        <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden animate-fade-in-up border border-slate-200">
            <div class="px-8 py-8 bg-slate-50 border-b border-slate-100 relative">
                <h2 class="text-2xl font-black text-slate-800 tracking-tighter uppercase italic flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20"><i class="fas fa-copy text-sm"></i></span>
                    Clonar Panel
                </h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span id="clone_panel_title" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-[9px] font-black uppercase tracking-widest border border-blue-200"></span>
                    <span id="clone_fase_title" class="px-3 py-1 bg-slate-200 text-slate-500 rounded-lg text-[9px] font-black uppercase tracking-widest border border-slate-300 italic"></span>
                </div>
            </div>
            
            <form action="paneles_jueces_code.php" method="POST" class="p-8">
                <input type="hidden" name="id_panel" id="clone_id_panel">
                <input type="hidden" name="source_fase" id="clone_source_fase">
                
                <div class="flex items-center justify-between mb-4">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Selecciona las fases de destino:</p>
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" id="selectAllFases" onchange="toggleAllFases(this)" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition-all cursor-pointer">
                        <span class="text-[9px] font-black text-blue-600 uppercase tracking-widest group-hover:text-blue-800 transition-colors">Seleccionar Todo</span>
                    </label>
                </div>
                
                <div class="max-h-60 overflow-y-auto pr-2 space-y-2 mb-8 custom-scrollbar">
                    <?php foreach($todas_las_fases as $fase_dest): 
                        $fase_label = ($_SESSION['figuras'] == 'si') ? "#".$fase_dest['numero']." ".$fase_dest['fig'] : $fase_dest['modali'];
                    ?>
                        <label class="flex items-center gap-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-100 hover:border-blue-400 hover:bg-white transition-all cursor-pointer group phase-checkbox-item shadow-sm" data-id="<?php echo $fase_dest['id']; ?>">
                            <input type="checkbox" name="target_fases[]" value="<?php echo $fase_dest['id']; ?>" class="target-fase-cb w-5 h-5 rounded-lg border-slate-300 text-blue-600 focus:ring-blue-500 transition-all cursor-pointer" onchange="checkSelectAllStatus()">
                            <div class="flex-1">
                                <p class="text-sm font-black text-slate-700 leading-none group-hover:text-blue-600 transition-colors uppercase italic tracking-tighter"><?php echo $fase_label; ?></p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 tracking-widest"><?php echo $fase_dest['cat']; ?></p>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="flex items-center justify-between gap-4 pt-6 border-t border-slate-100">
                    <button type="button" onclick="toggleCloneModal()" class="text-[10px] font-black uppercase text-slate-400 hover:text-slate-600 transition-colors tracking-widest">Cerrar</button>
                    <button type="submit" name="clone_panel_btn" class="px-8 py-4 bg-slate-900 text-white font-black uppercase text-[10px] tracking-widest rounded-2xl shadow-lg hover:bg-black hover:scale-105 active:scale-95 transition-all flex items-center gap-3">
                        <i class="fas fa-check"></i> Ejecutar Clonación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmEmptyPanel(id_panel, id_fase, name) {
    Swal.fire({
        title: '¿Vaciar Panel?',
        html: `Se desasignarán todos los jueces del panel <b>${name}</b> en esta fase.<br><small class='text-slate-400'>Si ya hay puntuaciones emitidas, la acción se bloqueará por seguridad.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Sí, vaciar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('empty_id_panel_val').value = id_panel;
            document.getElementById('empty_id_fase_val').value = id_fase;
            document.getElementById('emptyPanelForm').submit();
        }
    });
}

function togglePanel(id) {
    const p = document.getElementById(id);
    p.classList.toggle('hidden');
    p.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function confirmDeletePanel(id, name) {
    Swal.fire({
        title: '¿Eliminar Panel?',
        html: `Vas a borrar el panel <b>${name}</b>.<br><small class='text-slate-400'>Esta acción es irreversible si no hay notas.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_panel_id_val').value = id;
            document.getElementById('deletePanelForm').submit();
        }
    });
}

function toggleCloneModal() {
    document.getElementById('cloneModal').classList.toggle('hidden');
}

function openCloneModal(id_panel, id_fase, panel_name, fase_name) {
    document.getElementById('clone_id_panel').value = id_panel;
    document.getElementById('clone_source_fase').value = id_fase;
    document.getElementById('clone_panel_title').innerText = panel_name;
    document.getElementById('clone_fase_title').innerText = fase_name;
    
    document.querySelectorAll('.phase-checkbox-item').forEach(item => {
        if(item.dataset.id == id_fase) item.classList.add('hidden');
        else item.classList.remove('hidden');
    });
    
    toggleCloneModal();
}

function toggleAllFases(masterCheckbox) {
    const checkboxes = document.querySelectorAll('.target-fase-cb');
    checkboxes.forEach(cb => {
        // Solo marcamos las que están visibles (no ocultas por ser la fase de origen)
        const parentLabel = cb.closest('label');
        if (!parentLabel.classList.contains('hidden')) {
            cb.checked = masterCheckbox.checked;
        }
    });
}

function checkSelectAllStatus() {
    const visibleCheckboxes = Array.from(document.querySelectorAll('.target-fase-cb')).filter(cb => {
        return !cb.closest('label').classList.contains('hidden');
    });
    
    const allChecked = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked);
    document.getElementById('selectAllFases').checked = allChecked;
}

function saveFullPanel(btn) {
    const container = btn.closest('.space-y-4');
    const forms = container.querySelectorAll('form');
    
    // Crear un formulario virtual para el envío masivo
    const masterForm = document.createElement('form');
    masterForm.method = 'POST';
    masterForm.action = 'paneles_jueces_code.php';
    
    // Añadir el botón de guardado masivo
    const saveBtn = document.createElement('input');
    saveBtn.type = 'hidden';
    saveBtn.name = 'panel_jueces_bulk_save_btn';
    saveBtn.value = '1';
    masterForm.appendChild(saveBtn);

    // Recoger datos de todos los formularios individuales del panel
    forms.forEach((f, index) => {
        const formData = new FormData(f);
        for (let [key, value] of formData.entries()) {
            // Solo añadimos los IDs de panel y fase una vez
            if (index > 0 && (key === 'id_panel' || key === 'id_fase')) continue;
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            masterForm.appendChild(input);
        }
    });

    document.body.appendChild(masterForm);
    masterForm.submit();
}

function toggleExcluyenteAdd(tipo) {
    const aqua = document.getElementById('add_check_aqua');
    const sincro = document.getElementById('add_check_sincro');
    const puntua = document.getElementById('add_check_puntua');
    const dtc = document.getElementById('add_check_dtc');
    
    if (tipo === 'aqua' && aqua.checked) sincro.checked = false;
    else if (tipo === 'sincro' && sincro.checked) aqua.checked = false;
    
    if (tipo === 'puntua' && puntua.checked) dtc.checked = false;
    else if (tipo === 'dtc' && dtc.checked) puntua.checked = false;
}
</script>

<?php 
include('includes/scripts.php'); 
include('includes/footer.php'); 
?>
