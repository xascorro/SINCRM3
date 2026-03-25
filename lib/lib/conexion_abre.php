<?php

// Parametros a configurar para la conexion de la base de datos

$hostdb = "localhost";    // sera el valor de nuestra BD
$basededatos = "sincrm";    // sera el valor de nuestra BD

$usuariodb = "root";    // sera el valor de nuestra BD
$clavedb = "xas";    // sera el valor de nuestra BD

// Fin de los parametros a configurar para la conexion de la base de datos

$conexion_db = @mysql_connect("$hostdb","$usuariodb","$clavedb");

if(!$conexion_db){
	$clavedb = "";    // sera el valor de nuestra BD
	$conexion_db = mysql_connect("$hostdb","$usuariodb","$clavedb");
}
    $db = mysql_select_db("$basededatos", $conexion_db);

mysql_query("SET NAMES 'utf8'");
