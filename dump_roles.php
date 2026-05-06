<?php
include 'database/dbconfig.php';
$res = mysqli_query($connection, 'SELECT * FROM roles');
while($row = mysqli_fetch_assoc($res)) {
    echo $row['id'] . " - " . $row['nombre'] . " (Level: " . $row['level'] . ")\n";
}
?>