<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once "../config/database.php";

$db = Database::conectar();

/* ───────────────── VALIDACIONES BÁSICAS ───────────────── */

if (!isset($_SESSION['pedido_id'])) {
    echo json_encode([
        "ok" => false,
        "error" => "Pedido no válido"
    ]);
    exit;
}

$pedido_id = intval($_SESSION['pedido_id']);

$check = $db->query("
    SELECT cobrado
    FROM pedidos
    WHERE idpedidos = $pedido_id
    LIMIT 1
");

if (!$check || $check->num_rows == 0) {
    echo json_encode([
        "ok" => false,
        "error" => "Pedido inexistente"
    ]);
    exit;
}

$estado = $check->fetch_object();

if ($estado->cobrado === 'S') {
    echo json_encode([
        "ok" => false,
        "error" => "Este pedido ya está cobrado"
    ]);
    exit;
}

/* ───────────────── IMPORTE RECIBIDO (NORMALIZADO) ───────────────── */

$recibido_raw = $_POST['recibido'] ?? '0';
$recibido_raw = trim($recibido_raw);
$recibido_raw = str_replace(',', '.', $recibido_raw);

$recibido = round(floatval($recibido_raw), 2);

if ($recibido <= 0) {
    echo json_encode([
        "ok" => false,
        "error" => "Importe recibido no válido"
    ]);
    exit;
}

/* ───────────────── CALCULAR TOTAL PEDIDO ───────────────── */

$res = $db->query("
    SELECT SUM(cantidad * pvp) AS total
    FROM lineaspedido
    WHERE idpedidos = $pedido_id
");

if (!$res) {
    echo json_encode([
        "ok" => false,
        "error" => "Error SQL: " . $db->error
    ]);
    exit;
}

$row = $res->fetch_object();
$total = round(floatval($row->total), 2);

if ($total <= 0) {
    echo json_encode([
        "ok" => false,
        "error" => "El pedido no tiene líneas"
    ]);
    exit;
}

/* ───────────────── VALIDAR IMPORTE ───────────────── */

$cambio = round($recibido - $total, 2);

/* Protección contra errores de coma flotante */
if ($recibido + 0.001 < $total) {
    echo json_encode([
        "ok" => false,
        "error" => "Importe insuficiente"
    ]);
    exit;
}

/* ───────────────── MARCAR PEDIDO COBRADO Y SERVIDO ───────────────── */

$ok = $db->query("
    UPDATE pedidos
    SET 
        total        = $total,
        pagado       = $recibido,
        cambio       = $cambio,
        fecha_cobro  = ADDTIME(NOW(), '09:00:00'),
        cobrado      = 'S'
    WHERE idpedidos = $pedido_id
");

if (!$ok) {
    echo json_encode([
        "ok" => false,
        "error" => "Error al guardar el cobro"
    ]);
    exit;
}

/* ───────────────── CERRAR PEDIDO EN SESIÓN ───────────────── */

unset($_SESSION['pedido_id']);

/* ───────────────── RESPUESTA FINAL ───────────────── */

echo json_encode([
    "ok"      => true,
    "total"   => number_format($total, 2),
    "pagado"  => number_format($recibido, 2),
    "cambio"  => number_format($cambio, 2) 
]);
