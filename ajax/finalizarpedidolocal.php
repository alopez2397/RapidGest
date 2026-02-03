<?php
session_start();
require_once "../config/database.php";

$db = Database::conectar();
$pedido_id = $_SESSION['pedido_id'] ?? null;

if (!$pedido_id) {
    echo json_encode(["ok" => false]);
    exit;
}

/* Marcar como pendiente */
$db->query("
    UPDATE pedidos
    SET servido='N', cobrado='N', domicilio='N'
    WHERE idpedidos=$pedido_id
");

/* Cerrar sesión del pedido */
unset($_SESSION['pedido_id']);

echo json_encode(["ok" => true]);
