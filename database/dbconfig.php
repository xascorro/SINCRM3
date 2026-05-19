<?php
/**
 * Configuración de la Base de Datos Dinámica para Docker/Coolify
 */

// Intentar obtener configuración de variables de entorno (Docker)
$env_host = getenv('DB_HOST');
$env_user = getenv('DB_USER');
$env_pass = getenv('DB_PASS');
$env_name = getenv('DB_NAME');

if ($env_host) {
    // Estamos en Docker / Coolify
    $servername  = $env_host;
    $db_username = $env_user;
    $db_password = $env_pass;
    $db_name     = $env_name;
} else {
    // Fallback: Configuración clásica basada en dominio
    $prod_domains = ['sincrm.pedrodiaz.eu', 'beta.pedrodiaz.eu', '79.72.31.184'];
    
    if (in_array($_SERVER['SERVER_NAME'] ?? '', $prod_domains)) {
        $servername  = 'localhost';
        $db_username = 'xas';
        $db_password = '79eagle';
        $db_name     = (($_SERVER['SERVER_NAME'] ?? '') == 'beta.pedrodiaz.eu') ? 'sincrm4beta' : 'sincrm4';
    } else {
        $servername  = 'localhost';
        $db_name     = 'sincrm4';
        $db_username = 'root';
        $db_password = 'xas';
    }
}

// Configuración de Zona Horaria Global
date_default_timezone_set('Europe/Madrid');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain');

// Establecer conexión
$connection = mysqli_connect($servername, $db_username, $db_password, $db_name);

if ($connection) {
    mysqli_set_charset($connection, "utf8mb4");
    $dbconfig = true;
    $mysqli = new mysqli($servername, $db_username, $db_password, $db_name);
    $mysqli->set_charset("utf8mb4");
    mysqli_query($connection, "SET time_zone = '+02:00'");
    $mysqli->query("SET time_zone = '+02:00'");
} else {
    $dbconfig = false;
    if (php_sapi_name() !== 'cli') {
        echo 'Error de conexión a la base de datos.';
    }
}

if (!function_exists('write_log')) {
    function write_log($message, $level = 'INFO') {
        $log_dir = dirname(dirname(__FILE__)) . '/log';
        $log_file = $log_dir . '/log.txt';
        $timestamp = date('Y-m-d H:i:s');
        $user = $_SESSION['username'] ?? 'SISTEMA';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; 
        $log_entry = "[$timestamp] [$level] [$user] [$ip] $message" . PHP_EOL;
        @file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
}
?>
