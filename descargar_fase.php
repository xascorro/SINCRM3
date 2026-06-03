<?php
session_start();
set_time_limit(300);
ini_set('memory_limit', '512M');

include('security.php');

// 1. Prioridad a POST, fallback a GET (por si se necesita legacy)
$id_competicion = isset($_POST['id_competicion']) ? (int)$_POST['id_competicion'] : (isset($_GET['id_competicion']) ? (int)$_GET['id_competicion'] : null);
$id_fase        = isset($_POST['id_fase']) ? (int)$_POST['id_fase'] : (isset($_GET['id_fase']) ? (int)$_GET['id_fase'] : null);
$id_club_input  = isset($_POST['id_club']) ? (int)$_POST['id_club'] : (isset($_GET['id_club']) ? (int)$_GET['id_club'] : null);

// --- CONTROL DE ACCESO ESTRICTO ---
if ($_SESSION['id_rol'] == 5) {
    $id_club = (int)$_SESSION['club'];
} else {
    $id_club = $id_club_input;
}

$condicion_club = '';
if ($id_club !== null && $id_club > 0) {
    $condicion_club = ' AND rutinas.id_club = ' . (int)$id_club;
}

if ($id_competicion && $id_fase) {
    $path_base = './public/music/' . $id_competicion . '/';

    // 2. Consulta para obtener las rutinas de la fase
    $query = "SELECT 
                rutinas.id, 
                rutinas.orden, 
                rutinas.music_name, 
                fases.orden as orden_fase,
                modalidades.nombre as nombre_modalidad,
                categorias.nombre as nombre_categoria,
                clubes.nombre_corto as nombre_club
              FROM rutinas
              INNER JOIN fases ON rutinas.id_fase = fases.id
              INNER JOIN modalidades ON fases.id_modalidad = modalidades.id
              INNER JOIN categorias ON fases.id_categoria = categorias.id
              INNER JOIN clubes ON rutinas.id_club = clubes.id
              WHERE rutinas.id_fase = $id_fase 
                AND fases.id_competicion = $id_competicion 
                $condicion_club";

    $result = mysqli_query($connection, $query);

    $archivos_encontrados = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ruta_fisica = $path_base . $row['id'] . '.mp3';

        if (file_exists($ruta_fisica)) {
            // Estructura de carpetas: "Modalidad - Categoria / Orden - Club - NombreMusica.mp3"
            $nombre_carpeta = $row['nombre_modalidad'] . ' - ' . $row['nombre_categoria'];
            $nombre_archivo = $row['orden'] . ' - ' . $row['nombre_club'] . ' - ' . $row['music_name'] . '.mp3';
            
            // Limpiar caracteres no permitidos
            $nombre_carpeta = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $nombre_carpeta);
            $nombre_archivo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $nombre_archivo);

            $archivos_encontrados[] = [
                'ruta_origen' => $ruta_fisica,
                'ruta_zip'    => $nombre_carpeta . '/' . $nombre_archivo
            ];
        }
    }

    if (!empty($archivos_encontrados)) {
        $zip = new ZipArchive();
        $nombre_zip = 'temp_fase_' . $id_fase . '_' . time() . '.zip';

        if ($zip->open($nombre_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($archivos_encontrados as $arc) {
                $zip->addFile($arc['ruta_origen'], $arc['ruta_zip']);
            }
            $zip->close();

            if (file_exists($nombre_zip)) {
                // Nombre del ZIP final descargado
                $filename_final = "Musica_Fase_" . $id_fase . ".zip";
                if ($id_club > 0) $filename_final = "Musica_Club_" . $id_club . "_Fase_" . $id_fase . ".zip";

                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="'.$filename_final.'"');
                header('Content-Length: ' . filesize($nombre_zip));
                header('Pragma: no-cache');
                header('Expires: 0');
                
                readfile($nombre_zip);
                unlink($nombre_zip);
                exit();
            }
        } else {
            exit('Error: No se pudo crear el archivo comprimido.');
        }
    } else {
        exit('No se encontraron archivos físicos para la fase seleccionada.');
    }
} else {
    exit('Faltan parámetros obligatorios (Competición o Fase).');
}
?>