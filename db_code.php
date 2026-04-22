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
//Datos de conexión
//Actualizado el " . date('d/m/Y') . " a las " . date('H:i:s') . "
\$servername = '$servername';
\$db_name = '$db_name';
\$db_username = '$db_username';
\$db_password = '$db_password';

\$connection = mysqli_connect(\$servername, \$db_username, \$db_password, \$db_name);
mysqli_set_charset(\$connection, \"utf8mb4\");
\$dbconfig = mysqli_select_db(\$connection, \$db_name);
\$mysqli = new mysqli(\$servername, \$db_username, \$db_password, \$db_name);

if(!\$dbconfig){
	echo '
	<div class=\"container\">
		<div class=\"row\">
			<div class=\"col-md-8 mr-auto ml-auto text-center py-5 mt-5\">
				<div class=\"card\">
				<div class=\"card-body\">
						<h1 class=\"card-title bg-danger text-white\">Error de conexión a la base de datos</h1>
						<h2 class=\"card-title\">Fallo</h2>
						<div class=\"card-text\">Por favor comprueba la configuración de tu base de datos</div>
						<a href=\"./db_setup.php\" class=\"btn btn-primary\">:(</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	';
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
