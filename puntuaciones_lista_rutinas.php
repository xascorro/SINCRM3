<?php
include('security.php');
include('includes/header.php');
include('includes/navbar.php');

$id_fase = isset($_GET['id_fase']) ? (int)$_GET['id_fase'] : (isset($_POST['id_fase']) ? (int)$_POST['id_fase'] : 0);
$id_comp = $_SESSION['id_competicion_usuario'] ?? 0;

if ($id_comp == 0 && $_SESSION['id_rol'] == 1 && isset($_SESSION['id_competicion_activa'])) {
    $id_comp = $_SESSION['id_competicion_activa'];
}

// Obtener info de la fase
$query_fase = "SELECT c.nombre as categoria, m.nombre as modalidad 
               FROM fases f 
               JOIN categorias c ON f.id_categoria = c.id 
               JOIN modalidades m ON f.id_modalidad = m.id 
               WHERE f.id = $id_fase";
$res_fase = mysqli_query($connection, $query_fase);
$info_fase = mysqli_fetch_assoc($res_fase);
$nombre_modalidad = $info_fase['modalidad'] ?? 'Desconocida';
$nombre_categoria = $info_fase['categoria'] ?? 'Desconocida';
?>

<!-- Contenedor Principal -->
<main class="flex-1 flex flex-col min-w-0 bg-surface">
    
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend text-primary">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2 animate-fade-in">
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100 shadow-sm">Modo Mesa Técnica</span>
                </div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-flag-checkered text-lg"></i></span>
                    Puntuar Rutinas
                </h1>
                <p class="text-slate-500 font-medium text-lg uppercase tracking-tight italic"><?php echo $nombre_modalidad." ".$nombre_categoria; ?></p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="./puntuaciones_fases_rutinas_puntuar_categorias.php?id_fase=<?php echo $id_fase; ?>" target="_blank" class="px-5 py-3 bg-white border border-slate-200 text-slate-600 font-black uppercase text-xs tracking-widest rounded-2xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                    <i class="fas fa-calculator text-blue-500"></i> Calcular Resultados
                </a>
                <a href="./informes/informe_puntuaciones.php?titulo=Clasificaci%C3%B3n%20detallada&id_fase=<?php echo $id_fase; ?>" target="_blank" class="px-5 py-3 bg-slate-800 text-white font-black uppercase text-xs tracking-widest rounded-2xl shadow-lg hover:bg-slate-900 transition-all flex items-center gap-2">
                    <i class="fas fa-file-pdf text-red-400"></i> PDF Clasificación
                </a>
            </div>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-200 mb-10 overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar pb-4">
                <table class="w-full text-left border-collapse whitespace-nowrap" id="dataTable">
                    <thead>
                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <th class="px-4 py-3 w-16 text-center"><i class="fas fa-list-ol"></i></th>
                            <th class="px-4 py-3 w-16 text-center">ID</th>
                            <th class="px-4 py-3 min-w-[300px]">Rutina / Deportistas</th>
                            <th class="px-4 py-3 text-center">Coach Card</th>
                            <th class="px-4 py-3 text-center">Baja</th>
                            <th class="px-4 py-3 text-center">Nota Final</th>
                            <th class="px-4 py-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php
                        $query = "SELECT r.id, r.dd_total, r.orden, r.nombre as nombre_rutina, r.id_club, r.nota_final, r.baja, c.nombre_corto as nombre_club, m.nombre as nombre_modalidad, cat.nombre as nombre_categoria, r.id_fase, f.elementos_coach_card, f.obsoleto 
                                  FROM rutinas r
                                  JOIN fases f ON r.id_fase = f.id
                                  JOIN modalidades m ON f.id_modalidad = m.id
                                  JOIN categorias cat ON f.id_categoria = cat.id
                                  JOIN clubes c ON r.id_club = c.id
                                  WHERE f.id = $id_fase AND f.id_competicion = $id_comp
                                  ORDER BY r.orden, r.id";

                        $query_run = mysqli_query($connection, $query);
                        if(mysqli_num_rows($query_run) > 0){
                            while ($row = mysqli_fetch_assoc($query_run)) {
                                $q_nombres = "SELECT GROUP_CONCAT(n.nombre SEPARATOR ', ') as nombres 
                                              FROM rutinas_participantes rp
                                              JOIN nadadoras n ON n.id = rp.id_nadadora 
                                              WHERE rp.reserva = 'no' AND rp.id_rutina = " . $row['id'];
                                $res_nombres = mysqli_query($connection, $q_nombres);
                                $nombres = mysqli_fetch_assoc($res_nombres)['nombres'] ?? '';
                                
                                $isBaja = ($row['baja'] == 'si');
                                $isPS = ($row['orden'] == -1);
                                $isOldSystem = ($row['obsoleto'] == 'si');
                                $rowClass = $isBaja ? "bg-red-50/30 opacity-75" : "hover:bg-slate-50 transition-colors";
                                $orden_display = $isPS ? 'PS' : $row['orden'];
                                $icon_puntuar = $isOldSystem ? 'fa-calculator' : 'fa-square-root-variable';
                        ?>
                        <tr class="<?php echo $rowClass; ?> group">
                            <td class="px-4 py-4 text-center font-black <?php echo $isBaja ? 'text-red-400' : 'text-slate-400'; ?> text-lg"><?php echo $orden_display; ?></td>
                            <td class="px-4 py-4 text-center font-bold text-slate-400 text-xs">#<?php echo $row['id']; ?></td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="text-sm font-black <?php echo $isBaja ? 'text-red-700' : 'text-slate-800'; ?> uppercase tracking-tighter">
                                        <?php echo $row['nombre_modalidad']." ".$row['nombre_categoria']." ".$row['nombre_club']; ?>
                                    </p>
                                    <?php if ($isPS): ?>
                                        <span class="px-2 py-0.5 bg-purple-100 text-purple-600 text-[8px] font-black rounded uppercase tracking-widest">Preswimmer</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($row['nombre_rutina']): ?>
                                    <p class="text-xs font-bold text-slate-500 italic mb-1"><i class="fas fa-quote-left text-slate-300 mr-1"></i> <?php echo $row['nombre_rutina']; ?></p>
                                <?php endif; ?>
                                <p class="text-[10px] font-bold text-slate-400 uppercase italic max-w-sm truncate" title="<?php echo $nombres; ?>"><i class="fas fa-users text-slate-300 mr-1"></i> <?php echo $nombres; ?></p>
                            </td>
                            
                            <td class="px-4 py-4 text-center">
                                <?php if($row['elementos_coach_card'] > 0): ?>
                                    <div class="flex flex-col items-center gap-1">
                                        <a target="_blank" href="./coach_card_composer.php?id_rutina=<?php echo $row['id']; ?>&id_fase=<?php echo $row['id_fase']; ?>" class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition-all flex items-center justify-center border border-amber-100 shadow-sm hover:shadow-lg hover:shadow-amber-500/20" title="Ver Coach Card">
                                            <i class="fas fa-puzzle-piece"></i>
                                        </a>
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[9px] font-black rounded-md border border-slate-200">DD: <?php echo $row['dd_total']; ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-300 font-black">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-4 py-4 text-center">
                                <?php if(!$isBaja): ?>
                                    <a href="./rutinas_code.php?id_rutina=<?php echo $row['id']; ?>&dar_baja=si&id_fase=<?php echo $id_fase; ?>" class="w-10 h-10 rounded-xl bg-white text-slate-300 border border-slate-200 hover:bg-red-50 hover:text-red-500 hover:border-red-200 transition-all flex items-center justify-center mx-auto shadow-sm" title="Dar de Baja">
                                        <i class="fas fa-arrow-down"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="./rutinas_code.php?id_rutina=<?php echo $row['id']; ?>&dar_baja=no&id_fase=<?php echo $id_fase; ?>" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 border border-red-200 hover:bg-emerald-50 hover:text-emerald-500 hover:border-emerald-200 transition-all flex items-center justify-center mx-auto shadow-sm shadow-red-500/10" title="Quitar Baja">
                                        <i class="fas fa-arrow-up"></i>
                                    </a>
                                <?php endif; ?>
                            </td>

                            <td class="px-4 py-4 text-center">
                                <span class="px-4 py-2 <?php echo $row['nota_final'] > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-400 border border-slate-200'; ?> rounded-xl text-lg font-black shadow-inner">
                                    <?php echo $row['nota_final'] > 0 ? $row['nota_final'] : '-'; ?>
                                </span>
                            </td>

                            <td class="px-4 py-4 text-right">
                                <?php 
                                $action_page = $isOldSystem ? 'puntuaciones_rutina_obsoleta.php' : 'puntuaciones_rutina.php';
                                ?>
                                <form target="_blank" action="<?php echo $action_page; ?>" method="POST">
                                    <input type="hidden" name="id_rutina" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="id_fase" value="<?php echo $row['id_fase']; ?>">
                                    <input type="hidden" name="id_club" value="<?php echo $row['id_club']; ?>">
                                    <input type="hidden" name="nombre_modalidad" value="<?php echo $row['nombre_modalidad']; ?>">
                                    <input type="hidden" name="nombre_categoria" value="<?php echo $row['nombre_categoria']; ?>">
                                    <input type="hidden" name="nombre_club" value="<?php echo $row['nombre_club']; ?>">
                                    <input type="hidden" name="nombre_rutina" value="<?php echo $row['nombre_rutina']; ?>">
                                    
                                    <button class="px-6 py-3 <?php echo $isBaja ? 'bg-slate-100 text-slate-400 cursor-not-allowed border border-slate-200' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white border border-emerald-100 hover:border-emerald-600 shadow-sm hover:shadow-emerald-500/30'; ?> font-black uppercase text-[10px] tracking-widest rounded-2xl transition-all flex items-center justify-center gap-2 ml-auto" type="submit" name="edit_btn" <?php echo $isBaja ? 'disabled' : ''; ?>>
                                        <i class="fas <?php echo $icon_puntuar; ?>"></i> Puntuar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='7' class='px-6 py-12 text-center text-slate-400 font-bold italic tracking-widest uppercase'>No hay rutinas registradas en esta fase</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
include('includes/scripts.php');
include('includes/footer.php');
?>
