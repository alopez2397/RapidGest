<?php
require_once "config/database.php";
$db = Database::conectar();

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
    WHERE p.servido IS NULL OR p.servido != 'S'
    ORDER BY p.fecha, p.hora
");

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
    WHERE d.fecha = CURDATE() and d.servido = 'S'
    ORDER BY d.fecha, d.hora
");

?>
<!DOCTYPE html>
<html>
<head>
    <title>RapidGest · Inicio</title>
    <link rel="stylesheet"
<link rel="stylesheet" href="public/css/bootstrap.min.css">
<script src="public/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">

<div class="container-fluid p-3">

<h2 class="mb-3">Pizzeria La Fuente · Panel principal</h2>

<div class="row">

<!-- MANTENIMIENTO -->
<div class="col-md-4">
    <div class="card mb-3">
        <div class="card-header">Mantenimiento</div>
        <div class="card-body d-grid gap-2">
            <a href="admin/familias.php" class="btn btn-outline-primary">Familias</a>
            <a href="admin/articulos.php" class="btn btn-outline-primary">Artículos</a>
            <a href="admin/clientes.php" class="btn btn-outline-primary">Clientes</a>
        </div>
    </div>

    <button type="button"
            class="btn btn-primary btn-lg w-100"
            onclick="abrirNuevoPedido()">
        NUEVO PEDIDO
    </button>
<a href="ajax/resumenfechas.php"
   class="btn btn-warning btn-lg w-100">
    RESUMEN DE PEDIDOS
</a>
<a href="caja/resumen.php"
   class="btn btn-info btn-lg w-100">
    CIERRE DE CAJA
</a>
</div>

<!-- PEDIDOS PENDIENTES -->
<div class="col-md-8">
    <div class="card">
        <div class="card-header">
            Pedidos pendientes de servir
        </div>
        <div class="card-body">

            <?php if ($pendientes->num_rows == 0): ?>
                 <div class="alert alert-success">
                            No hay pedidos pendientes
                 </div>
            <?php endif; ?>
            <?php while ($p = $pendientes->fetch_object()): ?>
                <div class="border p-2 mb-2 d-flex justify-content-between">
                    <div>
                        <strong>Pedido #<?= $p->idpedidos ?></strong><br>
    		            🕒 <?= $p->hora ?><br>
    		            📞 <?= htmlspecialchars($p->telefono1) ?> <?= $p->nombre ?><br>
		                🏠 <?= htmlspecialchars($p->direccion) ?>
                        <?= $p->domicilio === 'S' ? '🚚 Domicilio' : '🏪 Local' ?>
                  	    <?php if ($p->cobrado === 'S'): ?>
                		<span class="badge bg-success mt-1">COBRADO</span>
                	    <?php else: ?>
                    		<span class="badge bg-warning text-dark mt-1">PENDIENTE DE COBRO</span>
                	    <?php endif; ?>
                   </div>
                   <div class="text-end">
                        <a href="tpv.php?pedido=<?= $p->idpedidos ?>"
                            class="btn btn-sm btn-primary">
                                Abrir en TPV
                        </a>
                   </div>
                </div>
            <?php endwhile; ?>

        </div>
    </div>

    <!-- PEDIDOS DEL DIA -->
        <div class="card">
            <div class="card-header">
                Pedidos del día
            </div>
        <div class="card-body">

        <?php if ($diarios->num_rows == 0): ?>
            <div class="alert alert-success">
                No hay pedidos del día
            </div>
        <?php endif; ?>
        <?php while ($d = $diarios->fetch_object()): ?>
            <div class="border p-2 mb-2 d-flex justify-content-between">
                <div>
                    <strong>Pedido #<?= $d->idpedidos ?></strong><br>
    		        🕒 <?= $d->hora ?><br>
    		        📞 <?= htmlspecialchars($d->telefono1) ?> <?= $d->nombre ?><br>
		            🏠 <?= htmlspecialchars($d->direccion) ?>
                <?= $p->domicilio === 'S' ? '🚚 Domicilio' : '🏪 Local' ?>
         	    <?php if ($d->cobrado === 'S'): ?>
            		<span class="badge bg-success mt-1">COBRADO</span>
        	    <?php else: ?>
            		<span class="badge bg-warning text-dark mt-1">PENDIENTE DE COBRO</span>
        	    <?php endif; ?>
         	    <?php if ($d->servido === 'S'): ?>
            		<span class="badge bg-success mt-1">PEDIDO SERVIDO</span>
        	    <?php else: ?>
            		<span class="badge bg-warning text-dark mt-1">PENDIENTE DE SERVIR</span>
        	    <?php endif; ?>

               </div>
                <div class="text-end">
                    <a href="tpv.php?pedido=<?= $d->idpedidos ?>"
                       class="btn btn-sm btn-primary">
                        Abrir en TPV
                    </a>
                </div>
            </div>
        <?php endwhile; ?>

</div>

<!-- MODAL CLIENTE (DENTRO DEL BODY) -->
<div class="modal fade" id="modalCliente" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Nuevo pedido</h5>
      </div>

      <div class="modal-body">
        <label>Teléfono</label>
        <input type="text" id="telefono" class="form-control mb-2">

        <label>Nombre</label>
        <input type="text" id="nombre" class="form-control mb-2">

        <label>Dirección</label>
        <input type="text" id="direccion" class="form-control">
      </div>

      <div class="modal-footer">
        <button class="btn btn-success w-100" onclick="crearPedido()">
          Crear pedido
        </button>
      </div>

    </div>
  </div>
</div>

<!-- JS SIEMPRE AL FINAL -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="public/js/index.js"></script>

</body>
</html>
