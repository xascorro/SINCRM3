<?php
include('security.php');
include('./includes/mysql_backup_import.php');
//Modificar ./database/dbconfig.php
if (isset($_POST['update_btn']) && isset($_POST['accept']) && $_POST['accept'] == '1') {
	$servername = "'" . $_POST['servername'] . "';";
	$db_username = "'" . $_POST['db_username'] . "';";
	$db_password = "'" . $_POST['db_password'] . "';";
	$db_name = "'" . $_POST['db_name'] . "';";
	// Abrir el archivo
	$archivo = './database/dbconfig.php';
	$abrir = fopen($archivo, 'r+');
	$contenido = fread($abrir, filesize($archivo));
	fclose($abrir);
	// Separar linea por linea
	$contenido = explode("\n", $contenido);
	// Modificar las líneas deseadas
	$contenido[0] = '<?php';
	$contenido[1] = '//Datos de conexión';
	$contenido[2] = "//Actualizado el " . date('dmY') . ' a las ' . date('H') . ':' . date('i') . ':' . date('s');
	$contenido[3] = '$servername = ' . $servername;
	$contenido[4] = '$db_name =' . $db_name;
	$contenido[5] = '$db_username =' . $db_username;
	$contenido[6] = '$db_password = ' . $db_password;
	// Unir archivo
	$contenido = implode("\n", $contenido);
	// Guardar Archivo
	$guardar = fopen($archivo, 'w');
	fwrite($guardar, $contenido);
	fclose($guardar);
	$_SESSION['correcto'] = 'Configuración actualizada';
	header('Location: db_setup.php');
}


//Realizar backup
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
}
