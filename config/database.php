<?php
class Database {
    public static function conectar() {
        $db = new mysqli("94.76.251.66", "Administrador", "8915452@jll", "rapidgest", 3308);
        if ($db->connect_error) {
            die("Error de conexión");
        }
        $db->set_charset("utf8");
        return $db;
    }
}
