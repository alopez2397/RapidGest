<?php
session_start();
require_once "../config/database.php";

header('Content-Type: application/json');

$db = Database::conectar();

/* Validaciones */
$pedido_id = intval($_POST['pedido_id'] ?? 0);
$articulo  = $db->real_escape_string($_POST['articulo'] ?? '');
$pvp       = floatval($_POST['pvp'] ?? 0);

if (!$pedido_id || !$articulo || $pvp <= 0) {
    echo json_encode([
        "ok" => false,
        "error" => "Datos incompletos"
    ]);
    exit;
}

/* Comprobar pedido */
$chk = $db->query("
    SELECT cobrado FROM pedidos WHERE idpedidos=$pedido_id
")->fetch_object();

if (!$chk || $chk->cobrado === 'S') {
    echo json_encode([
        "ok" => false,
        "error" => "Pedido no válido"
    ]);
    exit;
}

/* Insertar línea */
$db->query("
    INSERT INTO lineaspedido (idpedidos, articulo, pvp, cantidad)
    VALUES ($pedido_id, '$articulo', $pvp, 1)
");

/* Recargar líneas */
$res = $db->query("
    SELECT * FROM lineaspedido
    WHERE idpedidos=$pedido_id
");

$total = 0;
$html = "";

while ($l = $res->fetch_object()) {
    $lineaTotal = $l->cantidad * $l->pvp;
    $total += $lineaTotal;

    $html .= "
    <div class='d-flex justify-content-between align-items-center mb-1'>
        <div>
            <strong>{$l->articulo}</strong><br>
            <small>{$l->cantidad} x ".number_format($l->pvp,2)." €</small>
        </div>
        <div>
            <button class='btn btn-sm btn-outline-secondary'
                onclick='restarLinea({$l->idlineasPedido})'>−</button>
            <button class='btn btn-sm btn-outline-secondary'
                onclick='sumarLinea({$l->idlineasPedido})'>+</button>
        </div>
        <div class='fw-bold'>
            ".number_format($lineaTotal,2)." €
        </div>
    </div>";
}

echo json_encode([
    "ok" => true,
    "lineas" => $html,
    "total" => number_format($total, 2)
]);
