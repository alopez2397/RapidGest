<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "../vendor/phpmailer/Exception.php";
require_once "../vendor/phpmailer/PHPMailer.php";
require_once "../vendor/phpmailer/SMTP.php";


ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
session_start();

/* Validar POST */
if (
    !isset($_POST['pedido_id']) ||
    !isset($_POST['email'])
) {
    echo json_encode([
        "ok" => false,
        "error" => "Datos inválidos"
    ]);
    exit;
}

$pedido_id = intval($_POST['pedido_id']);
$email     = trim($_POST['email']);

if ($pedido_id <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "ok" => false,
        "error" => "Pedido o email no válidos"
    ]);
    exit;
}


$pdf = "../tickets/ticket_$pedido_id.pdf";

if (!file_exists($pdf)) {
    echo json_encode(["ok" => false, "error" => "Ticket no encontrado"]);
    exit;
}

$mail = new PHPMailer(true);

try {
    // ⚠️ CONFIGURACIÓN SMTP (ejemplo Gmail)
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'yldsoftware@gmail.com';
    $mail->Password   = 'kqik viqr tvcd zsgb';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Remitente
    $mail->setFrom('yldsoftware@gmail.com', 'RapidGest');
    $mail->addAddress($email);

    // Adjuntar PDF
    $mail->addAttachment($pdf, "ticket/ticket_$pedido_id.pdf");

    // Contenido
    $mail->isHTML(true);
    $mail->Subject = "Ticket pedido #$pedido_id";
    $mail->Body = "
        <p>Gracias por su pedido.</p>
        <p>Adjuntamos su ticket en PDF.</p>
        <p><strong>RapidGest</strong></p>
    ";

    $mail->send();

    echo json_encode(["ok" => true]);

} catch (Exception $e) {
    echo json_encode([
        "ok" => false,
        //"error" => $mail->ErrorInfo
        "error" => $e->getMessage()
    ]);
}
