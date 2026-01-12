<?php
session_start();
include_once("config.php");

require_once("auth.php");
requireLogin();

/* =========================
   VALIDAÇÃO BÁSICA
========================= */
if (
    empty($_POST['id_agenda']) ||
    empty($_POST['id_barb']) ||
    empty($_POST['id_servico']) ||
    empty($_POST['data_agenda']) ||
    empty($_POST['hora_inicio'])
) {
    die("Dados inválidos.");
}

$idAgenda  = (int) $_POST['id_agenda'];
$idBarb    = (int) $_POST['id_barb'];
$idServico = (int) $_POST['id_servico'];
$data      = $_POST['data_agenda'];
$horaIni   = $_POST['hora_inicio'];

/* =========================
   BUSCAR DURAÇÃO DO SERVIÇO
========================= */
$stmt = $conexao->prepare("
    SELECT duracao_min 
    FROM servicos 
    WHERE id_servico = ?
");
$stmt->bind_param("i", $idServico);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Serviço inválido.");
}

$duracao = (int) $res->fetch_assoc()['duracao_min'];
$stmt->close();

/* =========================
   CALCULAR HORA FIM
========================= */
$inicio = new DateTime("$data $horaIni");
$fim    = clone $inicio;
$fim->modify("+{$duracao} minutes");

$horaFim = $fim->format('H:i');

/* =========================
   ATUALIZAR AGENDA
========================= */
$stmt = $conexao->prepare("
    UPDATE agenda 
    SET 
        id_barb      = ?,
        id_servico   = ?,
        data_agenda  = ?,
        hora_inicio  = ?,
        hora_fim     = ?
    WHERE id_agenda = ?
");

$stmt->bind_param(
    "iisssi",
    $idBarb,
    $idServico,
    $data,
    $horaIni,
    $horaFim,
    $idAgenda
);

$stmt->execute();
$stmt->close();

/* =========================
   REDIRECIONAMENTO (CORREÇÃO)
========================= */
$tipo = $_SESSION['tipo'] ?? 'cliente';

if ($tipo === 'barbeiro') {
    header("Location: barbeiros.php");
} elseif ($tipo === 'admin') {
    header("Location: admin.php");
} else {
    header("Location: agendados.php");
}
exit;
