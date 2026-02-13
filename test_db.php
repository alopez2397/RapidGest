<?php
// TEST DE CONEXIÓN A BASE DE DATOS
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de Conexión</h1>";

try {
    $db = new mysqli(
        "94.76.251.66",
        "Administrador",
        "8915452@jll",
        "rapidgest",
        3308
    );
    
    if ($db->connect_error) {
        throw new Exception("Error de conexión: " . $db->connect_error);
    }
    
    echo "<p style='color: green;'>✅ Conexión exitosa a MySQL</p>";
    echo "<p>Versión MySQL: " . $db->server_info . "</p>";
    
    $db->set_charset("utf8mb4");
    echo "<p style='color: green;'>✅ Charset configurado: utf8mb4</p>";
    
    // Probar una consulta simple
    $result = $db->query("SHOW TABLES");
    if ($result) {
        echo "<p style='color: green;'>✅ Consulta exitosa</p>";
        echo "<h3>Tablas encontradas:</h3><ul>";
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>