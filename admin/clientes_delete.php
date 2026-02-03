<?php
require_once "../config/database.php";
$db = Database::conectar();

$id = intval($_GET['id']);
$db->query("DELETE FROM clientes WHERE idclientes=$id");

header("Location: clientes.php");
