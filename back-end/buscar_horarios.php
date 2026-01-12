<?php
include_once('config.php');

$data = $_GET['data'] ?? '';
$barbeiro = (int) ($_GET['barbeiro'] ?? 0);

if (!$data || !$barbeiro) {
    echo json_encode([]);
    exit;
}

// Horários fixos da barbearia
$horariosPossiveis = [
    '09:00','10:00','11:00','12:00',
    '13:00','14:00','15:00','16:00',
    '17:00','18:00'
];

// Busca horários já ocupados
$stmt = $conexao->prepare("
    SELECT hora_agenda 
    FROM agenda 
    WHERE data_agenda = ? AND id_barb = ?
");
$stmt->bind_param("si", $data, $barbeiro);
$stmt->execute();
$res = $stmt->get_result();

$ocupados = [];
while ($row = $res->fetch_assoc()) {
    $ocupados[] = substr($row['hora_agenda'], 0, 5);
}

$disponiveis = array_values(array_diff($horariosPossiveis, $ocupados));

echo json_encode($disponiveis);
