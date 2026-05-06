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
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-gavel text-lg"></i></span>
                    Paneles y Jueces
                </h1>
                <p class="text-slate-500 font-medium">Configuración de la dirección y paneles técnicos de la competición.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button onclick="togglePanel('addJuezPanel')" class="px-6 py-3 bg-blue-600 text-white font-black uppercase text-xs tracking-[0.2em] rounded-2xl shadow-lg shadow-blue-500/20 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-user-plus text-xs"></i> Añadir Juez
                </button>
                <button onclick="togglePanel('addPanelPanel')" class="px-6 py-3 bg-slate-800 text-white font-black uppercase text-xs tracking-[0.2em] rounded-2xl shadow-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <i class="fas fa-columns text-xs"></i> Nuevo Panel
                </button>
                <a href="#" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-black uppercase text-xs tracking-[0.2em] rounded-2xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i class="fas fa-download text-xs"></i> PDF
                </a>
            </div>
        </div>

        <!-- Alertas de Sesión -->
        <?php if(isset($_SESSION['correcto'])): ?>
            <div class="mb-8 p-4 bg-white border-l-4 border-green-500 text-slate-700 rounded-r-2xl shadow-sm flex items-center gap-4 animate-fade-in">
                <i class="fas fa-check-circle text-green-500"></i>
                <span class="text-sm font-bold"><?php echo $_SESSION['correcto']; unset($_SESSION['correcto']); ?></span>
            </div>
        <?php endif; ?>
        <?php if(isset($_SESSION['estado'])): ?>
            <div class="mb-8 p-4 bg-white border-l-4 border-red-500 text-slate-700 rounded-r-2xl shadow-sm flex items-center gap-4 animate-fade-in">
                <i class="fas fa-exclamation-triangle text-red-500"></i>
                <span class="text-sm font-bold"><?php echo $_SESSION['estado']; unset($_SESSION['estado']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Formulario Añadir Juez (Colapsable) -->
        <div id="addJuezPanel" class="hidden mb-10 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-blue-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                    <i class="fas fa-id-card text-blue-600"></i> Asignar Juez a Dirección
                </h2>
                <form action="paneles_jueces_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Puesto en Competición</label>
                        <?php 
                        ob_start();
                        include('./includes/puestos_select_option.php');
                        $select = ob_get_clean();
                        $select = preg_replace('/<label.*?>.*?<\/label>/i', '', $select);
                        echo str_replace('class="form-control"', 'class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold appearance-none"', $select);
                        ?>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Seleccionar Juez</label>
                        <?php 
                        ob_start();
                        include('./includes/juez_select_option.php');
                        $select = ob_get_clean();
                        echo str_replace('class="form-control"', 'class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 transition-all text-sm font-bold appearance-none"', $select);
                        ?>
                    </div>
                    <div class="flex items-end pb-1">
                        <button type="submit" name="save_btn" class="w-full py-3.5 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-blue-700 transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-plus"></i> Añadir a Dirección
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Formulario Añadir Panel (Colapsable) -->
        <div id="addPanelPanel" class="hidden mb-10 animate-fade-in-down">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-slate-800/10 relative overflow-hidden">
                <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                    <i class="fas fa-columns text-slate-800"></i> Definir Nuevo Panel Técnico
                </h2>
                <form action="paneles_jueces_code.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Nombre del Panel</label>
                            <input type="text" name="nombre" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-slate-800 transition-all text-sm font-bold" placeholder="Ej: Panel 1 - Elementos">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 px-1">Tipo de Panel</label>
                            <?php 
                            ob_start();
                            include("./includes/paneles_tipo_select_option.php");
                            $select = ob_get_clean();
                            $select = preg_replace('/<label.*?>.*?<\/label>/i', '', $select);
                            echo str_replace('class="form-control"', 'class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-slate-800 transition-all text-sm font-bold appearance-none"', $select);
                            ?>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1">Jueces</label>
                                <input type="number" name="numero_jueces" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-slate-800 transition-all text-sm font-bold">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1">% Nota</label>
                                <input type="number" name="peso" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-slate-800 transition-all text-sm font-bold">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-400 px-1">Color</label>
                                <input type="color" name="color" value="#3b82f6" class="w-full h-[46px] rounded-2xl bg-slate-50 border border-slate-100 cursor-pointer">
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 px-1">Descripción / Notas</label>
                        <input type="text" name="descripcion" class="w-full px-5 py-3 rounded-2xl bg-slate-50 border border-slate-100 focus:border-slate-800 transition-all text-sm font-bold" placeholder="Opcional...">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" name="save_btn_panel" class="px-10 py-3.5 bg-slate-800 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-black transition-all">
                            Guardar Panel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-12">
            
            <!-- SECCIÓN: DIRECCIÓN DE LA COMPETICIÓN -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-lg font-black text-slate-800 uppercase tracking-tighter italic">Dirección</h2>
                </div>
                <div class="overflow-x-auto no-scrollbar flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                                <th class="px-8 py-4">Puesto</th>
                                <th class="px-4 py-4">Nombre</th>
                                <th class="px-4 py-4">Federación</th>
                                <th class="px-4 py-4 text-center">Acciones</th>
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
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-4">
                                    <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg text-[10px] font-black uppercase tracking-widest"><?php echo $row['nombre_puestos_juez']; ?></span>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-sm font-black text-slate-700 leading-tight"><?php echo $row['nombre'].' '.$row['apellidos']; ?></p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase italic"><?php echo $row['licencia']; ?></p>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-xs font-bold text-slate-500 italic"><?php echo $row['nombre_corto']; ?></span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <form action="paneles_jueces_edit.php" method="POST">
                                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="id_puestos_juez" value="<?php echo $row['id_puestos_juez']; ?>">
                                            <button type="submit" name="edit_btn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-emerald-500 hover:bg-emerald-50 transition-all"><i class="fas fa-edit text-xs"></i></button>
                                        </form>
                                        <form action="paneles_jueces_code.php" method="POST" onsubmit="return confirm('¿Eliminar juez de la dirección?');">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_btn" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all"><i class="fas fa-trash text-xs"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; } else { echo "<tr><td colspan='4' class='px-8 py-10 text-center text-slate-400 italic font-medium'>No hay jueces asignados a la dirección.</td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECCIÓN: PANELES TÉCNICOS -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-lg font-black text-slate-800 uppercase tracking-tighter italic">Paneles Técnicos</h2>
                </div>
                <div class="overflow-x-auto no-scrollbar flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
                                <th class="px-8 py-4">Panel</th>
                                <th class="px-4 py-4">Tipo</th>
                                <th class="px-4 py-4 text-center">Info</th>
                                <th class="px-4 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php
                            $query = "SELECT paneles.color, paneles.nombre, paneles.id, paneles.peso, paneles.descripcion, paneles.numero_jueces, paneles_tipo.nombre as tipo_panel, paneles_tipo.id as id_tipo 
                                      from paneles, paneles_tipo 
                                      where paneles.id_paneles_tipo = paneles_tipo.id and id_competicion = '".$id_competicion."'";
                            $query_run = mysqli_query($connection,$query);
                            if(mysqli_num_rows($query_run) > 0){
                                while ($row = mysqli_fetch_assoc($query_run)):
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: <?php echo $row['color']; ?>;"></div>
                                        <div>
                                            <p class="text-sm font-black text-slate-700 leading-tight"><?php echo $row['nombre']; ?></p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase italic truncate max-w-[120px]"><?php echo $row['descripcion']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-tighter"><?php echo $row['tipo_panel']; ?></span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-black text-slate-700"><?php echo $row['numero_jueces']; ?> Jueces</span>
                                        <span class="text-[10px] font-black text-blue-500 italic"><?php echo $row['peso']; ?>% Nota</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <form action="paneles_jueces_edit.php" method="POST">
                                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="id_paneles_tipo" value="<?php echo $row['id_tipo']; ?>">
                                            <button type="submit" name="edit_btn_panel" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-emerald-500 hover:bg-emerald-50 transition-all"><i class="fas fa-edit text-xs"></i></button>
                                        </form>
                                        <form action="paneles_jueces_code.php" method="POST" onsubmit="return confirm('¿Eliminar panel?');">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_btn_panel" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all"><i class="fas fa-trash text-xs"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; } else { echo "<tr><td colspan='4' class='px-8 py-10 text-center text-slate-400 italic font-medium'>No hay paneles definidos.</td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECCIÓN: COMPOSICIÓN DE PANELES POR FASE -->
        <div class="space-y-10">
            <div class="flex items-center gap-4 border-l-[8px] border-emerald-500 pl-6 py-2">
                <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tighter italic">Composición de Paneles</h2>
            </div>

            <?php
            if($_SESSION['figuras'] == 'si'){
                $query = "SELECT fases.id as id, id_categoria, categorias.nombre as nombre_categoria, edad_minima, edad_maxima, id_figura, figuras.nombre as nombre_figura, numero, fases.orden 
                          FROM fases, categorias, figuras 
                          WHERE fases.id_categoria = categorias.id and fases.id_figura = figuras.id and fases.id_competicion = ".$id_competicion." 
                          ORDER BY fases.orden, fases.id";
            } else {
                $query = "SELECT fases.id as id, fases.elementos_coach_card, id_categoria, categorias.nombre as nombre_categoria, id_modalidad, modalidades.nombre as nombre, fases.orden 
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
                    
                    // ORIGEN DESCRIPTIVO PARA CLONAR (Categoría - #Num Figura / Rutina)
                    $fase_label_origin = $categoria_fase . " - " . (($_SESSION['figuras'] == 'si') ? "#".$row['numero']." " : "") . $nombre_fase;
            ?>
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden animate-fade-in">
                <div class="px-10 py-8 oceanic-gradient flex flex-col md:flex-row md:items-center justify-between gap-4 relative overflow-hidden">
                    <!-- Decoración de fondo -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl pointer-events-none"></div>
                    
                    <div class="relative z-10">
                        <h3 class="text-2xl font-black italic tracking-tighter uppercase text-white drop-shadow-sm"><?php echo $fase_label_origin; ?></h3>
                        <div class="flex items-center gap-3 mt-1">
                            <span class="px-3 py-0.5 bg-black/20 backdrop-blur-md rounded-full text-[10px] font-black text-white/90 uppercase tracking-widest border border-white/10">Fase ID #<?php echo $id_fase; ?></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-secondary-container animate-pulse"></span>
                        </div>
                    </div>
                </div>

                <div class="p-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <?php
                    // Filtro de paneles por tipo de competición (obsoleto/puntua)
                    $cond_paneles = ($_SESSION['figuras'] == 'si' || (isset($row['elementos_coach_card']) && $row['elementos_coach_card'] > 0)) 
                                    ? "obsoleto like 'no'" 
                                    : "obsoleto like 'si' and puntua like 'si'";

                    $query_p = "SELECT paneles.id, numero_jueces, paneles.nombre, paneles.color, paneles_tipo.nombre as panel_tipo 
                                from paneles, paneles_tipo 
                                where id_paneles_tipo=paneles_tipo.id and $cond_paneles and id_competicion = '".$id_competicion."'";
                    $query_run_p = mysqli_query($connection,$query_p);

                    while ($p = mysqli_fetch_assoc($query_run_p)):
                        $id_panel = $p['id'];
                    ?>
                    <div class="bg-slate-50 rounded-[2rem] p-6 border border-slate-100 flex flex-col shadow-inner">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: <?php echo $p['color']; ?>;"></div>
                                <h4 class="text-sm font-black text-slate-800 uppercase italic"><?php echo $p['nombre']; ?></h4>
                            </div>
                            <button type="button" 
                                    onclick="openCloneModal(<?php echo $id_panel; ?>, <?php echo $id_fase; ?>, '<?php echo $p['nombre']; ?>', '<?php echo $fase_label_origin; ?>')" 
                                    class="text-[9px] font-black uppercase text-blue-600 hover:text-blue-800 transition-colors flex items-center gap-1">
                                <i class="fas fa-copy"></i> Clonar
                            </button>
                        </div>

                        <form action="paneles_jueces_code.php" method="POST" class="space-y-4">
                            <input type="hidden" name="id_panel" value="<?php echo $id_panel; ?>">
                            <input type="hidden" name="id_fase" value="<?php echo $id_fase; ?>">
                            
                            <div class="space-y-3">
                                <?php
                                for($x=1; $x <= $p['numero_jueces']; $x++):
                                    $q_pj = "SELECT id, id_juez from panel_jueces WHERE id_panel = $id_panel and numero_juez = $x and id_fase = $id_fase";
                                    $res_pj = mysqli_query($connection, $q_pj);
                                    $pj_data = mysqli_fetch_assoc($res_pj);
                                    $id_registro = $pj_data['id'] ?? '';
                                    $id_juez_actual = $pj_data['id_juez'] ?? 0;
                                ?>
                                <div class="flex items-center gap-2 group">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-white border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-400 group-focus-within:bg-blue-600 group-focus-within:text-white group-focus-within:border-blue-600 transition-all">
                                        <?php echo $x; ?>
                                    </div>
                                    <div class="flex-1 relative">
                                        <input type="hidden" name="bulk_num[]" value="<?php echo $x; ?>">
                                        <input type="hidden" name="bulk_id[]" value="<?php echo $id_registro; ?>">
                                        <?php 
                                        $_POST['id_juez'] = $id_juez_actual;
                                        ob_start();
                                        include('./includes/juez_select_option.php');
                                        $select_j = ob_get_clean();
                                        echo str_replace(['class="form-control"', "name='id_juez'"], ['class="w-full pl-3 pr-8 py-2 rounded-xl bg-white border border-slate-200 focus:border-blue-500 text-xs font-bold appearance-none"', "name='bulk_id_juez[]'"], $select_j);
                                        ?>
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none opacity-30">
                                            <i class="fas fa-chevron-down text-[10px]"></i>
                                        </div>
                                    </div>
                                    <button type="submit" name="panel_jueces_bulk_save_btn" class="w-8 h-8 rounded-xl bg-white text-slate-300 hover:text-emerald-500 border border-slate-100 hover:border-emerald-100 transition-all flex items-center justify-center shadow-sm" title="Guardar este juez">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </button>
                                </div>
                                <?php endfor; ?>
                            </div>

                            <div class="pt-4 mt-2 border-t border-slate-200/60">
                                <button type="submit" name="panel_jueces_bulk_save_btn" class="w-full py-3 bg-slate-800 text-white font-black uppercase text-[10px] tracking-widest rounded-2xl shadow-lg hover:bg-black transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-save"></i> Guardar Todo el Panel
                                </button>
                            </div>
                        </form>
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

<!-- MODAL CLONAR PANEL (Tailwind v3) -->
<div id="cloneModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="toggleCloneModal()"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden animate-fade-in-up">
            <div class="px-10 pt-10 pb-6 bg-slate-50 border-b border-slate-100">
                <h2 class="text-2xl font-black text-slate-800 tracking-tighter uppercase italic mb-1">Clonar Composición</h2>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Panel: <span id="clone_panel_title" class="text-blue-600"></span></p>
                <p class="text-[10px] font-black text-slate-300 uppercase mt-1">Origen: <span id="clone_fase_title"></span></p>
            </div>
            
            <form action="paneles_jueces_code.php" method="POST" class="p-10">
                <input type="hidden" name="id_panel" id="clone_id_panel">
                <input type="hidden" name="source_fase" id="clone_source_fase">
                
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Selecciona las fases de destino:</p>
                
                <div class="max-h-60 overflow-y-auto pr-4 space-y-3 custom-scrollbar mb-8">
                    <?php foreach($todas_las_fases as $fase_dest): 
                        $fase_label = ($_SESSION['figuras'] == 'si') ? "#".$fase_dest['numero']." ".$fase_dest['fig'] : $fase_dest['modali'];
                    ?>
                        <label class="flex items-center gap-4 p-4 rounded-2xl bg-white border border-slate-100 hover:border-blue-500 hover:bg-blue-50/30 transition-all cursor-pointer group phase-checkbox-item" data-id="<?php echo $fase_dest['id']; ?>">
                            <input type="checkbox" name="target_fases[]" value="<?php echo $fase_dest['id']; ?>" class="w-5 h-5 rounded-lg border-slate-300 text-blue-600 focus:ring-blue-500 transition-all">
                            <div>
                                <p class="text-sm font-black text-slate-700 leading-none group-hover:text-blue-700"><?php echo $fase_label; ?></p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase mt-1"><?php echo $fase_dest['cat']; ?></p>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="flex items-center justify-between gap-4 pt-6 border-t border-slate-100">
                    <button type="button" onclick="toggleCloneModal()" class="text-xs font-black uppercase text-slate-400 hover:text-slate-600 transition-colors">Cancelar</button>
                    <button type="submit" name="clone_panel_btn" class="px-10 py-4 bg-blue-600 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg shadow-blue-500/20 hover:bg-blue-700 hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                        <i class="fas fa-check"></i> Ejecutar Clonación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePanel(id) {
    const p = document.getElementById(id);
    p.classList.toggle('hidden');
}

function toggleCloneModal() {
    document.getElementById('cloneModal').classList.toggle('hidden');
}

function openCloneModal(id_panel, id_fase, panel_name, fase_name) {
    document.getElementById('clone_id_panel').value = id_panel;
    document.getElementById('clone_source_fase').value = id_fase;
    document.getElementById('clone_panel_title').innerText = panel_name;
    document.getElementById('clone_fase_title').innerText = fase_name;
    
    // Ocultar la fase de origen de la lista
    document.querySelectorAll('.phase-checkbox-item').forEach(item => {
        if(item.dataset.id == id_fase) item.classList.add('hidden');
        else item.classList.remove('hidden');
    });
    
    toggleCloneModal();
}
</script>

<?php 
include('includes/scripts.php'); 
include('includes/footer.php'); 
?>
