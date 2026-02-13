<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/config.php";

// Requiere autenticación
Auth::require();

$pedido_id = intval($_SESSION['pedido_id'] ?? 0);

if (!$pedido_id) {
    echo json_encode(["lineas" => "", "total" => "0.00"]);
    exit;
}

$db = Database::conectar();

try {
    $lineas = $db->query("
        SELECT idlineasPedido, articulo, cantidad, pvp
        FROM lineaspedido
        WHERE idpedidos = $pedido_id
        ORDER BY idlineasPedido
    ");
    
    if (!$lineas) {
        throw new Exception("Error al cargar líneas");
    }
    
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
        "lineas" => $html,
        "total" => number_format($total, 2)
    ]);
    
} catch (Exception $e) {
    error_log("Error en cargarpedido.php: " . $e->getMessage());
    echo json_encode(["lineas" => "", "total" => "0.00"]);
}
