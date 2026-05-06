<?php
/**
 * Configuración de la Base de Datos
 * Gestiona la conexión para entornos de producción, beta y local.
 */

// Dominios de producción/beta
$prod_domains = ['sincrm.pedrodiaz.eu', 'beta.pedrodiaz.eu'];

// Configuración de Zona Horaria Global
date_default_timezone_set('Europe/Madrid');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain');

if (in_array($_SERVER['SERVER_NAME'] ?? '', $prod_domains)) {
    // Entorno Producción / Beta
    $servername  = 'localhost';
    $db_name     = 'sincrm3';
    $db_username = 'xas';
    $db_password = '79eagle';
} else {
    // Entorno Local / Desarrollo
    $servername  = 'localhost';
    $db_name     = 'sincrm3';
    $db_username = 'root';
    $db_password = 'xas';
}

// Establecer conexión (Procedural y POO para compatibilidad)
$connection = mysqli_connect($servername, $db_username, $db_password, $db_name);

if ($connection) {
    mysqli_set_charset($connection, "utf8mb4");
    $dbconfig = true;
    
    // Objeto MySQLi para código moderno
    $mysqli = new mysqli($servername, $db_username, $db_password, $db_name);
    $mysqli->set_charset("utf8mb4");

    // Sincronizar zona horaria con la base de datos
    mysqli_query($connection, "SET time_zone = '+02:00'"); // Ajuste para horario de verano en Mayo en España
    $mysqli->query("SET time_zone = '+02:00'");
} else {
    $dbconfig = false;
    // Mostrar error amigable si falla la conexión
    if (php_sapi_name() !== 'cli') { // Evitar salida HTML en scripts de consola
        echo '
        <div class="container" style="margin-top: 50px; font-family: sans-serif;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #dc3545; border-radius: 5px; text-align: center; background-color: #f8d7da;">
                <h1 style="color: #721c24;">Error de conexión</h1>
                <p style="color: #721c24;">No se ha podido establecer conexión con la base de datos.</p>
                <p>Por favor, comprueba la configuración o contacta con el administrador.</p>
                <a href="./db_setup.php" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">Revisar Configuración</a>
            </div>
        </div>';
    }
}

/**
 * Sistema de Log Profesional - Disponible Globalmente
 */
if (!function_exists('write_log')) {
    function write_log($message, $level = 'INFO') {
        $log_dir = dirname(dirname(__FILE__)) . '/log';
        $log_file = $log_dir . '/log.txt';
        $timestamp = date('Y-m-d H:i:s');
        
        $user = isset($_SESSION['username']) ? $_SESSION['username'] : 'SISTEMA';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; 
        $sid = session_id() ?: 'no-session';
        
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }
        
        $log_entry = "[$timestamp] [$level] [$user] [$ip] [$sid] $message" . PHP_EOL;
        @file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
}
?>
