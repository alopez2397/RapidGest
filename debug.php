<?php
// INDEX.PHP - VERSIÓN DE DEBUGGING
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Debug</title></head><body>";
echo "<h1>🔍 Debug de RapidGest</h1>";

// PASO 1: Verificar PHP
echo "<h2>Paso 1: PHP</h2>";
echo "<p style='color: green;'>✅ PHP funcionando - Versión: " . PHP_VERSION . "</p>";

// PASO 2: Verificar archivos config
echo "<h2>Paso 2: Archivos de configuración</h2>";

$archivos = [
    'config/database.php',
    'config/auth.php'
];

foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "<p style='color: green;'>✅ $archivo existe</p>";
    } else {
        echo "<p style='color: red;'>❌ $archivo NO existe</p>";
    }
}

// PASO 3: Intentar cargar database.php
echo "<h2>Paso 3: Cargar Database</h2>";
try {
    require_once "config/database.php";
    echo "<p style='color: green;'>✅ database.php cargado</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "</body></html>";
    exit;
}

// PASO 4: Intentar conectar
echo "<h2>Paso 4: Conexión a BD</h2>";
try {
    $db = Database::conectar();
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "</body></html>";
    exit;
}

// PASO 5: Consulta simple
echo "<h2>Paso 5: Consulta de prueba</h2>";
try {
    $result = $db->query("SELECT COUNT(*) as total FROM pedidos");
    if ($result) {
        $row = $result->fetch_object();
        echo "<p style='color: green;'>✅ Consulta exitosa - Total pedidos: " . $row->total . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>✅ Todo OK - El sistema puede funcionar</h2>";
echo "<p><a href='login.php' style='font-size: 20px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>IR AL LOGIN</a></p>";

echo "</body></html>";
?>