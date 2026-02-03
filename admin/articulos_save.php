<?php
require_once "../config/database.php";
$db = Database::conectar();

$id = $_POST['id'];
$articulo = $db->real_escape_string($_POST['articulo']);
$familia = intval($_POST['familia']);
$pvp = floatval($_POST['pvp']);

if ($id) {
    $db->query("
        UPDATE articulos
        SET articulo='$articulo',
            familia='$familia',
            pvp=$pvp
        WHERE idarticulos=$id
    ");
} else {
    $db->query("
        INSERT INTO articulos (articulo, familia, pvp)
        VALUES ('$articulo', '$familia', $pvp)
    ");
}

header("Location: articulos.php");
