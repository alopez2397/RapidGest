<?php
require_once "../config/database.php";
require_once "../libs/tcpdf/tcpdf.php";

$db = Database::conectar();

$pedido_id = (int)$_GET['pedido'];

$pedido = $db->query("
  SELECT p.*, c.nombre, c.telefono, c.direccion
  FROM pedidos p
  JOIN clientes c ON c.idclientes = p.idclientes
  WHERE p.idpedidos = $pedido_id
");

$lineas = $db->query("
  SELECT articulo, cantidad, pvp
  FROM lineaspedido
  WHERE idpedidos = $pedido_id
")->fetch_object();

$pdf = new TCPDF('P','mm',[80,200]);
$pdf->SetMargins(5,5,5);
$pdf->AddPage();
$pdf->SetFont('helvetica','',9);

$pdf->Cell(0,5,"PIZZERIA LA FUENTE",0,1,'C');
$pdf->Ln(2);

$pdf->MultiCell(0,4,
"Cliente: {$pedido->nombre}
Tel: {$pedido->telefono}
Dir: {$pedido->direccion}",0);

$pdf->Ln(2);

$total = 0;
while($l = $lineas->fetch_object()){
  $t = $l->cantidad * $l->pvp;
  $total += $t;
  $pdf->Cell(0,4,"{$l->cantidad} x {$l->articulo}  ".number_format($t,2)."€",0,1);
}

$pdf->Ln(2);
$pdf->Cell(0,5,"TOTAL: ".number_format($total,2)." €",0,1,'R');

$path = "../tickets/ticket_$pedido_id.pdf";
$pdf->Output($path,'F');

echo json_encode([
  "ok" => true,
  "file" => $path
]);
