<?php
include('security.php');
//Añadir registro
if (isset($_POST['update_btn']) && isset($_POST['accept']) && $_POST['accept'] == '1') {
	$servername = "'".$_POST['servername']."';";
	$db_username = "'".$_POST['db_username']."';";
	$db_password = "'".$_POST['db_password']."';";
	$db_name = "'".$_POST['db_name']."';";
	// Abrir el archivo
	$archivo = './database/dbconfig.php';
	$abrir = fopen($archivo,'r+');
	$contenido = fread($abrir,filesize($archivo));
	fclose($abrir);
	 
	// Separar linea por linea
	$contenido = explode("\n",$contenido);
	 
	// Modificar las líneas deseadas
	$contenido[0] = '<?php';
	$contenido[1] = '//Datos de conexión';
	$contenido[2] = "//Actualizado el ".date('dmY').' a las '.date('H').':'.date('i').':'.date('s')." por el usuario ";
	$contenido[3] = '$servername = '.$servername;
	$contenido[4] = '$db_name ='.$db_name;
	$contenido[5] = '$db_username ='.$db_username;
	$contenido[6] = '$db_password = '.$db_password;	 
	// Unir archivo
	$contenido = implode("\n",$contenido);	 
	// Guardar Archivo
	$guardar = fopen($archivo,'w');
	fwrite($guardar,$contenido);
	fclose($guardar);
	$_SESSION['correcto'] = 'Configuración actualizada';
		header('Location: de_setud.php');
	 

}



//Borrar registro
if (isset($_POST['delete_btn'])) {
	$id = $_POST['delete_id'];

	$query = "DELETE FROM jueces WHERE id ='$id'";
	$query_run = mysqli_query($connection, $query);
	if (mysqli_error($connection) == '') {
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: jueces.php');
	} else {
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>' . mysqli_error($connection);
		header('Location: jueces.php');
	}
}
