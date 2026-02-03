<?php
header('Content-Type: application/json; charset=utf-8');

require_once "../config/database.php";
$db = Database::conectar();

$telefono = isset($_GET['telefono']) ? trim($_GET['telefono']) : '';

if ($telefono === '') {
    echo json_encode(null);
    exit;
}

$res = $db->query("
    SELECT nombre, direccion
    FROM clientes
    WHERE telefono1 = '".$db->real_escape_string($telefono)."'
    LIMIT 1
");

if ($res && $res->num_rows > 0) {
    echo json_encode($res->fetch_object());
} else {
    echo json_encode(null);
}
