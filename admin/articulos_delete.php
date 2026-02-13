<?php
require_once "../config/database.php";
require_once "../config/auth.php";

Auth::require();

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: articulos.php?error=invalid");
    exit;
}

try {
    
    // Eliminar articulo
    $stmt = Database::execute(
        "DELETE FROM articulos WHERE idarticulos = ?",
        "i",
        [$id]
    );
    
    if (!$stmt) {
        throw new Exception("Error al eliminar");
    }
    
    $stmt->close();
    header("Location: articulos.php?success=deleted");
    
} catch (Exception $e) {
    error_log("Error en articulos_delete.php: " . $e->getMessage());
    header("Location: articulos.php?error=delete");
}
