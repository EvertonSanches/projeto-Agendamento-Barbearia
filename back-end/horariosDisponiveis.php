<?php
include_once("config.php");

date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: application/json');

/* ========================
   VALIDAÇÃO
======================== */
if (
    empty($_GET['data']) ||
    empty($_GET['id_barb']) ||
    empty($_GET['id_servico'])
) {
    echo json_encode([]);
    exit;
}

$data       = $_GET['data'];
$idBarb     = (int)$_GET['id_barb'];
$idServico  = (int)$_GET['id_servico'];

/* ========================
   DURAÇÃO DO SERVIÇO
======================== */
$stmt = $conexao->prepare("
    SELECT duracao_min 
    FROM servicos 
    WHERE id_servico = ?
");
$stmt->bind_param("i", $idServico);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode([]);
    exit;
}

$duracao = (int)$res->fetch_assoc()['duracao_min'];
$stmt->close();

/* ========================
   HORÁRIO DE FUNCIONAMENTO
======================== */
$inicioDia = new DateTime("$data 09:00");
$fimDia    = new DateTime("$data 18:00");

/* ========================
   AGENDAMENTOS EXISTENTES
======================== */
$stmt = $conexao->prepare("
    SELECT hora_inicio, hora_fim
    FROM agenda
    WHERE id_barb = ?
      AND data_agenda = ?
");
$stmt->bind_param("is", $idBarb, $data);
$stmt->execute();
$resAg = $stmt->get_result();

$ocupados = [];
while ($row = $resAg->fetch_assoc()) {
    $ocupados[] = [
        'inicio' => new DateTime("$data {$row['hora_inicio']}"),
        'fim'    => new DateTime("$data {$row['hora_fim']}")
    ];
}
$stmt->close();

/* ========================
   GERAR HORÁRIOS LIVRES
======================== */
$horariosLivres = [];
$agora = new DateTime();
$hoje  = $agora->format('Y-m-d');

$cursor = clone $inicioDia;

while (true) {

    $inicio = clone $cursor;
    $fim    = clone $inicio;
    $fim->modify("+{$duracao} minutes");

    if ($fim > $fimDia) {
        break;
    }

    /* Bloquear horários passados no dia atual */
    if ($data === $hoje && $inicio < $agora) {
        $cursor->modify("+{$duracao} minutes");
        continue;
    }

    /* Verificar conflito */
    $conflito = false;
    foreach ($ocupados as $o) {
        if ($inicio < $o['fim'] && $fim > $o['inicio']) {
            $conflito = true;
            break;
        }
    }

    if (!$conflito) {
        $horariosLivres[] = $inicio->format('H:i');
    }

    /* AVANÇO CORRETO */
    $cursor->modify("+{$duracao} minutes");
}

echo json_encode($horariosLivres);
exit;
