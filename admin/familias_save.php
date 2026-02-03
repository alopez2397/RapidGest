<?php
require_once "../config/database.php";
$db = Database::conectar();

$id = $_POST['id'];
$familia = $db->real_escape_string($_POST['familia']);

if ($id) {
    $db->query("
        UPDATE familias
        SET familia='$familia'
        WHERE idfamilias=$id
    ");
} else {
    $db->query("
        INSERT INTO familias (familia)
        VALUES ('$familia')
    ");
}

header("Location: familias.php");
