<?php
session_start();
set_time_limit(300);
ini_set('memory_limit', '512M');

include('security.php');

// 1. Seguridad: Forzar tipos enteros
$id_competicion = isset($_GET['id_competicion']) ? (int)$_GET['id_competicion'] : null;
$id_fase        = isset($_GET['id_fase']) ? (int)$_GET['id_fase'] : null;
$id_club        = isset($_GET['id_club']) ? (int)$_GET['id_club'] : null;

$condicion_club = '';
if ($id_club !== null) {
    $condicion_club = ' AND rutinas.id_club = ' . mysqli_real_escape_string($connection, $id_club);
}

if ($id_competicion && $id_fase) {
    $path_base = './public/music/' . $id_competicion . '/';

    // 2. Una sola consulta para obtener todas las rutinas de la fase
    $query = "SELECT 
                rutinas.id, 
                rutinas.orden, 
                rutinas.music_name, 
                fases.orden as orden_fase,
                modalidades.nombre as nombre_modalidad,
                categorias.nombre as nombre_categoria
              FROM rutinas
              INNER JOIN fases ON rutinas.id_fase = fases.id
              INNER JOIN modalidades ON fases.id_modalidad = modalidades.id
              INNER JOIN categorias ON fases.id_categoria = categorias.id
              WHERE rutinas.id_fase = $id_fase 
                AND fases.id_competicion = $id_competicion 
                $condicion_club";

    $result = mysqli_query($connection, $query);

    $archivos_encontrados = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ruta_fisica = $path_base . $row['id'] . '.mp3';

        // 3. Verificamos si el archivo existe físicamente
        if (file_exists($ruta_fisica)) {
            // Estructura de carpetas: "OrdenFase - Modalidad - Categoria / Orden - NombreMusica"
            $nombre_carpeta = $row['orden_fase'] . ' - ' . $row['nombre_modalidad'] . ' - ' . $row['nombre_categoria'];
            $nombre_archivo = $row['orden'] . ' - ' . $row['music_name'];
            
            // Limpiar caracteres no permitidos en nombres de carpetas/archivos
            $nombre_carpeta = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $nombre_carpeta);

            $archivos_encontrados[] = [
                'ruta_origen' => $ruta_fisica,
                'ruta_zip'    => $nombre_carpeta . '/' . $nombre_archivo
            ];
        }
    }

    if (!empty($archivos_encontrados)) {
        $zip = new ZipArchive();
        // Creamos un nombre temporal único para el ZIP
        $nombre_zip = 'temp_fase_' . $id_fase . '_' . time() . '.zip';

        if ($zip->open($nombre_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($archivos_encontrados as $arc) {
                $zip->addFile($arc['ruta_origen'], $arc['ruta_zip']);
            }
            $zip->close();

            // 4. Envío del archivo al navegador
            if (file_exists($nombre_zip)) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="Musica_Competicion_'.$id_competicion.'_Fase_'.$id_fase.'.zip"');
                header('Content-Length: ' . filesize($nombre_zip));
                header('Pragma: no-cache');
                header('Expires: 0');
                
                readfile($nombre_zip);
                
                // Borramos el temporal
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