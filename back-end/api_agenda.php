<?php
include_once('config.php');

$result = $conexao->query("
  SELECT 
    a.id_agenda AS id,
    CONCAT(c.nome, ' - ', b.nome_barb) AS title,
    CONCAT(a.data_agenda, ' ', a.hora_agenda) AS start
  FROM agenda a
  JOIN cliente c ON a.id_cliente = c.id_cliente
  JOIN barbeiro b ON a.id_barb = b.id_barb
");

$eventos = [];

while ($row = $result->fetch_assoc()) {
    $eventos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($eventos);
