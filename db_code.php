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

	$archivo = './database/dbconfig.php';
	
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
                <a href=\"./db_setup.php\" style=\"display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;\">Revisar Configuración</a>
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
	header('Location: db_setup.php');
	exit();
}

// Guardar Ajustes de Depuración
if (isset($_POST['save_debug_settings'])) {
    $debug_val = isset($_POST['show_errors']) ? 'true' : 'false';
    $archivo = './includes/config.php';
    
    if (file_exists($archivo)) {
        $contenido = file_get_contents($archivo);
        $nuevo_contenido = preg_replace(
            "/define\('DEBUG_MODE', (true|false)\);/",
            "define('DEBUG_MODE', $debug_val);",
            $contenido
        );
        
        if (file_put_contents($archivo, $nuevo_contenido)) {
            $_SESSION['correcto'] = 'Ajustes de depuración actualizados';
        } else {
            $_SESSION['error'] = 'Error al actualizar el archivo de configuración';
        }
    } else {
        $_SESSION['error'] = 'El archivo de configuración no existe';
    }
    header('Location: db_setup.php');
    exit();
}

// Borrar backup
if (isset($_POST['delete_backup'])) {
    $file = $_POST['backup_file'];
    $path = './database/backup/' . basename($file);
    if (file_exists($path) && unlink($path)) {
        $_SESSION['correcto'] = 'Backup eliminado correctamente';
    } else {
        $_SESSION['error'] = 'No se pudo eliminar el archivo';
    }
    header('Location: db_setup.php');
    exit();
}

// Restaurar backup
if (isset($_POST['restore_backup'])) {
    $file = $_POST['backup_file'];
    $path = './database/backup/' . basename($file);
    
    if (file_exists($path)) {
        $content = '';
        if (str_ends_with($file, '.gz')) {
            $content = gzdecode(file_get_contents($path));
        } else {
            $content = file_get_contents($path);
        }

        if ($content) {
            $res = mysqli_import_sql($content, $servername, $db_username, $db_password, $db_name);
            if ($res === 'complete dumping database !') {
                $_SESSION['correcto'] = 'Base de datos restaurada con éxito';
            } else {
                $_SESSION['error'] = 'Error en la restauración: ' . $res;
            }
        } else {
            $_SESSION['error'] = 'No se pudo leer el contenido del archivo';
        }
    } else {
        $_SESSION['error'] = 'El archivo no existe';
    }
    header('Location: db_setup.php');
    exit();
}

include('./includes/email_functions.php');

// Enviar email de prueba
if (isset($_POST['test_email'])) {
    $asunto = "Prueba de Conectividad SMTP - " . date('d/m/Y H:i:s');
    $cuerpo = "<h1>¡Conexión Exitosa!</h1><p>Si estás leyendo esto, el sistema de correo de GoDaddy está funcionando correctamente para mensajes de texto.</p>";
    
    $res = enviar_email(EMAIL_DESTINO, $asunto, $cuerpo);
    
    if ($res === true) {
        $_SESSION['correcto'] = 'Email de prueba enviado a ' . EMAIL_DESTINO;
    } else {
        $_SESSION['error'] = 'Fallo en la prueba: ' . $res;
    }
    header('Location: db_setup.php');
    exit();
}

// Enviar backup por email
if (isset($_POST['email_backup'])) {
    $file = $_POST['backup_file'];
    $path = './database/backup/' . basename($file);
    
    if (file_exists($path)) {
        $asunto = "Backup Base de Datos - " . $file;
        $cuerpo = "Se adjunta la copia de seguridad de la base de datos realizada el " . date('d/m/Y H:i:s') . ".<br><br>Archivo: <b>$file</b>";
        
        $res = enviar_email(EMAIL_DESTINO, $asunto, $cuerpo, $path);
        
        if ($res === true) {
            $_SESSION['correcto'] = 'Backup enviado correctamente a ' . EMAIL_DESTINO;
        } else {
            $_SESSION['error'] = 'Error al enviar el email: ' . $res;
        }
    } else {
        $_SESSION['error'] = 'El archivo de backup no existe';
    }
    header('Location: db_setup.php');
    exit();
}

// Vaciar Log
if (isset($_POST['clear_log'])) {
    $archivo = './log/log.txt';
    if (file_exists($archivo)) {
        file_put_contents($archivo, "");
        $_SESSION['correcto'] = 'Historial de log vaciado';
    }
    header('Location: db_setup.php');
    exit();
}

// Optimizar Base de Datos
if (isset($_POST['optimize_db'])) {
    $result = mysqli_query($connection, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) {
        mysqli_query($connection, "OPTIMIZE TABLE " . $row[0]);
    }
    $_SESSION['correcto'] = 'Tablas optimizadas y desfragmentadas con éxito';
    header('Location: db_setup.php');
    exit();
}

// Realizar backup
if (isset($_POST['backup_btn'])) {
	$directory = './database/backup/';
	$outname = $db_name;
	$descripcion = $_POST['descripcion'];
	if (backup_database($directory, $outname, $descripcion, $servername, $db_username, $db_password, $db_name)) {
		$_SESSION['correcto'] = 'Se ha realizado el backup de la base de datos';
	} else {
		$_SESSION['correcto'] = 'Error al realizar el backup de la base de datos';
	}
	header('Location: db_setup.php');
	exit();
}
