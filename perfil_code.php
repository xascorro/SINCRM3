<?php
include('security.php');

if(isset($_POST['update_profile_btn'])){
    $user_id = $_SESSION['id_usario'];
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $telefono = mysqli_real_escape_string($connection, $_POST['telefono']);
    $new_password = $_POST['new_password'];
    $r_new_password = $_POST['r_new_password'];

    // Validar duplicidad de email (si se cambió)
    $check_email = mysqli_query($connection, "SELECT id FROM usuarios WHERE email = '$email' AND id != '$user_id'");
    if(mysqli_num_rows($check_email) > 0){
        $_SESSION['estado'] = "El correo electrónico ya está registrado por otro usuario.";
        header('Location: perfil.php');
        exit();
    }

    $query_parts = [
        "username = '$username'",
        "email = '$email'",
        "telefono = '$telefono'"
    ];

    // Lógica de contraseña
    if(!empty($new_password)){
        if(strlen($new_password) < 6){
            $_SESSION['estado'] = "La nueva contraseña debe tener al menos 6 caracteres.";
            header('Location: perfil.php');
            exit();
        }
        if($new_password !== $r_new_password){
            $_SESSION['estado'] = "Las contraseñas no coinciden.";
            header('Location: perfil.php');
            exit();
        }
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $query_parts[] = "hash = '$hashed'";
    }

    // Lógica de subida de foto
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)){
            $target_dir = "images/users/";
            if(!is_dir($target_dir)){
                mkdir($target_dir, 0777, true);
            }
            $new_filename = "user_" . $user_id . "_" . time() . "." . $ext;
            $target_file = $target_dir . $new_filename;
            
            if(move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)){
                $query_parts[] = "foto = '$target_file'";
                $_SESSION['foto'] = $target_file;
            } else {
                write_log("Error al mover la foto subida de perfil", "ERROR");
            }
        } else {
            $_SESSION['estado'] = "Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP.";
            header('Location: perfil.php');
            exit();
        }
    }

    $sql = "UPDATE usuarios SET " . implode(", ", $query_parts) . " WHERE id = '$user_id'";
    $query_run = mysqli_query($connection, $sql);

    if($query_run){
        // Actualizar variables de sesión críticas
        $_SESSION['username'] = $email; // En este sistema parece que username = email para el login
        $_SESSION['email'] = $email;
        
        write_log("Perfil actualizado por el usuario", "INFO");
        $_SESSION['correcto'] = "Tu perfil ha sido actualizado correctamente.";
    } else {
        write_log("Error al actualizar perfil: " . mysqli_error($connection), "ERROR");
        $_SESSION['estado'] = "Error técnico al actualizar el perfil.";
    }
    header('Location: perfil.php');
    exit();
}
?>