<?php
require_once "../config/database.php";
$db = Database::conectar();

$res = $db->query("SELECT DATABASE() db");
var_dump($res->fetch_object());
$res = $db->query("SELECT COUNT(*) total FROM clientes");
var_dump($res->fetch_object());
exit;
$res = $db->query("
    SELECT telefono1, HEX(telefono1) hex
    FROM clientes
");
while ($c = $res->fetch_object()) {
    echo $c->telefono1 . " → " . $c->hex . "<br>";
}
exit;
?>