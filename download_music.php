<?php
session_start();
set_time_limit(1000);
ini_set('memory_limit', '512M');
include('security.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$id_competicion = $_GET['id_competicion'] ?? null;
$id_club = $_GET['id_club'] ?? null;
$condicion_club = '';
if ($id_club !== null) {
    $condicion_club = ' AND rutinas.id_club = ' . mysqli_real_escape_string($connection, $id_club);
}

if (!isset($_SESSION['mensajes_descarga_' . $id_competicion])) {
    $_SESSION['mensajes_descarga_' . $id_competicion] = [];
}
if (!isset($_SESSION['fases_encontradas_' . $id_competicion])) {
    $_SESSION['fases_encontradas_' . $id_competicion] = [];
}

function agregarMensajeDescarga($mensaje) {
    global $id_competicion;
    $hora = date('[Y-m-d H:i:s] ');
    $_SESSION['mensajes_descarga_' . $id_competicion][] = $hora . $mensaje;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Descarga por Fase</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light text-dark">

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4">Descarga de Archivos por Fase</h1>
            <img src="https://sincrm.pedrodiaz.eu/images/logo_sincrm_removebg.png" alt="Logo SINCRM" height="50">
        </div>

        <div class="card shadow">
            <div class="card-body">
                <h5 class="card-title">Enlaces de Descarga</h5>

                <?php
                if ($id_competicion !== null && is_dir('./public/music/' . $id_competicion . '/')) {
                    if (!isset($_GET['start'])) {
                        agregarMensajeDescarga("Iniciando escaneo de archivos para la competición: " . htmlspecialchars($id_competicion));
                        $path_competicion = './public/music/' . $id_competicion . '/';
                        $it = new RecursiveDirectoryIterator($path_competicion);
                        $iterator = new RecursiveIteratorIterator($it);
                        $fasesUnicas = [];

                        foreach ($iterator as $file) {
                            if ($file->getExtension() === 'mp3') {
                                $filename = str_replace($path_competicion, '', $file);
                                $id_archivo = str_replace('.mp3', '', $filename);

                                $query = "SELECT
    rutinas.id_fase,
    modalidades.nombre AS nombre_modalidad,
    categorias.nombre AS nombre_categoria,
    fases.orden
FROM rutinas
LEFT JOIN fases ON rutinas.id_fase = fases.id
LEFT JOIN modalidades ON fases.id_modalidad = modalidades.id
LEFT JOIN categorias ON fases.id_categoria = categorias.id
WHERE rutinas.id = '$id_archivo' $condicion_club";


                                $result = mysqli_query($connection, $query);
                                if ($row = mysqli_fetch_assoc($result)) {
    $fasesUnicas[$row['id_fase']] = [
        'modalidad' => $row['nombre_modalidad'],
        'categoria' => $row['nombre_categoria'],
        'orden' => $row['orden'] ?? 9999
    ];
}

                                mysqli_free_result($result);
                            }
                        }
uasort($fasesUnicas, function($a, $b) {
    return $a['orden'] <=> $b['orden'];
});

                        if (!empty($fasesUnicas)) {
                            echo '<ul class="list-group">';
                            foreach ($fasesUnicas as $fase_id => $info) {
                                echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                                echo '<div><strong>Fase ' . htmlspecialchars($fase_id) . '</strong><br><small>' .
                                    htmlspecialchars($info['modalidad']) . ' - ' .
                                    htmlspecialchars($info['categoria']) . '</small></div>';
                                echo '<a href="descargar_fase.php?id_competicion=' . urlencode($id_competicion) . '&id_fase=' . urlencode($fase_id) . '" class="btn btn-primary btn-sm">Descargar</a>';
                                echo '</li>';
                            }
                            echo '</ul>';
                            agregarMensajeDescarga("Escaneo completado. Enlaces de descarga generados.");
                            $_SESSION['fases_encontradas_' . $id_competicion] = $fasesUnicas;
                        } else {
                            echo '<div class="alert alert-warning">No se encontraron archivos MP3 para esta competición.</div>';
                        }
                    } else {
                        echo '<div class="alert alert-info">El procesamiento por lotes no es necesario para la descarga directa por fase.</div>';
                    }
                } elseif (isset($_GET['id_competicion'])) {
                    echo '<div class="alert alert-danger">La ruta de la competición no existe: ' . htmlspecialchars('./public/music/' . $_GET['id_competicion'] . '/') . '</div>';
                } else {
                    echo '<div class="alert alert-info">Por favor, proporciona el ID de la competición en la URL.</div>';
                }
                ?>
            </div>
        </div>

        <?php if (isset($_SESSION['mensajes_descarga_' . $id_competicion]) && is_array($_SESSION['mensajes_descarga_' . $id_competicion])): ?>
            <div class="mt-4">
                <h5>Registro del Escaneo</h5>
                <ul class="list-group list-group-flush">
                    <?php foreach ($_SESSION['mensajes_descarga_' . $id_competicion] as $mensaje): ?>
                        <li class="list-group-item"><?= htmlspecialchars($mensaje) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php unset($_SESSION['mensajes_descarga_' . $id_competicion]); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="text-center text-muted py-4 mt-5 border-top bg-white">
        <small>
            &copy; <?= date('Y') ?> SINCRM · Desarrollado por Pedro Díaz · <a href="https://sincrm.pedrodiaz.eu" target="_blank">sincrm.pedrodiaz.eu</a>
        </small>
    </footer>

</body>
</html>
