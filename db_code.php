<?php
include('security.php');
include('database/dbconfig.php');
include('./includes/mysql_backup_import.php');

//Modificar ./database/dbconfig.php
if (isset($_POST['update_btn']) && isset($_POST['accept']) && $_POST['accept'] == '1') {
	$servername = $_POST['servername'];
	$db_username = $_POST['db_username'];
	$db_password = $_POST['db_password'];
	$db_name = $_POST['db_name'];

	$archivo = 'database/dbconfig.php';
	
	$nuevo_contenido = "<?php
/**
 * Configuración de la Base de Datos
 * Actualizado desde el panel de control el " . date('d/m/Y') . " a las " . date('H:i:s') . "
 */

// Dominios de producción/beta
\$prod_domains = ['sincrm.pedrodiaz.eu', 'beta.pedrodiaz.eu'];

if (in_array(\$_SERVER['SERVER_NAME'], \$prod_domains)) {
    // Entorno Producción / Beta
    \$servername  = '$servername';
    \$db_name     = '$db_name';
    \$db_username = '$db_username';
    \$db_password = '$db_password';
} else {
    // Entorno Local / Desarrollo (Valores por defecto)
    \$servername  = 'localhost';
    \$db_name     = '$db_name';
    \$db_username = 'root';
    \$db_password = 'xas';
}

// Establecer conexión
\$connection = mysqli_connect(\$servername, \$db_username, \$db_password, \$db_name);

if (\$connection) {
    mysqli_set_charset(\$connection, \"utf8mb4\");
    \$dbconfig = true;
    
    // Objeto MySQLi para código moderno
    \$mysqli = new mysqli(\$servername, \$db_username, \$db_password, \$db_name);
    \$mysqli->set_charset(\"utf8mb4\");
} else {
    \$dbconfig = false;
    if (php_sapi_name() !== 'cli') {
        echo '
        <div class=\"container\" style=\"margin-top: 50px; font-family: sans-serif;\">
            <div style=\"max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #dc3545; border-radius: 5px; text-align: center; background-color: #f8d7da;\">
                <h1 style=\"color: #721c24;\">Error de conexión</h1>
                <p style=\"color: #721c24;\">No se ha podido establecer conexión con la base de datos.</p>
                <p>Por favor, comprueba la configuración o contacta con el administrador.</p>
                <a href=\"./mantenimiento.php\" style=\"display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;\">Revisar Configuración</a>
            </div>
        </div>';
    }
}
?>";

	if (file_put_contents($archivo, $nuevo_contenido)) {
		$_SESSION['correcto'] = 'Configuración actualizada';
	} else {
		$_SESSION['error'] = 'Error al escribir el archivo de configuración';
	}
	header('Location: configuracion_sistema.php');
	exit();
}

// Guardar Ajustes de Depuración
if (isset($_POST['save_debug_settings'])) {
    $debug_val = isset($_POST['show_errors']) ? 'true' : 'false';
    $archivo = 'includes/config.php';
    
    if (file_exists($archivo)) {
        if (is_writable($archivo)) {
            $contenido = file_get_contents($archivo);
            $nuevo_contenido = preg_replace(
                "/define\s*\(\s*['\"]DEBUG_MODE['\"]\s*,\s*(true|false)\s*\)\s*;/",
                "define('DEBUG_MODE', $debug_val);",
                $contenido
            );
            
            if (file_put_contents($archivo, $nuevo_contenido) !== false) {
                $_SESSION['correcto'] = 'Ajustes de depuración actualizados';
            } else {
                $_SESSION['error'] = 'Error al escribir en el archivo de configuración';
            }
        } else {
            $_SESSION['error'] = 'El archivo de configuración no tiene permisos de escritura (chmod 666)';
        }
    } else {
        $_SESSION['error'] = 'El archivo de configuración no existe en ' . $archivo;
    }
    header('Location: configuracion_sistema.php');
    exit();
}

// Guardar Límites PHP (.user.ini y .htaccess)
if (isset($_POST['save_php_settings'])) {
    $upload = $_POST['upload_max_filesize'];
    $post   = $_POST['post_max_size'];
    $memory = $_POST['memory_limit'];
    $exec   = $_POST['max_execution_time'];
    $vars   = $_POST['max_input_vars'];
    
    // 1. .user.ini (Para PHP-FPM/FastCGI)
    $archivo_ini = '.user.ini';
    $contenido_ini = "upload_max_filesize = $upload\npost_max_size = $post\nmemory_limit = $memory\nmax_execution_time = $exec\nmax_input_vars = $vars";
    file_put_contents($archivo_ini, $contenido_ini);

    // 2. .htaccess (Para Apache mod_php)
    $archivo_htaccess = '.htaccess';
    $bloque_php = "\n# --- BEGIN SINCRM PHP LIMITS ---\n";
    $bloque_php .= "<IfModule mod_php.c>\n";
    $bloque_php .= "   php_value upload_max_filesize $upload\n";
    $bloque_php .= "   php_value post_max_size $post\n";
    $bloque_php .= "   php_value memory_limit $memory\n";
    $bloque_php .= "   php_value max_execution_time $exec\n";
    $bloque_php .= "   php_value max_input_vars $vars\n";
    $bloque_php .= "</IfModule>\n";
    $bloque_php .= "<IfModule mod_php7.c>\n";
    $bloque_php .= "   php_value upload_max_filesize $upload\n";
    $bloque_php .= "   php_value post_max_size $post\n";
    $bloque_php .= "   php_value memory_limit $memory\n";
    $bloque_php .= "   php_value max_execution_time $exec\n";
    $bloque_php .= "   php_value max_input_vars $vars\n";
    $bloque_php .= "</IfModule>\n";
    $bloque_php .= "# --- END SINCRM PHP LIMITS ---\n";

    // Leer .htaccess actual si existe, para no borrar otras cosas
    $contenido_actual = "";
    if (file_exists($archivo_htaccess)) {
        $contenido_actual = file_get_contents($archivo_htaccess);
        // Limpiar bloque anterior si existe
        $contenido_actual = preg_replace("/# --- BEGIN SINCRM PHP LIMITS ---.*# --- END SINCRM PHP LIMITS ---/s", "", $contenido_actual);
    }
    
    if (file_put_contents($archivo_htaccess, trim($contenido_actual) . "\n" . $bloque_php)) {
        write_log("Runtime PHP actualizado: Upload=$upload, Mem=$memory, Exec=$exec", 'SUCCESS');
        $_SESSION['correcto'] = 'Límites PHP actualizados en .user.ini y .htaccess';
    } else {
        write_log("Error al escribir configuración de límites PHP", 'ERROR');
        $_SESSION['error'] = 'Error al escribir el archivo .htaccess';
    }
    
    header('Location: configuracion_sistema.php');
    exit();
}

// Crear Backup
if (isset($_POST['backup_btn'])) {
    $descripcion = $_POST['descripcion'] ?: 'Backup manual';
    $res = backup_database('./database/backup', 'sincrm4', $descripcion, $servername, $db_username, $db_password, $db_name);
    
    if ($res) {
        write_log("Nuevo backup creado: $res ($descripcion)", "SUCCESS");
        $_SESSION['correcto'] = "Copia de seguridad creada: $res";
    } else {
        write_log("Error al crear backup", "ERROR");
        $_SESSION['error'] = 'Error al generar la copia de seguridad';
    }
    header('Location: configuracion_sistema.php');
    exit();
}

// Restaurar backup
if (isset($_POST['restore_backup'])) {
    $file = $_POST['backup_file'];
    $path = './database/backup/' . basename($file);
    
    if (file_exists($path)) {
        $res = mysqli_import_sql($path, $servername, $db_username, $db_password, $db_name);
        if ($res === 'complete dumping database !') {
            write_log("Restauración de base de datos exitosa: $file", "SUCCESS");
            $_SESSION['correcto'] = 'Base de datos restaurada con éxito';
        } else {
            write_log("Error al restaurar base de datos ($file): $res", "ERROR");
            $_SESSION['error'] = 'Error en la restauración: ' . $res;
        }
    } else {
        $_SESSION['error'] = 'El archivo de backup no existe';
    }
    header('Location: configuracion_sistema.php');
    exit();
}

// Borrar backup
if (isset($_POST['delete_backup'])) {
    $file = $_POST['backup_file'];
    $path = './database/backup/' . basename($file);
    
    if (file_exists($path)) {
        if (unlink($path)) {
            $_SESSION['correcto'] = 'Copia de seguridad eliminada';
        } else {
            $_SESSION['error'] = 'Error al borrar el archivo físico';
        }
    } else {
        $_SESSION['error'] = 'El archivo ya no existe';
    }
    header('Location: configuracion_sistema.php');
    exit();
}

// Optimizar DB
if (isset($_POST['optimize_db'])) {
    $tables_res = mysqli_query($connection, "SHOW TABLES");
    $all_ok = true;
    while ($t = mysqli_fetch_array($tables_res)) {
        $table = $t[0];
        if (!mysqli_query($connection, "OPTIMIZE TABLE `$table`")) $all_ok = false;
        if (!mysqli_query($connection, "REPAIR TABLE `$table`")) $all_ok = false;
    }
    
    if ($all_ok) {
        write_log("Optimización y reparación de base de datos completada", "SUCCESS");
        $_SESSION['correcto'] = 'Base de datos optimizada y reparada';
    } else {
        $_SESSION['error'] = 'Algunas tablas no pudieron optimizarse';
    }
    header('Location: configuracion_sistema.php');
    exit();
}

// Vaciar Log
if (isset($_POST['clear_log'])) {
    $log_file = './log/log.txt';
    if (file_put_contents($log_file, "")) {
        $_SESSION['correcto'] = 'Archivo de log vaciado correctamente';
    } else {
        $_SESSION['error'] = 'Error al vaciar el archivo de log';
    }
    header('Location: mantenimiento.php');
    exit();
}

// Borrar archivo residual
if (isset($_POST['delete_residual_file'])) {
    $file = $_POST['file_path'];
    if (unlink($file)) {
        $_SESSION['correcto'] = "Archivo residual eliminado: $file";
    } else {
        $_SESSION['error'] = "No se pudo eliminar el archivo";
    }
    header('Location: mantenimiento.php');
    exit();
}
?>
