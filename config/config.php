<?php
/**
 * Configuración global del sistema
 * Define rutas base y URLs
 */

// Detectar si estamos en HTTPS
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
    || $_SERVER['SERVER_PORT'] == 443;

// Protocolo
define('PROTOCOL', $is_https ? 'https://' : 'http://');

// Host con puerto si no es estándar
$port = $_SERVER['SERVER_PORT'];
$host = $_SERVER['HTTP_HOST'];

// Si HTTP_HOST ya incluye el puerto, usarlo directamente
// Si no, añadir el puerto si no es 80 (HTTP) o 443 (HTTPS)
if (strpos($host, ':') === false) {
    if (($is_https && $port != 443) || (!$is_https && $port != 80)) {
        $host .= ':' . $port;
    }
}

define('HOST', $host);

// Ruta base del proyecto (detectada automáticamente)
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname($script_name);

// Si estamos en la raíz, base_path será '/'
// Si estamos en subcarpeta, será '/rapidgest_corregido'
if ($base_path === '/' || $base_path === '\\') {
    $base_path = '';
}

define('BASE_PATH', $base_path);

// URL completa base
define('BASE_URL', PROTOCOL . HOST . BASE_PATH);

// Función helper para generar URLs correctas
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

// Función helper para redirecciones
function redirect($path = '') {
    header('Location: ' . url($path));
    exit;
}

// Debug (comentar en producción)
// echo "BASE_URL: " . BASE_URL . "<br>";
// echo "Ejemplo url('login.php'): " . url('login.php') . "<br>";