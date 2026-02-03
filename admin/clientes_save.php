<?php
require_once "../config/database.php";
$db = Database::conectar();

$id = $_POST['id'];
$nombre = $db->real_escape_string($_POST['nombre']);
$telefono1 = $db->real_escape_string($_POST['telefono1']);
$direccion = $db->real_escape_string($_POST['direccion']);
$email = $db->real_escape_string($_POST['email']);

if ($id) {
    $db->query("
        UPDATE clientes
        SET nombre='$nombre',
            telefono1='$telefono1',
            direccion='$direccion',
            email='$email'
        WHERE idclientes=$id
    ");
} else {
    $db->query("
        INSERT INTO clientes (nombre, telefono1, direccion, email)
        VALUES ('$nombre','$telefono1','$direccion','$email')
    ");
}

header("Location: clientes.php");
