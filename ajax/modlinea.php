<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/config.php";

// Requiere autenticación
Auth::require();

$db = Database::conectar();

$id = intval($_POST['id'] ?? 0);
$accion = $_POST['accion'] ?? '';
$pedido_id = intval($_SESSION['pedido_id'] ?? 0);

if (!$id || !in_array($accion, ['sumar', 'restar']) || !$pedido_id) {
    echo json_encode([
        "ok" => false,
        "error" => "Datos inválidos"
    ]);
    exit;
}

try {
    // Verificar que la línea pertenece al pedido en sesión
    $stmt = Database::execute(
        "SELECT idlineasPedido FROM lineaspedido 
         WHERE idlineasPedido = ? AND idpedidos = ?",
        "ii",
        [$id, $pedido_id]
    );
    $stmt->close();
    
    // Verificar que el pedido no está cobrado
    $stmt = Database::execute(
        "SELECT cobrado FROM pedidos WHERE idpedidos = ?",
        "i",
        [$pedido_id]
    );
    
    $pedido = $stmt->get_result()->fetch_object();
    $stmt->close();
    
    if ($pedido->cobrado === 'S') {
        echo json_encode([
            "ok" => false,
            "error" => "No se puede modificar un pedido cobrado"
        ]);
        exit;
    }
    
    // Ejecutar acción
    if ($accion === "sumar") {
        Database::execute(
            "UPDATE lineaspedido SET cantidad = cantidad + 1 WHERE idlineasPedido = ?",
            "i",
            [$id]
        );
    } else {
        Database::execute(
            "UPDATE lineaspedido SET cantidad = cantidad - 1 WHERE idlineasPedido = ?",
            "i",
            [$id]
        );
        
        // Eliminar si cantidad <= 0
        Database::execute(
            "DELETE FROM lineaspedido WHERE cantidad <= 0",
            "",
            []
        );
    }
    
    // Recargar líneas
    $lineas = $db->query("
        SELECT idlineasPedido, articulo, cantidad, pvp 
        FROM lineaspedido 
        WHERE idpedidos = $pedido_id
        ORDER BY idlineasPedido
    ");
    
    $total = 0;
    $html = "";
    
    while ($l = $lineas->fetch_object()) {
        $lineaTotal = $l->cantidad * $l->pvp;
        $total += $lineaTotal;
        
        $html .= "
        <div class='d-flex justify-content-between align-items-center mb-2 p-2 border rounded'>
            <div class='flex-grow-1'>
                <strong>" . htmlspecialchars($l->articulo) . "</strong><br>
                <small class='text-muted'>" . $l->cantidad . " × " . number_format($l->pvp, 2) . " €</small>
            </div>
            <div class='btn-group btn-group-sm me-2'>
                <button class='btn btn-outline-danger' onclick='restarLinea(" . $l->idlineasPedido . ")'>−</button>
                <button class='btn btn-outline-success' onclick='sumarLinea(" . $l->idlineasPedido . ")'>+</button>
            </div>
            <div class='fw-bold text-end' style='min-width: 70px;'>
                " . number_format($lineaTotal, 2) . " €
            </div>
        </div>";
    }
    
    echo json_encode([
        "ok" => true,
        "lineas" => $html,
        "total" => number_format($total, 2)
    ]);
    
} catch (Exception $e) {
    error_log("Error en modlinea.php: " . $e->getMessage());
    echo json_encode([
        "ok" => false,
        "error" => "Error al modificar línea en modlinea " . $e->getMessage() 
    ]);
}
