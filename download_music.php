<?php
session_start();
set_time_limit(1000);
ini_set('memory_limit', '512M');
include('security.php'); 

error_reporting(E_ALL);
ini_set('display_errors', 1);

$id_competicion = isset($_GET['id_competicion']) ? (int)$_GET['id_competicion'] : null;
$id_club = isset($_GET['id_club']) ? (int)$_GET['id_club'] : null;

$condicion_club = '';
if ($id_club !== null) {
    $condicion_club = ' AND rutinas.id_club = ' . mysqli_real_escape_string($connection, $id_club);
}

if ($id_competicion && !isset($_SESSION['mensajes_descarga_' . $id_competicion])) {
    $_SESSION['mensajes_descarga_' . $id_competicion] = [];
}

function agregarMensajeDescarga($mensaje, $tipo = 'info') {
    global $id_competicion;
    $hora = date('[H:i:s] ');
    $color = ($tipo == 'error') ? 'text-danger' : (($tipo == 'warning') ? 'text-warning' : 'text-success');
    $_SESSION['mensajes_descarga_' . $id_competicion][] = "<span class='$color'>$hora $mensaje</span>";
}

// --- LÓGICA DE ESCANEO E INTEGRIDAD ---
$path_base = "./public/music/" . (int)$id_competicion . "/";
$stats_fases = [];
$ids_en_disco = [];

if ($id_competicion !== null && is_dir($path_base)) {
    // 1. Escaneo físico del disco
    $dir = new DirectoryIterator($path_base);
    foreach ($dir as $file) {
        if ($file->isFile() && $file->getExtension() === 'mp3') {
            $id_archivo = $file->getBasename('.mp3');
            if (is_numeric($id_archivo)) $ids_en_disco[] = (int)$id_archivo;
        }
    }

    // 2. Consulta de base de datos
    $query = "SELECT 
                rutinas.id,
                rutinas.id_fase, 
                modalidades.nombre AS mod_nom, 
                categorias.nombre AS cat_nom, 
                clubes.nombre_corto AS nombre_club,
                fases.orden 
              FROM rutinas 
              INNER JOIN fases ON rutinas.id_fase = fases.id 
              INNER JOIN modalidades ON fases.id_modalidad = modalidades.id 
              INNER JOIN categorias ON fases.id_categoria = categorias.id 
              INNER JOIN clubes ON rutinas.id_club = clubes.id
              WHERE fases.id_competicion = $id_competicion $condicion_club
              ORDER BY fases.orden ASC";

    $result = mysqli_query($connection, $query);
    
    $ids_en_db = [];
    $rutinas_sin_musica = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $f_id = $row['id_fase'];
        $id_rutina = (int)$row['id'];
        $ids_en_db[] = $id_rutina;

        // Inicializar fase en el array de estadísticas si no existe
        if (!isset($stats_fases[$f_id])) {
            $stats_fases[$f_id] = [
                'nombre' => $row['mod_nom'] . " " . $row['cat_nom'],
                'total' => 0,
                'subidas' => 0
            ];
        }
        $stats_fases[$f_id]['total']++;

        // Verificar si existe el archivo
        if (in_array($id_rutina, $ids_en_disco)) {
            $stats_fases[$f_id]['subidas']++;
        } else {
            $rutinas_sin_musica[] = $row['nombre_club'] . " (" . $row['mod_nom'] . " " . $row['cat_nom'] . ")";
        }
    }

    // 3. Registro de alertas en el log
    $huerfanos = array_diff($ids_en_disco, $ids_en_db);
    foreach ($huerfanos as $h) {
        agregarMensajeDescarga("HUÉRFANO: El archivo $h.mp3 no pertenece a ninguna rutina activa.", 'warning');
    }
    foreach ($rutinas_sin_musica as $faltante) {
        agregarMensajeDescarga("FALTANTE: Sin música -> $faltante", 'error');
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestor de Música - SINCRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .border-left-info { border-left: .25rem solid #36b9cc!important; }
        .border-left-danger { border-left: .25rem solid #e74a3b!important; }
        .border-left-success { border-left: .25rem solid #1cc88a!important; }
        .progress-sm { height: .5rem; }
    </style>
</head>
<body class="bg-light">

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 text-gray-800">Gestor de Música por Fase</h1>
            <img src="https://sincrm.pedrodiaz.eu/images/logo_sincrm_removebg.png" alt="Logo" height="50">
        </div>

        <div class="row mb-2">
            <div class="col-12">
                <h5 class="font-weight-bold text-dark"><i class="fas fa-tasks me-2"></i>Progreso y Descarga</h5>
            </div>
        </div>

        <div class="row mb-4">
            <?php if (!empty($stats_fases)): ?>
                <?php foreach ($stats_fases as $id_f => $info): 
                    $porcentaje = ($info['total'] > 0) ? round(($info['subidas'] / $info['total']) * 100) : 0;
                    
                    // Colores de barra y borde según progreso
                    $color_bar = "bg-danger";
                    $border_class = "border-left-danger";

                    if ($porcentaje > 40) $color_bar = "bg-warning";
                    if ($porcentaje > 89) $color_bar = "bg-info";
                    
                    if ($porcentaje == 100) {
                        $color_bar = "bg-success";
                        $border_class = "border-left-success";
                    }
                ?>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card shadow-sm <?php echo $border_class; ?> h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="text-xs font-weight-bold text-info text-uppercase" style="font-size: 0.75rem;">
                                    <?php echo htmlspecialchars($info['nombre']); ?>
                                </div>
                                <span class="badge <?php echo ($porcentaje == 100) ? 'text-bg-success' : 'text-bg-dark'; ?> border">
                                    <?php echo $info['subidas']; ?> / <?php echo $info['total']; ?>
                                </span>
                            </div>
                            
                            <div class="row no-gutters align-items-center mb-3">
                                <div class="col-auto me-2">
                                    <div class="h6 mb-0 font-weight-bold text-gray-800"><?php echo $porcentaje; ?>%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm">
                                        <div class="progress-bar <?php echo $color_bar; ?>" role="progressbar" 
                                             style="width: <?php echo $porcentaje; ?>%" 
                                             aria-valuenow="<?php echo $porcentaje; ?>" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto pt-2">
                                <?php if ($info['subidas'] > 0): ?>
                                    <a href="descargar_fase.php?id_competicion=<?php echo $id_competicion; ?>&id_fase=<?php echo $id_f; ?>" 
                                       class="btn btn-primary btn-sm w-100 shadow-sm">
                                        <i class="fas fa-download me-1"></i> Descargar ZIP
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm w-100 disabled" disabled>
                                        <i class="fas fa-exclamation-circle me-1"></i> Sin archivos
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">No se encontraron fases o rutinas para esta competición.</div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($_SESSION['mensajes_descarga_' . $id_competicion])): ?>
            <div class="card shadow-sm bg-dark text-light mb-5">
                <div class="card-header bg-dark border-secondary py-2">
                    <h6 class="m-0 font-weight-bold text-secondary small text-uppercase">Alertas e Integridad del Sistema</h6>
                </div>
                <div class="card-body py-2 small" style="font-family: 'Courier New', Courier, monospace; max-height: 250px; overflow-y: auto;">
                    <?php 
                    foreach ($_SESSION['mensajes_descarga_' . $id_competicion] as $m) echo $m . "<br>";
                    unset($_SESSION['mensajes_descarga_' . $id_competicion]);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-center mb-5">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Panel Principal
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>