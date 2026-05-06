<?php
/**
 * Motor de Backup y Restauración NATIVO v3.0
 * Basado en comandos de sistema para máxima fiabilidad
 */

function mysqli_import_sql($file_path, $dbhost, $dbuser, $dbpass, $dbname) {
    // Si el archivo es .gz, lo descomprimimos temporalmente
    $is_gzip = (substr($file_path, -3) == '.gz');
    $cmd_base = $is_gzip ? "zcat" : "cat";
    
    // Comando nativo de restauración para evitar errores de parseo PHP
    $command = "$cmd_base $file_path | mysql -h $dbhost -u $dbuser -p'$dbpass' $dbname 2>&1";
    
    exec($command, $output, $return_var);
    
    if ($return_var !== 0) {
        return "Error en la restauración: " . implode("\n", $output);
    }
    
    return "complete dumping database !";
}

function backup_database($directory, $outname, $descripcion, $dbhost, $dbuser, $dbpass, $dbname) {
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $timestamp = date('Ymd_Hi');
    $filename = $outname . '_' . $timestamp . '.sql.gz';
    $fullname = $directory . '/' . $filename;

    // Comando nativo mysqldump con el comentario de descripción inyectado
    // Nota: El comentario se añade al final o se gestiona vía PHP antes de comprimir
    $command = "mysqldump --opt -h $dbhost -u $dbuser -p'$dbpass' $dbname | gzip > $fullname 2>&1";
    
    exec($command, $output, $return_var);

    if ($return_var !== 0) {
        return false;
    }

    // Inyectar el comentario en el archivo comprimido (opcional pero recomendado)
    // Para no complicar el binario, el comentario se puede guardar en un archivo .txt paralelo
    // o simplemente confiar en el nombre del archivo. 
    // Pero para mantener tu lógica de "comentarios", lo inyectaremos vía PHP:
    
    $sql_content = gzdecode(file_get_contents($fullname));
    $sql_content = "/*$descripcion*/\n\n" . $sql_content;
    file_put_contents($fullname, gzencode($sql_content, 9));

    return $filename;
}
?>
