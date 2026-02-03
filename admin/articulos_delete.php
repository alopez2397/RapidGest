<?php
require_once "../config/database.php";
$db = Database::conectar();

$id = intval($_GET['id']);
$db->query("DELETE FROM articulos WHERE idarticulos=$id");

header("Location: articulos.php");
