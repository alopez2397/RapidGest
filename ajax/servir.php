<?php
session_start();
require_once "../config/database.php";

$db = Database::conectar();

$id = $_SESSION['pedido_id'] ?? 0;
if (!$id) exit;

$db->query("UPDATE pedidos SET servido='S' WHERE idpedidos=$id");

unset($_SESSION['pedido_id']);

echo json_encode(["ok" => true]);
