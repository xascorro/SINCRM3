<?php
session_start();
include('database/dbconfig.php');
$db = new dbconfig();
$db->write_log("PRUEBA DE LOG V3.0 - FUNCIONANDO", "SUCCESS");
echo "Log escrito (o al menos intentado). Revisa mantenimiento.php o el archivo log.txt";
?>