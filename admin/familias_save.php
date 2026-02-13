<?php
require_once "../config/database.php";
require_once "../config/auth.php";

Auth::require();

$id = intval($_POST['id'] ?? 0);
$familia = trim($_POST['familia'] ?? '');

// Validación
if (empty($familia)) {
    header("Location: familias_form.php?id=$id&error=empty");
    exit;
}

$db = Database::conectar();

try {
    if ($id > 0) {
        // Actualizar
        $stmt = Database::execute(
            "UPDATE familias SET familia = ? WHERE idfamilias = ?",
            "si",
            [$familia, $id]
        );
        
        if (!$stmt) {
            throw new Exception("Error al actualizar");
        }
        
    } else {
        // Insertar
        $stmt = Database::execute(
            "INSERT INTO familias (familia) VALUES (?)",
            "s",
            [$familia]
        );
        
        if (!$stmt) {
            throw new Exception("Error al crear");
        }
    }
    
    $stmt->close();
    header("Location: familias.php?success=1");
    
} catch (Exception $e) {
    error_log("Error en familias_save.php: " . $e->getMessage());
    header("Location: familias_form.php?id=$id&error=save");
}
