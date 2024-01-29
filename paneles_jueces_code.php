<?php
include('security.php');
$id_competicion = $_SESSION['id_competicion_activa'];
//Añadir puesto juez
if(isset($_POST['save_btn'])){
	$id_puestos_juez = $_POST['id_puestos_juez'];
	$id_juez = $_POST['id_juez'];

	$query="INSERT INTO puesto_juez (id_puestos_juez, id_juez, id_competicion) VALUES ('".$id_puestos_juez."','".$id_juez."', '".$id_competicion."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Juez añadido con éxito';
		header('Location: paneles_jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');	
	}
}

//Actualizar registro
if(isset($_POST['update_btn'])){
	$id = $_POST['edit_id'];
	$nombre = $_POST['edit_nombre'];
	$id_juez = $_POST['id_juez'];	
    $id_puestos_juez = $_POST['id_puestos_juez'];


	$query = "UPDATE puesto_juez SET id_puestos_juez ='$id_puestos_juez', id_juez='$id_juez' WHERE id='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Datos actualizados con éxito';
		header('Location: paneles_jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. Los datos no se han actualizado <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');	
	}
}

//Borrar registro
if(isset($_POST['delete_btn'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM puesto_juez WHERE id ='$id'"; 
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Registro eliminado con éxito';
		header('Location: paneles_jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. El Registro no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');	
	}
}


//Añadir panel jueces
if(isset($_POST['save_btn_panel'])){
	$nombre = $_POST['nombre'];
	$numero_jueces = $_POST['numero_jueces'];
    $peso = $_POST['peso'];
    $descripcion = $_POST['descripcion'];
    $puntua = $_POST['puntua'];
    $color = $_POST['color'];




	$query="INSERT INTO paneles (nombre, numero_jueces, peso, descripcion, puntua, color, id_competicion) VALUES ('".$nombre."','".$numero_jueces."', '".$peso."','".$descripcion."','".$puntua."','".$color."','".$id_competicion."')";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Panel añadido con éxito';
		header('Location: paneles_jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');
	}
}

//Actualizar panel de jueces
if(isset($_POST['update_btn_panel'])){
	$id = $_POST['edit_id'];
	$id_paneles_tipo = $_POST['id_paneles_tipo'];
	$nombre = $_POST['edit_nombre'];
	$numero_jueces = $_POST['edit_numero_jueces'];
    $peso = $_POST['edit_peso'];
	$color = $_POST['edit_color'];
	$descripcion = $_POST['edit_descripcion'];


	$query = "UPDATE paneles SET nombre ='$nombre', numero_jueces='$numero_jueces', peso ='$peso', color ='$color', descripcion ='$descripcion', id_paneles_tipo='$id_paneles_tipo' WHERE id='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Panel actualizado con éxito';
		header('Location: paneles_jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. El Panel no se ha actualizado <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');
	}
}

//Borrar registro
if(isset($_POST['delete_btn_panel'])){
	$id = $_POST['delete_id'];

	$query = "DELETE FROM paneles WHERE id ='$id'";
	$query_run = mysqli_query($connection,$query);
	if(mysqli_error($connection) == ''){
		$_SESSION['correcto'] = 'Panel eliminado con éxito';
		header('Location: paneles_jueces.php');
	}else{
		$_SESSION['estado'] = 'Error. El Panel no se ha eliminado <br>'.mysqli_error($connection);
		header('Location: paneles_jueces.php');
	}
}





//Añadir panel_jueces
if(isset($_POST['panel_jueces_save_btn'])){
	$id = $_POST['id'];
	$id_fase = $_POST['id_fase'];
	$id_juez = $_POST['id_juez'];
    $numero_juez = $_POST['numero_juez'];
    $id_panel = $_POST['id_panel'];



    if($id == '  ' and $id_juez <> ''){
           $query="INSERT INTO panel_jueces (id_fase, id_juez, numero_juez, id_panel, id_competicion) VALUES ('".$id_fase."','".$id_juez."', '".$numero_juez."','".$id_panel."','".$id_competicion."')";
        $query_run = mysqli_query($connection,$query);
        if(mysqli_error($connection) == ''){
            $_SESSION['correcto'] = 'Panel añadido con éxito';
            header('Location: paneles_jueces.php');
        }else{
            $_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection).'<br>'.$query;
            header('Location: paneles_jueces.php');
        }
    }else if($id_juez <> ''){
        $query="UPDATE panel_jueces set id_juez=$id_juez where id=$id";
        $query_run = mysqli_query($connection,$query);
        if(mysqli_error($connection) == ''){
            $_SESSION['correcto'] = 'Panel añadido con éxito';
            header('Location: paneles_jueces.php');
        }else{
            $_SESSION['estado'] = 'Error. Registro no añadido <br>'.mysqli_error($connection).'<br>'.$query;
            header('Location: paneles_jueces.php');
        }
    }else
        $_SESSION['correcto'] = 'No se ha seleccionado ningún juez';
        header('Location: paneles_jueces.php');
}
	?>
