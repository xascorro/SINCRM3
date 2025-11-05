<?php
session_start();
set_time_limit(300);
ini_set('memory_limit', '512M');

include('security.php');

$id_competicion = $_GET['id_competicion'] ?? null;
$id_fase = $_GET['id_fase'] ?? null;
$id_club = $_GET['id_club'] ?? null;
$condicion_club = '';
if ($id_club !== null) {
    $condicion_club = ' AND rutinas.id_club = ' . mysqli_real_escape_string($connection, $id_club);
}

if ($id_competicion && $id_fase) {
    $path_competicion_original = './public/music/' . $id_competicion . '/';
    $path_competicion_ordenado = './public/music/' . $id_competicion . ' ordenado/';

    $archivos_a_zip = [];

    $it = new RecursiveDirectoryIterator($path_competicion_original);
    foreach (new RecursiveIteratorIterator($it) as $file) {
        if ($file->getExtension() === 'mp3') {
            $filename = str_replace($path_competicion_original, '', $file);
            $id_archivo = str_replace('.mp3', '', $filename);

            $query = "SELECT rutinas.orden, rutinas.music_name, fases.orden as orden_fase
                      FROM rutinas, fases
                      WHERE rutinas.id = '$id_archivo'
                        AND rutinas.id_fase = '$id_fase'
                        AND rutinas.id_fase = fases.id " . $condicion_club;
            $result = mysqli_query($connection, $query);

            if ($row = mysqli_fetch_assoc($result)) {
                $orden = $row['orden'];
                $music_name = $row['music_name'];
                $orden_fase = $row['orden_fase'];

                $folder = explode(" - ", $music_name);
                $modalidad_categoria = trim($folder[1] ?? '');
                $club_nombre = trim($folder[0] ?? '');

                $nombre_relativo_zip = $orden_fase . ' - ' . $modalidad_categoria . ' - ' . $club_nombre . '/' . $orden . ' - ' . $music_name;
                $archivos_a_zip[] = ['ruta_fisica' => $file, 'ruta_zip' => $nombre_relativo_zip];
            }
            mysqli_free_result($result);
        }
    }

    if (!empty($archivos_a_zip)) {
        $zip = new ZipArchive();
        $nombre_zip = 'competicion_' . $id_competicion . '_fase_' . $id_fase . '.zip';

        if ($zip->open($nombre_zip, ZipArchive::CREATE) === TRUE) {
            foreach ($archivos_a_zip as $archivo) {
                $zip->addFile($archivo['ruta_fisica'], $archivo['ruta_zip']);
            }
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $nombre_zip . '"');
            header('Content-Length: ' . filesize($nombre_zip));
            flush();
            readfile($nombre_zip);
            unlink($nombre_zip); // Eliminar el archivo zip temporal
            exit();
        } else {
            exit('No se pudo crear el archivo ZIP');
        }
    } else {
        exit('No se encontraron archivos para la fase ' . htmlspecialchars($id_fase) . '.');
    }
} else {
    exit('ID de competición o ID de fase no proporcionados.');
}
?>
