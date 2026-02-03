<?php
require_once "../config/database.php";
$db = Database::conectar();

$id = intval($_GET['id']);

$db->query("DELETE FROM familias WHERE idfamilias=$id");

header("Location: familias.php");
