<?php
require_once "config/config.php";
require_once "config/database.php";
require_once "config/auth.php";

// Requiere autenticación
Auth::require();

$db = Database::conectar();

// Filtrar pedidos según el rol
$whereDomicilio = '';
$whereDomicilio1 = '';
if (Auth::isDelivery()) {
    // Delivery solo ve pedidos a domicilio
    $whereDomicilio = " AND p.domicilio = 'S'";
    $whereDomicilio1 = " AND d.domicilio = 'S'";}

// Consulta preparada para pedidos pendientes
$pendientes = $db->query("
    SELECT 
        p.idpedidos,
        p.hora,
        p.cobrado,
        COALESCE(c.telefono1, '-') AS telefono1,
        COALESCE(p.direccion, '-') AS direccion,
        c.nombre, 
        p.domicilio
    FROM pedidos p
    LEFT JOIN clientes c ON c.idclientes = p.idclientes
    WHERE (p.servido IS NULL OR p.servido != 'S') $whereDomicilio
    ORDER BY p.fecha, p.hora
");

// Consulta para pedidos del día
$diarios = $db->query("
    SELECT 
        d.idpedidos,
        d.hora,
        d.cobrado,
        d.servido,
        COALESCE(cl.telefono1, '-') AS telefono1,
        COALESCE(cl.direccion, '-') AS direccion,
        cl.nombre, 
        d.domicilio
    FROM pedidos d
    LEFT JOIN clientes cl ON cl.idclientes = d.idclientes
    WHERE d.fecha = CURDATE() AND d.servido = 'S' $whereDomicilio1
    ORDER BY d.fecha, d.hora
");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RapidGest · Inicio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container-fluid p-3">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Pizzeria La Fuente · Panel principal</h2>
    <div>
        <span class="me-3">
            👤 <?= htmlspecialchars(Auth::getNombreCompleto()) ?>
            <?php if (Auth::isAdmin()): ?>
                <span class="badge bg-danger">Admin</span>
            <?php elseif (Auth::isCajero()): ?>
                <span class="badge bg-primary">Cajero</span>
            <?php elseif (Auth::isDelivery()): ?>
                <span class="badge bg-info">Delivery</span>
            <?php endif; ?>
        </span>
        <a href="logout.php" class="btn btn-sm btn-outline-danger">Cerrar sesión</a>
    </div>
</div>

<div class="row">

<!-- MANTENIMIENTO -->
<div class="col-md-4">
    <?php if (Auth::isAdmin()): ?>
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">Mantenimiento</div>
        <div class="card-body d-grid gap-2">
            <a href="admin/familias.php" class="btn btn-outline-primary">Familias</a>
            <a href="admin/articulos.php" class="btn btn-outline-primary">Artículos</a>
            <a href="admin/clientes.php" class="btn btn-outline-primary">Clientes</a>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!Auth::isDelivery()): ?>
    <button type="button"
            class="btn btn-primary btn-lg w-100 mb-2"
            onclick="abrirNuevoPedido()">
        🆕 NUEVO PEDIDO
    </button>
    <?php endif; ?>
    
    <?php if (Auth::isAdmin()): ?>
    <a href="ajax/resumenfechas.php"
       class="btn btn-warning btn-lg w-100 mb-2">
        📊 RESUMEN DE PEDIDOS
    </a>
    
    <a href="caja/resumen.php"
       class="btn btn-info btn-lg w-100 mb-2">
        💰 CIERRE DE CAJA
    </a>
    <?php endif; ?>
</div>

<!-- PEDIDOS PENDIENTES -->
<div class="col-md-8">
    <div class="card mb-3">
        <div class="card-header bg-warning">
            ⏳ Pedidos pendientes de servir
        </div>
        <div class="card-body">

            <?php if ($pendientes->num_rows == 0): ?>
                 <div class="alert alert-success">
                    ✅ No hay pedidos pendientes
                 </div>
            <?php endif; ?>
            
            <?php while ($p = $pendientes->fetch_object()): ?>
                <div class="border p-3 mb-2 rounded d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="fs-5">Pedido #<?= $p->idpedidos ?></strong><br>
                        🕐 <?= htmlspecialchars($p->hora) ?><br>
                        📞 <?= htmlspecialchars($p->telefono1) ?> - <?= htmlspecialchars($p->nombre) ?><br>
                        🏠 <?= htmlspecialchars($p->direccion) ?>
                        
                        <?php if ($p->domicilio === 'S'): ?>
                            <span class="badge bg-info">🚚 Domicilio</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">🏪 Local</span>
                        <?php endif; ?>
                        
                        <?php if ($p->cobrado === 'S'): ?>
                            <span class="badge bg-success">✅ COBRADO</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">⏳ PENDIENTE COBRO</span>
                        <?php endif; ?>
                   </div>
                   <div class="text-end">
                        <a href="tpv.php?pedido=<?= $p->idpedidos ?>"
                            class="btn btn-primary">
                            Abrir en TPV →
                        </a>
                   </div>
                </div>
            <?php endwhile; ?>

        </div>
    </div>

    <!-- PEDIDOS DEL DÍA -->
    <div class="card">
        <div class="card-header bg-success text-white">
            📅 Pedidos del día completados
        </div>
        <div class="card-body">

        <?php if ($diarios->num_rows == 0): ?>
            <div class="alert alert-info">
                ℹ️ No hay pedidos completados hoy
            </div>
        <?php endif; ?>
        
        <?php while ($d = $diarios->fetch_object()): ?>
            <div class="border p-3 mb-2 rounded d-flex justify-content-between align-items-center bg-light">
                <div>
                    <strong>Pedido #<?= $d->idpedidos ?></strong><br>
                    🕐 <?= htmlspecialchars($d->hora) ?><br>
                    📞 <?= htmlspecialchars($d->telefono1) ?> - <?= htmlspecialchars($d->nombre) ?><br>
                    🏠 <?= htmlspecialchars($d->direccion) ?>
                
                    <?php if ($d->domicilio === 'S'): ?>
                        <span class="badge bg-info">🚚 Domicilio</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">🏪 Local</span>
                    <?php endif; ?>
                    
                    <span class="badge bg-success">✅ SERVIDO</span>
                    
                    <?php if ($d->cobrado === 'S'): ?>
                        <span class="badge bg-success">💰 COBRADO</span>
                    <?php endif; ?>
               </div>
               <div class="text-end">
                    <a href="tpv.php?pedido=<?= $d->idpedidos ?>"
                       class="btn btn-sm btn-outline-primary">
                        Ver detalles
                    </a>
                </div>
            </div>
        <?php endwhile; ?>

        </div>
    </div>
</div>

</div>

<!-- MODAL NUEVO PEDIDO -->
<div class="modal fade" id="modalCliente" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">🆕 Nuevo pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <label class="form-label">Teléfono *</label>
        <input type="tel" id="telefono" class="form-control mb-3" 
               placeholder="Ej: 612345678" required>

        <label class="form-label">Nombre *</label>
        <input type="text" id="nombre" class="form-control mb-3" 
               placeholder="Nombre del cliente" required>

        <label class="form-label">Dirección</label>
        <input type="text" id="direccion" class="form-control"
               placeholder="Calle, número, piso...">
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success" onclick="crearPedido()">
          ✅ Crear pedido
        </button>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="public/js/index.js"></script>

</body>
</html>