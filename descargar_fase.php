<?php
session_start();
set_time_limit(300);
ini_set('memory_limit', '512M');

include('security.php');

// 1. Prioridad a POST, fallback a GET (por si se necesita legacy)
$id_competicion = isset($_POST['id_competicion']) ? (int)$_POST['id_competicion'] : (isset($_GET['id_competicion']) ? (int)$_GET['id_competicion'] : null);
$id_fase        = isset($_POST['id_fase']) ? (int)$_POST['id_fase'] : (isset($_GET['id_fase']) ? (int)$_GET['id_fase'] : null);
$id_club_input  = isset($_POST['id_club']) ? (int)$_POST['id_club'] : (isset($_GET['id_club']) ? (int)$_GET['id_club'] : null);
$descargar_todo = isset($_POST['descargar_todo']) ? (bool)$_POST['descargar_todo'] : (isset($_GET['descargar_todo']) ? (bool)$_GET['descargar_todo'] : false);

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

if ($id_competicion && ($id_fase || $descargar_todo)) {
    $path_base = './public/music/' . $id_competicion . '/';

    $condicion_fase = $id_fase ? " AND rutinas.id_fase = $id_fase " : "";

    // 2. Consulta para obtener las rutinas
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
              WHERE fases.id_competicion = $id_competicion 
                $condicion_fase 
                $condicion_club";

    $result = mysqli_query($connection, $query);

    $archivos_encontrados = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ruta_fisica = $path_base . $row['id'] . '.mp3';

        if (file_exists($ruta_fisica)) {
            // Estructura de carpetas: "[OrdenFase]. Modalidad - Categoria / [OrdenNadadora] - Club - NombreMusica.mp3"
            $nombre_carpeta = $row['orden_fase'] . '. ' . $row['nombre_modalidad'] . ' - ' . $row['nombre_categoria'];
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
        $nombre_zip = sys_get_temp_dir() . '/temp_fase_' . ($id_fase ?? 'all') . '_' . time() . '.zip';

        if ($zip->open($nombre_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($archivos_encontrados as $arc) {
                $zip->addFile($arc['ruta_origen'], $arc['ruta_zip']);
            }
            $zip->close();

            if (file_exists($nombre_zip)) {
                // Obtener datos para el nombre del archivo final
                if ($descargar_todo) {
                    $q_comp_info = "SELECT nombre FROM competiciones WHERE id = $id_competicion";
                    $comp_info = mysqli_fetch_assoc(mysqli_query($connection, $q_comp_info));
                    $nom_comp = str_replace(' ', '_', $comp_info['nombre'] ?? 'Competicion');
                    
                    if ($_SESSION['id_rol'] == 5 || ($id_club !== null && $id_club > 0)) {
                        $q_club_nom = "SELECT nombre_corto FROM clubes WHERE id = $id_club";
                        $club_nom = mysqli_fetch_assoc(mysqli_query($connection, $q_club_nom))['nombre_corto'] ?? 'Club';
                        $club_nom_clean = str_replace(' ', '_', $club_nom);
                        $filename_final = "Musica_" . $nom_comp . "_" . $club_nom_clean . ".zip";
                    } else {
                        $filename_final = "Musica_" . $nom_comp . ".zip";
                    }
                } else {
                    $q_fase_info = "SELECT m.nombre as mod_nom, c.nombre as cat_nom 
                                    FROM fases fs 
                                    JOIN modalidades m ON fs.id_modalidad = m.id 
                                    JOIN categorias c ON fs.id_categoria = c.id 
                                    WHERE fs.id = $id_fase";
                    $fase_info = mysqli_fetch_assoc(mysqli_query($connection, $q_fase_info));
                    
                    $nom_mod = str_replace(' ', '_', $fase_info['mod_nom']);
                    $nom_cat = str_replace(' ', '_', $fase_info['cat_nom']);

                    if ($_SESSION['id_rol'] == 5 || ($id_club !== null && $id_club > 0)) {
                        // Nombre para Club: Musica_NombreClub_Modalidad_Categoria.zip
                        $q_club_nom = "SELECT nombre_corto FROM clubes WHERE id = $id_club";
                        $res_club_nom = mysqli_query($connection, $q_club_nom);
                        $row_club_nom = mysqli_fetch_assoc($res_club_nom);
                        $club_nom = $row_club_nom['nombre_corto'] ?? 'Club';
                        
                        $club_nom_clean = str_replace(' ', '_', $club_nom);
                        $filename_final = "Musica_" . $club_nom_clean . "_" . $nom_mod . "_" . $nom_cat . ".zip";
                    } else {
                        // Nombre para Admin (Fase completa): Musica_Fase_Modalidad_Categoria.zip
                        $filename_final = "Musica_Fase_" . $nom_mod . "_" . $nom_cat . ".zip";
                    }
                }

                // Limpiar el nombre final de caracteres extraños
                $filename_final = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $filename_final);

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