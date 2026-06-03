<?php
include('security.php');

$id_rutina = $_SESSION['id_rutina_usuario'] ?? 0;
$id_competicion = $_SESSION['id_competicion_usuario'] ?? 0;

if ($id_rutina == 0) {
    header('Location: rutinas.php');
    exit();
}

include('includes/header.php');
include('includes/navbar.php');

// Fetch Routine Details
$query = "SELECT rutinas.id, rutinas.nombre as nombre_rutina, rutinas.id_fase, rutinas.id_club, 
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
    header('Location: rutinas.php');
    exit();
}

$nombre_modalidad = $data['nombre_modalidad'];
$nombre_categoria = $data['nombre_categoria'];
$nombre_club = $data['nombre_club'];
$nombre_rutina = $data['nombre_rutina'];
$numero_participantes = $data['numero_participantes'];
$numero_reservas = $data['numero_reservas'];
?>

<main class="flex-1 flex flex-col min-w-0 bg-surface">
    <?php include('includes/topbar.php'); ?>

    <div class="p-6 md:p-10 max-w-5xl mx-auto w-full font-lexend">
        
        <!-- Header -->
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tighter mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200"><i class="fas fa-users text-lg"></i></span>
                    Participantes
                </h1>
                <p class="text-slate-500 font-medium"><?php echo $nombre_modalidad." ".$nombre_categoria." - ". $nombre_club.' '.$nombre_rutina; ?></p>
            </div>
            <a href="rutinas.php" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-black uppercase text-xs tracking-widest rounded-2xl shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="fas fa-chevron-left text-xs"></i> Volver
            </a>
        </div>

        <?php include('includes/alertas_v4.php'); ?>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden mb-10">
            <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-lg font-black text-slate-800 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fas fa-id-badge"></i></span>
                    Titulares (<?php echo $numero_participantes; ?>)
                </h3>
            </div>
            
            <div class="p-8 space-y-4">
                <?php for ($x=0; $x<$numero_participantes; $x++): 
                    $query_p = "SELECT * FROM rutinas_participantes WHERE id_rutina=$id_rutina AND reserva='no' LIMIT $x,1";
                    $participante = mysqli_fetch_assoc(mysqli_query($connection, $query_p));
                    $id_nadadora = $participante['id_nadadora'] ?? 0;
                    $id_registro = $participante['id'] ?? 0;
                ?>
                    <form action="rutinas_participantes_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center p-4 bg-slate-50/50 rounded-2xl border border-slate-100 hover:border-blue-200 transition-colors">
                        <div class="md:col-span-2">
                            <span class="px-3 py-1 bg-blue-600 text-white text-[10px] font-black uppercase rounded-lg tracking-widest">TITULAR <?php echo $x+1; ?></span>
                        </div>
                        <div class="md:col-span-8">
                            <?php 
                            ob_start();
                            include('./includes/nadadoras_select_option.php');
                            $select_html = ob_get_clean();
                            echo str_replace('<select', '<select name="id_nadadora" class="v3-select-fix"', preg_replace('/<label.*?>.*?<\/label>/i', '', $select_html));
                            ?>
                        </div>
                        <div class="md:col-span-2 flex justify-end gap-2">
                            <input type="hidden" name="id" value="<?php echo $id_registro; ?>">
                            <input type="hidden" name="reserva" value="no">
                            <?php if ($id_nadadora > 0): ?>
                                <button type="submit" name="update_btn" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Actualizar">
                                    <i class="fas fa-save text-xs"></i>
                                </button>
                                <button type="submit" name="delete_btn" class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Quitar">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            <?php else: ?>
                                <button type="submit" name="save_btn" class="w-full py-2.5 bg-blue-600 text-white font-black uppercase text-[10px] tracking-widest rounded-xl shadow-lg hover:scale-105 transition-all">
                                    Asignar
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endfor; ?>
            </div>
        </div>

        <?php if($numero_reservas > 0): ?>
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-8 border-b border-slate-100 bg-amber-50/30">
                <h3 class="text-lg font-black text-slate-800 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm"><i class="fas fa-id-badge"></i></span>
                    Reservas (<?php echo $numero_reservas; ?>)
                </h3>
            </div>
            
            <div class="p-8 space-y-4">
                <?php for ($x=0; $x<$numero_reservas; $x++): 
                    $query_p = "SELECT * FROM rutinas_participantes WHERE id_rutina=$id_rutina AND reserva='si' LIMIT $x,1";
                    $participante = mysqli_fetch_assoc(mysqli_query($connection, $query_p));
                    $id_nadadora = $participante['id_nadadora'] ?? 0;
                    $id_registro = $participante['id'] ?? 0;
                ?>
                    <form action="rutinas_participantes_code.php" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center p-4 bg-slate-50/50 rounded-2xl border border-slate-100 hover:border-amber-200 transition-colors">
                        <div class="md:col-span-2">
                            <span class="px-3 py-1 bg-amber-500 text-white text-[10px] font-black uppercase rounded-lg tracking-widest">RESERVA <?php echo $x+1; ?></span>
                        </div>
                        <div class="md:col-span-8">
                            <?php 
                            ob_start();
                            include('./includes/nadadoras_select_option.php');
                            $select_html = ob_get_clean();
                            echo str_replace('<select', '<select name="id_nadadora" class="v3-select-fix"', preg_replace('/<label.*?>.*?<\/label>/i', '', $select_html));
                            ?>
                        </div>
                        <div class="md:col-span-2 flex justify-end gap-2">
                            <input type="hidden" name="id" value="<?php echo $id_registro; ?>">
                            <input type="hidden" name="reserva" value="si">
                            <?php if ($id_nadadora > 0): ?>
                                <button type="submit" name="update_btn" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Actualizar">
                                    <i class="fas fa-save text-xs"></i>
                                </button>
                                <button type="submit" name="delete_btn" class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Quitar">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            <?php else: ?>
                                <button type="submit" name="save_btn" class="w-full py-2.5 bg-amber-500 text-white font-black uppercase text-[10px] tracking-widest rounded-xl shadow-lg hover:scale-105 transition-all">
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

<?php 
include('includes/scripts.php');
include('includes/footer.php'); 
?>
