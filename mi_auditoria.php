<?php
include('security.php');

// Si no es juez, redirigir al ranking
if ($_SESSION['id_rol'] != 4) {
    header('Location: ranking_jueces.php');
    exit();
}

$user_email = $_SESSION['email'];
$username = $_SESSION['username'];

// Intentar encontrar al juez por email o nombre
// Primero buscamos por email (asumiendo que podría existir una columna email en jueces algún día, o usando el username como puente)
$q_find = "
    SELECT group_key FROM auditoria_jueces_stats 
    WHERE nombre_entidad LIKE '%$username%' 
    AND entidad_tipo = 'GLOBAL' 
    LIMIT 1
";
$res_find = mysqli_query($connection, $q_find);
$found = mysqli_fetch_assoc($res_find);

if ($found) {
    header('Location: perfil_juez.php?key=' . $found['group_key']);
    exit();
} else {
    // Si no ha puntuado nunca, no habrá registros en auditoria_jueces_stats
    // Intentamos buscarlo en la tabla jueces para darle un mensaje informativo
    $q_juez = "SELECT id FROM jueces WHERE nombre LIKE '%$username%' OR apellidos LIKE '%$username%' LIMIT 1";
    $res_juez = mysqli_query($connection, $q_juez);
    
    include('includes/header.php');
    include('includes/navbar.php');
    ?>
    <main class="flex-1 flex flex-col min-w-0 bg-surface">
        <?php include('includes/topbar.php'); ?>
        <div class="p-6 md:p-10 max-w-7xl mx-auto w-full font-lexend flex flex-col items-center justify-center min-h-[60vh] text-center">
            <div class="w-24 h-24 rounded-3xl bg-blue-50 text-blue-400 flex items-center justify-center text-4xl mb-6 shadow-sm border border-blue-100">
                <i class="fas fa-user-magnifying-glass"></i>
            </div>
            <h2 class="text-3xl font-black text-slate-800 mb-2 italic uppercase tracking-tighter">Perfil de Juez no Vinculado</h2>
            <p class="text-slate-500 max-w-md font-medium leading-relaxed mb-8">
                No hemos podido encontrar registros de auditoría asociados a tu nombre de usuario (<?php echo $username; ?>). 
                Asegúrate de que tu nombre en el sistema coincida con tu ficha de Juez o solicita a un administrador que vincule tu cuenta.
            </p>
            <a href="ranking_jueces.php" class="btn-primary-v3">Ir al Ranking Global</a>
        </div>
    </main>
    <?php
    include('includes/scripts.php');
    include('includes/footer.php');
}
?>