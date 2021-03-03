<?php
//Datos de conexión
//Actualizado el 03032021 a las 00:27:24 por el usuario 
$servername = 'localhost';
$db_name ='sincrm3';
$db_username ='root';
$db_password = 'xas';

$connection = mysqli_connect($servername,$db_username,$db_password, $db_name);
$dbconfig = mysqli_select_db($connection,$db_name);
$mysqli = new mysqli($servername,$db_username,$db_password, $db_name);

if($dbconfig){
	// echo 'Conectado a base de datos';
}else{
	echo '
	<div class="container">
		<div class="row">
			<div class="col-md-8 mr-auto ml-auto text-center py-5 mt-5">
				<div class="card">
				<div class="card-body">
						<h1 class="card-title bg-danger text-white">Error de conexión a la base de datos</h1>
						<h2 class="card-title">Fallo</h2>
						<div class="card-text">Por favor comprueba la configuración de tu base de datos</div>
						<a href="./db_setup.php" class="btn btn-primary">:(</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	';
}
