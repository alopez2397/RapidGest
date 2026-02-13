# 🔐 CHECKLIST DE SEGURIDAD - RAPIDGEST

## ✅ ANTES DE DESPLEGAR EN PRODUCCIÓN

### 📋 Configuración Básica

- [ ] **Cambiar credenciales de base de datos**
  - Usuario diferente a 'Administrador'
  - Contraseña fuerte (min 16 caracteres, mayúsculas, minúsculas, números, símbolos)
  - Crear usuario específico con solo permisos necesarios

- [ ] **Cambiar credenciales de login**
  - Modificar usuario en `config/auth.php`
  - Usar contraseña hasheada con `password_hash()`
  - Implementar tabla de usuarios en BD

- [ ] **Configurar archivo .env**
  ```bash
  cp .env.example .env
  chmod 600 .env
  ```
  - Mover credenciales de `database.php` a `.env`
  - Añadir `.env` a `.gitignore`

- [ ] **Activar HTTPS**
  - Obtener certificado SSL (Let's Encrypt gratuito)
  - Configurar redirección HTTP → HTTPS en `.htaccess`
  - Cambiar `session.cookie_secure` a 1 en `auth.php`

### 🛡️ Seguridad de Archivos

- [ ] **Configurar permisos correctamente**
  ```bash
  # Archivos PHP
  find . -type f -name "*.php" -exec chmod 644 {} \;
  
  # Directorios
  find . -type d -exec chmod 755 {} \;
  
  # Carpeta de tickets (escritura)
  chmod 777 tickets/
  
  # Config sensible
  chmod 600 config/database.php
  chmod 600 .env
  ```

- [ ] **Proteger archivos sensibles**
  - Verificar que `.htaccess` bloquea acceso a `.env`, `.git`, logs
  - Mover `config/` fuera del directorio público si es posible

- [ ] **Deshabilitar listado de directorios**
  - Verificado en `.htaccess`: `Options -Indexes`

### 🔒 PHP Security

- [ ] **Configurar php.ini en producción**
  ```ini
  display_errors = Off
  log_errors = On
  error_log = /var/log/php/error.log
  expose_php = Off
  session.cookie_httponly = 1
  session.cookie_secure = 1 (si HTTPS)
  session.use_strict_mode = 1
  allow_url_fopen = Off
  disable_functions = exec,passthru,shell_exec,system,proc_open,popen
  ```

- [ ] **Headers de seguridad**
  - Verificar que `.htaccess` incluye X-Frame-Options, X-XSS-Protection, etc.
  - O añadirlos en PHP si no tienes acceso a .htaccess

- [ ] **Validar todas las entradas**
  - Revisar que todos los inputs usan validación
  - Verificar tipos de datos (intval, floatval, trim)
  - Sanear outputs (htmlspecialchars, addslashes)

### 🗄️ Base de Datos

- [ ] **Usuario de BD con mínimos privilegios**
  ```sql
  CREATE USER 'rapidgest_user'@'localhost' IDENTIFIED BY 'contraseña_fuerte';
  GRANT SELECT, INSERT, UPDATE, DELETE ON rapidgest.* TO 'rapidgest_user'@'localhost';
  FLUSH PRIVILEGES;
  ```

- [ ] **Verificar consultas preparadas**
  - Revisar que TODOS los archivos usan `Database::execute()`
  - No hay concatenación directa de variables en SQL

- [ ] **Configurar backups automáticos**
  ```bash
  # Cron job diario
  0 2 * * * mysqldump -u user -p'pass' rapidgest > /backups/rapidgest_$(date +\%Y\%m\%d).sql
  ```

### 🔑 Autenticación y Sesiones

- [ ] **Implementar usuarios en BD**
  - Ejecutar SQL de creación de tabla `usuarios`
  - Modificar `auth.php` para verificar contra BD
  - Usar `password_hash()` y `password_verify()`

- [ ] **Configurar expiración de sesiones**
  ```php
  ini_set('session.gc_maxlifetime', 3600); // 1 hora
  ini_set('session.cookie_lifetime', 0); // Cierra al cerrar navegador
  ```

- [ ] **Regenerar ID de sesión**
  ```php
  session_regenerate_id(true); // Después de login exitoso
  ```

- [ ] **Proteger contra CSRF**
  - Generar token en formularios
  - Validar token en POST

### 📊 Logging y Monitorización

- [ ] **Configurar logs de aplicación**
  ```php
  // En database.php y archivos críticos
  error_log("Error: " . $e->getMessage());
  ```

- [ ] **Logs de acceso**
  - Activar logs de Apache/Nginx
  - Monitorizar intentos de acceso no autorizados

- [ ] **Configurar rotación de logs**
  ```bash
  # /etc/logrotate.d/rapidgest
  /var/log/rapidgest/*.log {
      daily
      rotate 30
      compress
      delaycompress
      notifempty
      create 644 www-data www-data
  }
  ```

### 🧪 Testing

- [ ] **Tests de seguridad**
  - [ ] SQL Injection (intentar `' OR '1'='1` en formularios)
  - [ ] XSS (`<script>alert('XSS')</script>` en inputs)
  - [ ] CSRF (enviar POST sin token)
  - [ ] Acceso sin autenticación (abrir URLs directamente)
  - [ ] Path traversal (`../../../etc/passwd`)

- [ ] **Tests funcionales**
  - [ ] Crear pedido completo
  - [ ] Modificar cantidades
  - [ ] Cobrar pedido
  - [ ] Cancelar pedido
  - [ ] Imprimir ticket

- [ ] **Tests de rendimiento**
  - [ ] Tiempo de carga < 2 segundos
  - [ ] Múltiples usuarios simultáneos
  - [ ] Base de datos con >1000 pedidos

### 🚨 Plan de Respuesta a Incidentes

- [ ] **Documentar procedimientos**
  - ¿Qué hacer si hay una brecha de seguridad?
  - Contactos de emergencia
  - Proceso de backup y restore

- [ ] **Backup probado**
  - Verificar que los backups se restauran correctamente
  - Tener backup offsite

### 📱 Funcionalidades Opcionales

- [ ] **Rate Limiting**
  - Limitar intentos de login (3-5 fallos = bloqueo temporal)
  - Limitar requests por IP

- [ ] **2FA (Autenticación de dos factores)**
  - Usar Google Authenticator o similar
  - Solo para usuarios admin

- [ ] **Auditoría**
  - Registrar todas las acciones importantes
  - Tabla `auditoria` en BD

### 🌐 Networking

- [ ] **Firewall configurado**
  - Solo puertos 80, 443 abiertos
  - SSH en puerto no estándar
  - Restringir acceso SSH por IP

- [ ] **CDN/WAF (opcional)**
  - Cloudflare gratuito
  - Protección DDoS
  - Cache de recursos estáticos

### 📄 Documentación

- [ ] **Manual de usuario creado**
  - Cómo crear pedidos
  - Cómo cobrar
  - Cómo gestionar artículos

- [ ] **Manual de administrador**
  - Backup y restore
  - Añadir usuarios
  - Interpretar logs

- [ ] **Changelog mantenido**
  - Documentar cambios en cada versión

---

## ⚠️ ERRORES COMUNES A EVITAR

### ❌ NO HACER:
- Mostrar errores de BD al usuario
- Usar `root` para conexión de BD
- Dejar credenciales en código
- Confiar en validación solo frontend
- Permitir upload de archivos sin validación
- Ejecutar comandos del sistema sin sanitizar
- Usar MD5 o SHA1 para passwords
- Almacenar contraseñas en texto plano

### ✅ SÍ HACER:
- Validar en backend SIEMPRE
- Usar HTTPS en producción
- Mantener PHP actualizado
- Revisar logs regularmente
- Hacer backups automatizados
- Usar consultas preparadas
- Password hash con bcrypt/argon2
- Principio de menor privilegio

---

## 🔍 HERRAMIENTAS DE AUDITORÍA

### Online:
- [ ] **SSL Labs** (https://www.ssllabs.com/ssltest/)
  - Verificar certificado SSL

- [ ] **Security Headers** (https://securityheaders.com/)
  - Verificar headers de seguridad

### Local:
- [ ] **OWASP ZAP** o **Burp Suite**
  - Escaneo de vulnerabilidades

- [ ] **SQLMap**
  - Test de SQL injection

- [ ] **Nikto**
  - Escaneo de servidor web

---

## 📊 MÉTRICAS DE SEGURIDAD

Monitorizar:
- Intentos de login fallidos
- Queries lentas en BD
- Uso de disco (logs)
- Tráfico anómalo
- Cambios en archivos del sistema

---

## 📞 CONTACTOS DE EMERGENCIA

- **Hosting:** ___________________
- **DBA:** ___________________
- **Desarrollador:** ___________________
- **Responsable negocio:** ___________________

---

## 📅 REVISIONES PERIÓDICAS

### Mensual:
- [ ] Revisar logs de errores
- [ ] Verificar backups
- [ ] Actualizar dependencias

### Trimestral:
- [ ] Auditoría de seguridad completa
- [ ] Revisar permisos de usuarios
- [ ] Test de restauración de backup

### Anual:
- [ ] Renovar certificado SSL
- [ ] Revisar todo este checklist
- [ ] Actualizar documentación

---

**Última revisión:** _____________
**Responsable:** _____________
**Próxima revisión:** _____________
