<?php
require_once "../config/database.php";

$pedido = intval($_POST['pedido_id']);

$to = "gescvg@email.com"; // o desde BD
$subject = "Ticket pedido #$pedido";
$mensaje = "
Gracias por su pedido.

Puede descargar su ticket aquí:
https://tudominio.com/tickets/ticket_$pedido.pdf
";

$headers = "From: pedidos@tudominio.com";

mail($to, $subject, $mensaje, $headers);

echo json_encode(["ok" => true]);
