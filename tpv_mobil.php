<?php
session_start();
require_once "config/config.php";
require_once "config/database.php";
require_once "config/auth.php";

// Requiere autenticación
Auth::require();

$db = Database::conectar();

/* Si viene pedido por URL → cargarlo en sesión */
if (isset($_GET['pedido'])) {
    $_SESSION['pedido_id'] = intval($_GET['pedido']);
}

/* Si NO hay pedido en sesión → volver a inicio */
if (!isset($_SESSION['pedido_id'])) {
    redirect('index.php');
}

$pedido_id = intval($_SESSION['pedido_id']);

/* Cargar datos del pedido */
$stmt = Database::execute(
    "SELECT * FROM pedidos WHERE idpedidos = ?",
    "i",
    [$pedido_id]
);

$result = $stmt->get_result();

if (!$result || $result->num_rows == 0) {
    unset($_SESSION['pedido_id']);
    redirect('index.php');
}

$pedido = $result->fetch_object();
$stmt->close();

/* Cargar datos del cliente */
$stmt = Database::execute(
    "SELECT c.* FROM clientes c 
     JOIN pedidos p ON p.idclientes = c.idclientes 
     WHERE p.idpedidos = ?",
    "i",
    [$pedido_id]
);

$cliente = $stmt ? $stmt->get_result()->fetch_object() : null;
$stmt->close();

if (!$cliente) {
    $cliente = (object)[
        'nombre' => 'Cliente anónimo',
        'telefono1' => '-',
        'direccion' => '-'
    ];
}

/* Cargar familias */
$familias = $db->query("SELECT * FROM familias ORDER BY familia");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#343a40">
    <title>TPV - Pedido #<?= $pedido_id ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        /* Variables para móvil */
        :root {
            --header-height: 120px;
            --footer-height: 70px;
        }

        /* Optimización táctil */
        * {
            -webkit-tap-highlight-color: rgba(0,0,0,0);
        }

        body {
            overflow: hidden;
            font-size: 14px;
        }

        /* Header responsive */
        .tpv-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #343a40;
            color: white;
            padding: 10px;
            height: var(--header-height);
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .tpv-header {
                font-size: 12px;
                height: 140px;
            }
            :root {
                --header-height: 140px;
            }
        }

        /* Contenedor principal */
        .tpv-container {
            position: fixed;
            top: var(--header-height);
            bottom: var(--footer-height);
            left: 0;
            right: 0;
            display: flex;
            flex-direction: column;
        }

        @media (min-width: 769px) {
            .tpv-container {
                flex-direction: row;
            }
        }

        /* Familias - Ocultar en móvil por defecto */
        .familias-panel {
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            overflow-y: auto;
            padding: 10px;
        }

        @media (max-width: 768px) {
            .familias-panel {
                display: none;
                position: fixed;
                top: var(--header-height);
                left: 0;
                width: 200px;
                bottom: var(--footer-height);
                z-index: 1020;
                box-shadow: 2px 0 10px rgba(0,0,0,0.3);
            }
            
            .familias-panel.show {
                display: block;
            }
        }

        @media (min-width: 769px) {
            .familias-panel {
                width: 180px;
                flex-shrink: 0;
            }
        }

        /* Artículos */
        .articulos-panel {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            background: white;
        }

        @media (max-width: 768px) {
            .articulos-panel {
                display: none;
                position: fixed;
                top: var(--header-height);
                left: 0;
                right: 0;
                bottom: var(--footer-height);
                z-index: 1010;
                overflow-y: auto;
            }
            
            .articulos-panel.show {
                display: block;
            }
        }

        /* Pedido panel */
        .pedido-panel {
            background: white;
            border-left: 1px solid #dee2e6;
            overflow-y: auto;
            padding: 15px;
        }

        @media (max-width: 768px) {
            .pedido-panel {
                display: none;
                position: fixed;
                top: var(--header-height);
                left: 0;
                right: 0;
                bottom: var(--footer-height);
                z-index: 1030;
                border-left: none;
                border-top: 2px solid #dee2e6;
                overflow-y: auto;
                background: white;
            }
            
            .pedido-panel.show {
                display: block;
            }
        }

        @media (min-width: 769px) {
            .pedido-panel {
                width: 350px;
                flex-shrink: 0;
            }
        }

        /* Botones grandes para táctil */
        .familia-btn {
            height: 55px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .articulo-btn {
            min-height: 75px;
            font-size: 13px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 10px;
        }

        .articulo-nombre {
            font-weight: 600;
            margin-bottom: 5px;
            text-align: center;
        }

        .articulo-precio {
            color: #28a745;
            font-weight: bold;
            font-size: 16px;
        }

        /* Footer botones */
        .tpv-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #343a40;
            padding: 8px;
            z-index: 1000;
            height: var(--footer-height);
        }

        @media (max-width: 768px) {
            .tpv-footer {
                overflow-x: auto;
                white-space: nowrap;
                height: auto;
                padding: 5px;
            }
            
            .tpv-footer .btn {
                font-size: 11px;
                padding: 8px 10px;
                margin: 2px;
            }
        }

        /* Botón menú hamburguesa */
        .menu-toggle {
            position: fixed;
            top: calc(var(--header-height) + 10px);
            left: 10px;
            z-index: 1050;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #007bff;
            color: white;
            border: none;
            font-size: 20px;
            box-shadow: 0 4px 15px rgba(0,123,255,0.5);
            display: none;
            transition: all 0.3s ease;
        }
        
        .menu-toggle:hover {
            background: #0056b3;
            transform: scale(1.1);
        }
        
        .menu-toggle:active {
            transform: scale(0.95);
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        /* Botón ver pedido en móvil */
        .ver-pedido-btn {
            position: fixed;
            bottom: calc(var(--footer-height) + 10px);
            right: 10px;
            z-index: 1050;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #28a745;
            color: white;
            border: none;
            font-size: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            display: none;
            transition: all 0.3s ease;
        }
        
        .ver-pedido-btn:active {
            transform: scale(0.95);
        }

        @media (max-width: 768px) {
            .ver-pedido-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0% { box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4); }
                50% { box-shadow: 0 4px 20px rgba(40, 167, 69, 0.7); }
                100% { box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4); }
            }
        }

        .badge-pedido {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            border: 2px solid white;
            animation: bounce 0.5s;
        }
        
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Overlay para cerrar paneles */
      /*  .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1005;
        }

        .overlay.show {
            display: block;
        }*/

        /* Líneas de pedido móvil */
        @media (max-width: 768px) {
            .linea-pedido {
                font-size: 13px;
                padding: 8px !important;
            }
            
            .btn-group-sm .btn {
                font-size: 16px;
                padding: 5px 10px;
            }
        }

        /* Total grande en móvil */
        .total-pedido {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
            text-align: right;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-top: 10px;
        }

        /* Grid artículos responsive */
        .articulos-grid {
            display: grid;
            gap: 8px;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        @media (max-width: 768px) {
            .articulos-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .articulos-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="tpv-header">
        <div class="row g-2">
            <div class="col-md-4 col-12">
                <strong>👤 <?= htmlspecialchars($cliente->nombre) ?></strong><br>
                <small>📞 <?= htmlspecialchars($cliente->telefono1) ?></small><br>
                <small>🏠 <?= htmlspecialchars($pedido->direccion ?? $cliente->direccion) ?></small>
            </div>
            <div class="col-md-4 col-6 text-center">
                <h5 class="mb-0">Pedido #<?= $pedido_id ?></h5>
                <small><?= date("d/m/Y H:i") ?></small>
            </div>
            <div class="col-md-4 col-6 text-end">
                <?php if ($pedido->domicilio === 'S'): ?>
                    <span class="badge bg-info">🚚 Domicilio</span>
                <?php else: ?>
                    <span class="badge bg-secondary">🏪 Local</span>
                <?php endif; ?>
                <br>
                <a href="<?= url('index.php') ?>" class="btn btn-sm btn-outline-light mt-1">
                    ← Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Botón menú móvil -->
    <?php if (!Auth::isDelivery()): ?>
    <button class="menu-toggle" id="menuToggle" onclick="toggleFamilias()">
        ☰
    </button>
    <?php endif; ?>

    <!-- Botón ver pedido móvil -->
    <?php if (!Auth::isDelivery()): ?>
    <button class="ver-pedido-btn" id="verPedidoBtn" onclick="togglePedido()">
        🛒
        <span class="badge-pedido" id="badgePedido">0</span>
    </button>
    <?php endif; ?>

    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="cerrarPaneles()"></div>

    <!-- Contenedor principal -->
    <div class="tpv-container">
        
        <?php if (!Auth::isDelivery()): ?>
        <!-- FAMILIAS -->
        <div class="familias-panel" id="familiasPanel">
            <h6 class="text-center mb-3 d-md-block d-none">📁 Familias</h6>
            <button class="btn btn-danger w-100 mb-2 d-md-none" onclick="toggleFamilias()">
                ✕ Cerrar
            </button>
            <?php while($f = $familias->fetch_object()): ?>
                <button class="btn btn-outline-primary w-100 familia-btn"
                    onclick="cargarArticulos(<?= $f->idfamilias ?>)">
                    <?= htmlspecialchars($f->familia) ?>
                </button>
            <?php endwhile; ?>
        </div>

        <!-- ARTÍCULOS -->
        <div class="articulos-panel show" id="articulosPanel">
            <div class="alert alert-info">
                👈 Selecciona una familia para ver los artículos
            </div>
        </div>
        <?php else: ?>
        <!-- DELIVERY: Solo mensaje informativo -->
        <div class="articulos-panel show" id="articulosPanel">
            <div class="alert alert-info text-center">
                <h5>🚚 Modo Delivery</h5>
                <p>Solo puedes cobrar y servir pedidos a domicilio.</p>
                <p class="mb-0">No puedes modificar el contenido del pedido.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- PEDIDO -->
        <div class="pedido-panel <?= Auth::isDelivery() ? '' : '' ?>" id="pedidoPanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">🛒 Pedido</h5>
                <?php if (!Auth::isDelivery()): ?>
                <button class="btn btn-sm btn-outline-danger d-md-none" onclick="togglePedido()">
                    ✕ Cerrar
                </button>
                <?php endif; ?>
            </div>
            <div id="lineas" class="mb-3"></div>
            <div class="total-pedido">
                Total: <span id="total">0.00</span> €
            </div>
        </div>
    </div>

    <!-- Footer botones -->
    <div class="tpv-footer text-center">
        <?php if (!Auth::isDelivery()): ?>
        <button class="btn btn-primary btn-sm" onclick="finalizarPedidoLocal()">
            🏪 LOCAL
        </button>
        <button class="btn btn-info btn-sm" onclick="finalizarPedidoDomicilio()">
            🚚 DOMICILIO
        </button>
        <?php endif; ?>
        
        <button class="btn btn-success btn-sm" onclick="cobrar()">
            💰 COBRAR
        </button>
        <button class="btn btn-warning btn-sm" onclick="servir()">
            ✅ SERVIR
        </button>
        
        <?php if (!Auth::isDelivery()): ?>
        <button class="btn btn-secondary btn-sm" onclick="imprimirPedido()">
            🖨️ TICKET
        </button>
        <?php endif; ?>
        
        <?php if (Auth::isAdmin() || Auth::isCajero()): ?>
        <button class="btn btn-danger btn-sm" onclick="cancelar()">
            ❌ CANCELAR
        </button>
        <?php endif; ?>
    </div>

    <!-- MODAL COBRO -->
    <div class="modal fade" id="modalCobro" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">💰 Cobro en efectivo</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <h3 class="mb-3">Total: <span id="cobroTotal" class="text-success">0.00</span> €</h3>

                <input type="text" id="importeRecibido"
                    class="form-control form-control-lg text-center mb-3"
                    readonly value="0" 
                    style="font-size: 2rem; height: 70px;">

                <h4 class="mb-4">Cambio: <span id="cambio" class="text-primary">0.00</span> €</h4>
                
                <hr>

                <h6 class="mb-2">📤 Enviar ticket</h6>

                <div class="btn-group w-100 mb-3" role="group">
                    <input type="radio" class="btn-check" name="envioTicket" id="envioNada" value="nada" checked>
                    <label class="btn btn-outline-secondary" for="envioNada">No enviar</label>

                    <input type="radio" class="btn-check" name="envioTicket" id="envioWhatsapp" value="whatsapp">
                    <label class="btn btn-outline-success" for="envioWhatsapp">📱 WhatsApp</label>

                    <input type="radio" class="btn-check" name="envioTicket" id="envioEmail" value="email">
                    <label class="btn btn-outline-primary" for="envioEmail">📧 Email</label>
                </div>

                <hr>

                <!-- TECLADO NUMÉRICO -->
                <div class="row g-2 mt-2">
                    <div class="col-4">
                        <button class="btn btn-secondary w-100 btn-lg" 
                                style="height: 60px; font-size: 1.5rem;" 
                                onclick="tecla(1)">1</button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-secondary w-100 btn-lg" 
                                style="height: 60px; font-size: 1.5rem;" 
                                onclick="tecla(2)">2</button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-secondary w-100 btn-lg" 
                                style="height: 60px; font-size: 1.5rem;" 
                                onclick="tecla(3)">3</button>
                    </div>

                    <div class="col-4">
                        <button class="btn btn-secondary w-100 btn-lg" 
                                style="height: 60px; font-size: 1.5rem;" 
                                onclick="tecla(4)">4</button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-secondary w-100 btn-lg" 
                                style="height: 60px; font-size: 1.5rem;" 
                                onclick="tecla(5)">5</button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-secondary w-100 btn-lg" 
                                style="height: 60px; font-size: 1.5rem;" 
                                onclick="tecla(6)">6</button>
                    </div>

                    <div class="col-4">
                        <button class="btn btn-secondary w-100 btn-lg" 
                                style="height: 60px; font-size: 1.5rem;" 
                                onclick="tecla(7)">7</button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-secondary w-100 btn-lg" 
                                style="height: 60px; font-size: 1.5rem;" 
                                onclick="tecla(8)">8</button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-secondary w-100 btn-lg" 
                                style="height: 60px; font-size: 1.5rem;" 
                                onclick="tecla(9)">9</button>
                    </div>

                    <div class="col-4">
                        <button class="btn btn-danger w-100 btn-lg" 
                                style="height: 60px; font-size: 1.5rem;" 
                                onclick="borrar()">C</button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-secondary w-100 btn-lg" 
                                style="height: 60px; font-size: 1.5rem;" 
                                onclick="tecla(0)">0</button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-secondary w-100 btn-lg" 
                                style="height: 60px; font-size: 1.5rem;" 
                                onclick="tecla('.')">.</button>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success btn-lg w-100" 
                        style="height: 60px; font-size: 1.2rem;" 
                        onclick="confirmarCobro()">
                    ✅ CONFIRMAR COBRO
                </button>
            </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const PEDIDO_ID = <?= $pedido_id ?>;
        const CLIENTE = {
            telefono: "<?= $cliente->telefono1 ?? '' ?>",
            nombre: "<?= addslashes($cliente->nombre ?? '') ?>",
            direccion: "<?= addslashes($cliente->direccion ?? '') ?>"
        };
    </script>
    <script src="public/js/tpv.js"></script>

</body>
</html>
