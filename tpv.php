<?php
    session_start();
    require_once "config/database.php";

    $db = Database::conectar();

    /* 1️⃣ Si viene pedido por URL → cargarlo */
    if (isset($_GET['pedido'])) {
        $_SESSION['pedido_id'] = intval($_GET['pedido']);
    }

    /* 2️⃣ Si NO hay pedido en sesión → volver a inicio */
    if (!isset($_SESSION['pedido_id'])) {
        header("Location: index.php");
        exit;
    }

    $pedido_id = $_SESSION['pedido_id'];

    $pedidoRes = $db->query("
        SELECT * FROM pedidos WHERE idpedidos=$pedido_id
    ");

    if (!$pedidoRes || $pedidoRes->num_rows == 0) {
        unset($_SESSION['pedido_id']);
        header("Location: index.php");
        exit;
    }
    $cliente = $db->query("
        SELECT c.*
        FROM clientes c
        JOIN pedidos p ON p.idclientes = c.idclientes
        WHERE p.idpedidos = $pedido_id
    ")->fetch_object();

    $pedido = $pedidoRes->fetch_object();

    /* 3️⃣ Cargar pedido */
    $pedido = $db->query("
        SELECT * FROM pedidos WHERE idpedidos=$pedido_id
    ")->fetch_object();
    
    $familias = $db->query("SELECT * FROM familias");

?>

<!DOCTYPE html>
    <html>
        <head>
            <title>TPV</title>
            <link rel="stylesheet" href="public/css/bootstrap.min.css">
            <script src="public/js/bootstrap.bundle.min.js"></script>
        </head>

    <body>
        <div class="container-fluid vh-100">

            <!-- CABECERA -->
            <div class="row bg-dark text-white p-2">
                <div class="col">
                    <strong><?= htmlspecialchars($cliente->nombre) ?></strong><br>
                    📞 <?= htmlspecialchars($cliente->telefono1) ?><br>
                    🏠 <?= htmlspecialchars($pedido->direccion) ?>
                    <?php if ($pedido->domicilio === 'S'): ?>
                        🚚 <strong>Entrega a domicilio</strong>
                    <?php else: ?>
                        🏪 Recogida en local
                    <?php endif; ?>
                </div>
                <div class="col text-center">Pedido #<?= $pedido_id ?></div>
                <div class="col text-end"><?= date("d/m/Y") ?></div>
            </div>

            <!-- CUERPO -->
            <div class="row h-75">

                <!-- FAMILIAS -->
                <div class="col-2 bg-light overflow-auto">
                    <?php while($f = $familias->fetch_object()): ?>
                        <button class="btn btn-secondary w-100 mb-2"
                            onclick="cargarArticulos(<?= $f->idfamilias ?>)">
                            <?= $f->familia ?>
                        </button>
                    <?php endwhile; ?>
            </div>

            <!-- ARTÍCULOS -->
            <div class="col-6 p-2 h-100 overflow-auto" id="articulos"></div>
            <!-- PEDIDO -->
            <div class="col-4 bg-white p-2 h-100 overflow-auto">
                <h4>Pedido</h4>
                <div id="lineas"></div>
                    <h3 class="text-end">Total: <span id="total">0.00</span> €</h3>
                </div>
            </div>

            <!-- BOTONES -->
            <div class="row bg-dark p-3">
                <div class="col text-center">
                    <button class="btn btn-primary btn-lg" onclick="finalizarPedidoLocal()">RECOGIDA EN LOCAL</button>
                    <button class="btn btn-primary btn-lg" onclick="finalizarPedidoDomicilio()">ENTREGA A DOMICILIO</button>
                    <button class="btn btn-success btn-lg" onclick="cobrar()">COBRAR</button>
                    <button class="btn btn-warning btn-lg" onclick="servir()">SERVIR</button>
                    <button class="btn btn-info btn-lg" onclick="imprimirPedido()">IMPRIMIR</button>
                    <button class="btn btn-danger btn-lg" onclick="cancelar()">CANCELAR</button>
                </div>
            </div>

        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            const PEDIDO_ID = <?= $pedido_id ?>;
        </script>
        <script>
            const CLIENTE = {
                telefono: "<?= $cliente->telefono1 ?? '' ?>",
                nombre: "<?= addslashes($cliente->nombre ?? '') ?>",
                direccion: "<?= addslashes($cliente->direccion ?? '') ?>"
            };
        </script>
        <script src="public/js/tpv.js"></script>

        <!-- MODAL COBRO -->
        <div class="modal fade" id="modalCobro" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Cobro en efectivo</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <h3>Total: <span id="cobroTotal">0.00</span> €</h3>

                    <input type="text" id="importeRecibido"
                        class="form-control form-control-lg text-center mb-2"
                        readonly value="0">

                    <h4>Cambio: <span id="cambio">0.00</span> €</h4>
                    <hr>

			<h6>Enviar ticket</h6>

                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="envioTicket" id="envioNada" value="nada" checked>
                            <label class="form-check-label" for="envioNada">
                                No enviar
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="envioTicket" id="envioWhatsapp" value="whatsapp">
                            <label class="form-check-label" for="envioWhatsapp">
                                WhatsApp
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="envioTicket" id="envioEmail" value="email">
                            <label class="form-check-label" for="envioEmail">
                                Email
                            </label>
                        </div>

                        <!-- TECLADO -->
                        <div class="row g-2 mt-2">
                            <div class="col-4"><button class="btn btn-secondary w-100" onclick="tecla(1)">1</button></div>
                            <div class="col-4"><button class="btn btn-secondary w-100" onclick="tecla(2)">2</button></div>
                            <div class="col-4"><button class="btn btn-secondary w-100" onclick="tecla(3)">3</button></div>

                            <div class="col-4"><button class="btn btn-secondary w-100" onclick="tecla(4)">4</button></div>
                            <div class="col-4"><button class="btn btn-secondary w-100" onclick="tecla(5)">5</button></div>
                            <div class="col-4"><button class="btn btn-secondary w-100" onclick="tecla(6)">6</button></div>

                            <div class="col-4"><button class="btn btn-secondary w-100" onclick="tecla(7)">7</button></div>
                            <div class="col-4"><button class="btn btn-secondary w-100" onclick="tecla(8)">8</button></div>
                            <div class="col-4"><button class="btn btn-secondary w-100" onclick="tecla(9)">9</button></div>

                            <div class="col-4"><button class="btn btn-danger w-100" onclick="borrar()">C</button></div>
                            <div class="col-4"><button class="btn btn-secondary w-100" onclick="tecla(0)">0</button></div>
                            <div class="col-4"><button class="btn btn-secondary w-100" onclick="tecla('.')">.</button></div>
                        </div>
                </div>

      <div class="modal-footer">
        <button class="btn btn-success btn-lg w-100" onclick="confirmarCobro()">COBRAR</button>
      </div>

    </div>
  </div>
</div>
</body>
</html>
