<?php
require_once "../config/database.php";

$pedido_id = (int)$_POST['pedido'];
$email = $_POST['email'];

$file = "../tickets/ticket_$pedido_id.pdf";

$subject = "Ticket pedido #$pedido_id";
$message = "Gracias por su pedido. Adjuntamos su ticket.";
$separator = md5(time());

$headers = "From: pedidos@tupizzeria.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$separator\"";

$body = "--$separator\r\n";
$body .= "Content-Type: text/plain; charset=\"utf-8\"\r\n\r\n";
$body .= "$message\r\n";

$attachment = chunk_split(base64_encode(file_get_contents($file)));

$body .= "--$separator\r\n";
$body .= "Content-Type: application/pdf; name=\"ticket.pdf\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n";
$body .= "Content-Disposition: attachment\r\n\r\n";
$body .= $attachment."\r\n";
$body .= "--$separator--";

mail($email, $subject, $body, $headers);

echo json_encode(["ok"=>true]);
