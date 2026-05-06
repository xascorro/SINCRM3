<?php
include('database/dbconfig.php');

echo "<h1>Diagnóstico de Conexión</h1>";
echo "SERVER_NAME: " . $_SERVER['SERVER_NAME'] . "<br>";
echo "DB Host: " . $servername . "<br>";
echo "DB Name: " . $db_name . "<br>";
echo "DB User: " . $db_username . "<br>";

if ($connection) {
    echo "<h2 style='color:green'>Conexión Exitosa</h2>";
    $res = mysqli_query($connection, "SELECT COUNT(*) as total FROM usuarios");
    $row = mysqli_fetch_assoc($res);
    echo "Total Usuarios: " . $row['total'] . "<br>";
} else {
    echo "<h2 style='color:red'>Fallo de Conexión</h2>";
    echo "Error: " . mysqli_connect_error();
}
?>