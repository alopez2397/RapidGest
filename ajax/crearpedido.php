<?php
session_start();
require_once "../config/database.php";
$db = Database::conectar();

$tel = $_POST['telefono'];
$nombre = $_POST['nombre'];
$direccion = $_POST['direccion'];



/* Buscar cliente */
$res = $db->query("
    SELECT idclientes FROM clientes WHERE telefono1='$tel' LIMIT 1
");

if ($res->num_rows) {
    $idcliente = $res->fetch_object()->idclientes;
} else {
    /* Crear cliente */
    $db->query("
        INSERT INTO clientes (telefono1, nombre, direccion)
        VALUES ('$tel', '$nombre', '$direccion')
    ");
    $idcliente = $db->insert_id;
}

/* Crear pedido */

$db->query("
    INSERT INTO pedidos (fecha, hora, idclientes, direccion, empleado, domicilio, cobrado, servido)
    VALUES (CURDATE(), CURTIME(), $idcliente, '$direccion', 'Pizzeria', 'N', 'N', 'N')
");

$_SESSION['pedido_id'] = $db->insert_id;

echo json_encode(["ok" => true]);
