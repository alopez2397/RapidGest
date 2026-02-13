<?php
/**
 * Sistema de autenticación y manejo de sesiones
 * Con soporte para roles: admin, cajero, delivery
 */

// Cargar configuración si no está cargada
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/config.php';
}

// Cargar database si no está cargada
if (!class_exists('Database')) {
    require_once __DIR__ . '/database.php';
}

class Auth {
    
    /**
     * Inicia sesión de forma segura
     */
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            @ini_set('session.cookie_httponly', '1');
            @ini_set('session.use_only_cookies', '1');
            @ini_set('session.cookie_secure', '0'); // Cambiar a 1 si usas HTTPS
            session_start();
        }
    }
    
    /**
     * Verifica si hay una sesión activa
     */
    public static function check() {
        self::startSession();
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }
    
    /**
     * Requiere autenticación (redirige si no está logueado)
     */
    public static function require() {
        if (!self::check()) {
            redirect('login.php');
        }
    }
    
    /**
     * Requiere un rol específico
     * @param array $roles Array de roles permitidos ['admin', 'cajero']
     */
    public static function requireRole($roles = []) {
        self::require();
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        $userRole = self::getRole();
        
        if (!in_array($userRole, $roles)) {
            redirect('index.php?error=access_denied');
        }
    }
    
    /**
     * Intenta hacer login contra la base de datos
     */
    public static function login($username, $password) {
        try {
            $db = Database::conectar();
            
            $stmt = Database::execute(
                "SELECT id, username, password_hash, nombre_completo, rol, activo 
                 FROM usuarios 
                 WHERE username = ? AND activo = 1
                 LIMIT 1",
                "s",
                [$username]
            );
            $result = $stmt->get_result();
           
            if (!$result || $result->num_rows == 0) {
                return false;
            }
            
            $user = $result->fetch_object();
            $stmt->close();
            
            // Verificar contraseña
            if (!password_verify($password, $user->password_hash)) {
                return false;
            }
            
            // Login exitoso
            self::startSession();
            $_SESSION['authenticated'] = true;
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['nombre_completo'] = $user->nombre_completo;
            $_SESSION['rol'] = $user->rol;
            
            // Actualizar último acceso
            Database::execute(
                "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?",
                "i",
                [$user->id]
            );
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cierra sesión
     */
    public static function logout() {
        self::startSession();
        $_SESSION = [];
        session_destroy();
    }
    
    /**
     * Obtiene el ID del usuario actual
     */
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Obtiene el nombre del usuario actual
     */
    public static function getUsername() {
        return $_SESSION['username'] ?? 'Invitado';
    }
    
    /**
     * Obtiene el nombre completo del usuario actual
     */
    public static function getNombreCompleto() {
        return $_SESSION['nombre_completo'] ?? $_SESSION['username'] ?? 'Invitado';
    }
    
    /**
     * Obtiene el rol del usuario actual
     */
    public static function getRole() {
        return $_SESSION['rol'] ?? null;
    }
    
    /**
     * Verifica si el usuario es admin
     */
    public static function isAdmin() {
        return self::getRole() === 'admin';
    }
    
    /**
     * Verifica si el usuario es cajero
     */
    public static function isCajero() {
        return self::getRole() === 'cajero';
    }
    
    /**
     * Verifica si el usuario es delivery
     */
    public static function isDelivery() {
        return self::getRole() === 'delivery';
    }
    
    /**
     * Verifica si el usuario tiene uno de los roles especificados
     */
    public static function hasRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        return in_array(self::getRole(), $roles);
    }
}