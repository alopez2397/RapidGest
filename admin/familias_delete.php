<?php
require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/config.php";

Auth::require();

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: familias.php?error=invalid");
    exit;
}

try {
    // Verificar si hay artículos asociados
    $stmt = Database::execute(
        "SELECT COUNT(*) as total FROM articulos WHERE familia = ?",
        "i",
        [$id]
    );
    
    $result = $stmt->get_result()->fetch_object();
    $stmt->close();
    
    if ($result->total > 0) {
        header("Location: familias.php?error=hasarticles&count=" . $result->total);
        exit;
    }
    
    // Eliminar familia
    $stmt = Database::execute(
        "DELETE FROM familias WHERE idfamilias = ?",
        "i",
        [$id]
    );
    
    if (!$stmt) {
        throw new Exception("Error al eliminar");
    }
    
    $stmt->close();
    header("Location: familias.php?success=deleted");
    
} catch (Exception $e) {
    error_log("Error en familias_delete.php: " . $e->getMessage());
    header("Location: familias.php?error=delete");
}
