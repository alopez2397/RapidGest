<?php
/**
 * Clase Database con seguridad mejorada
 * - Usa consultas preparadas
 * - Manejo de errores
 * - Configuración desde archivo .env (recomendado)
 */

class Database {
    private static $instance = null;
    private $conn;
    
    // Configuración (MOVER A .env EN PRODUCCIÓN)
    private const DB_HOST = "sql311.byetcluster.com";
    private const DB_USER = "if0_41065676";
    private const DB_PASS = "8915452Ajll";
    private const DB_NAME = "if0_41065676_rapidgest";
    
    private function __construct() {
        try {
            $this->conn = new mysqli(
                self::DB_HOST,
                self::DB_USER,
                self::DB_PASS,
                self::DB_NAME,
            );
            
            if ($this->conn->connect_error) {
                throw new Exception("Error de conexión: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            die("Error al conectar con la base de datos. Contacte al administrador.");
        }
    }
    
    /**
     * Obtiene la instancia única de la conexión (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtiene la conexión mysqli
     */
    public static function conectar() {
        return self::getInstance()->conn;
    }
    
    /**
     * Ejecuta una consulta preparada
     * @param string $sql Consulta SQL con placeholders (?)
     * @param string $types Tipos de datos (i=int, s=string, d=double)
     * @param array $params Parámetros
     * @return mysqli_stmt|false
     */
    public static function execute($sql, $types = '', $params = []) {
        $conn = self::conectar();
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Error en prepare: " . $conn->error);
            return false;
        }
        
        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            error_log("Error en execute: " . $stmt->error);
            return false;
        }
        
        return $stmt;
    }
    
    /**
     * Escapa una cadena (usar solo cuando no sea posible usar preparadas)
     */
    public static function escape($value) {
        return self::conectar()->real_escape_string($value);
    }
    
    // Prevenir clonación
    private function __clone() {}
    
    // Prevenir deserialización
    public function __wakeup() {
        throw new Exception("No se puede deserializar un Singleton");
    }
}
