session_start();
require_once "../config/database.php";
Database::conectar()->query(
"DELETE FROM pedidos WHERE idpedidos=".$_SESSION['pedido_id']
);
unset($_SESSION['pedido_id']);
