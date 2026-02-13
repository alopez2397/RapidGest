<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/config.php";

// Requiere autenticaci?n
Auth::require();

// Validar datos recibidos
$telefono = trim($_POST['telefono'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

if (empty($telefono)) {
    echo json_encode(["ok" => false, "error" => "El tel?fono es obligatorio"]);
    exit;
}

if (empty($nombre)) {
    echo json_encode(["ok" => false, "error" => "El nombre es obligatorio"]);
    exit;
}

$db = Database::conectar();

try {
    // Buscar si el cliente ya existe
    $res = $db->query("
        SELECT idclientes
        FROM clientes
        WHERE telefono1 = '".$db->real_escape_string($telefono)."'
        LIMIT 1
    ");
    if ($res && $res->num_rows > 0) {
        $idcliente = $res->fetch_object()->idclientes;
        // Actualizar datos del cliente si han cambiado
        Database::execute(
            "UPDATE clientes SET nombre = ?, direccion = ? WHERE idclientes = ?",
            "ssi",
            [$nombre, $direccion, $idcliente]
        );
    } else {
        // Crear nuevo cliente
        $stmt = Database::execute(
            "INSERT INTO clientes (telefono1, nombre, direccion) VALUES (?, ?, ?)",
            "sss",
            [$telefono, $nombre, $direccion]
        );
        
        if (!$stmt) {
            throw new Exception("Error al crear cliente");
        }
        
        $idcliente = $db->insert_id;
        $stmt->close();
    }

    // Crear pedido
    $stmt = Database::execute(
        "INSERT INTO pedidos 
         (fecha, hora, idclientes, direccion, empleado, domicilio, cobrado, servido) 
         VALUES (CURDATE(), ADDTIME(CURTIME(), '09:00:00'), ?, ?, 'Pizzeria', 'N', 'N', 'N')",
        "is",
        [$idcliente, $direccion]
    );
    
    if (!$stmt) {
        throw new Exception("Error al crear pedido");
    }
    
    $_SESSION['pedido_id'] = $db->insert_id;
    $stmt->close();
    
    echo json_encode([
        "ok" => true,
        "pedido_id" => $_SESSION['pedido_id']
    ]);
    
} catch (Exception $e) {
    error_log("Error en crearpedido.php: " . $e->getMessage());
    echo json_encode([
        "ok" => false,
        "error" => "Error al crear el pedido. Int?ntalo de nuevo. crearpedido.php" . $e->getMessage()
    ]);
}
