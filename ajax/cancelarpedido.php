<?php
session_start();
require_once "../config/database.php";

$db = Database::conectar();

if (!isset($_SESSION['pedido_id'])) {
    echo json_encode(["ok" => false]);
    exit;
}

$id = $_SESSION['pedido_id'];

/* Elimina líneas por FK ON DELETE CASCADE */
$db->query("DELETE FROM pedidos WHERE idpedidos=$id");

unset($_SESSION['pedido_id']);

echo json_encode(["ok" => true]);
