<?php
session_start();
include_once("config.php");

date_default_timezone_set('America/Sao_Paulo');

require_once("auth.php");
requireLogin();

if (getUserType() !== 'cliente') {
    header("Location: login.php");
    exit;
}

/* =========================
   VALIDAÇÃO POST
========================= */
if (
    empty($_POST['data_agenda']) ||
    empty($_POST['hora_inicio']) ||
    empty($_POST['id_barb']) ||
    empty($_POST['id_servico'])
) {
    echo "<script>alert('Dados inválidos.'); history.back();</script>";
    exit;
}

$data       = $_POST['data_agenda'];
$horaInicio = $_POST['hora_inicio'];
$idBarb     = (int)$_POST['id_barb'];
$idServico  = (int)$_POST['id_servico'];
$idCliente  = (int)$_SESSION['id_cliente'];

/* =========================
   BLOQUEAR PASSADO
========================= */
$inicio = new DateTime("$data $horaInicio");
$agora  = new DateTime();

if ($inicio < $agora) {
    echo "<script>alert('Não é permitido agendar horários passados.'); history.back();</script>";
    exit;
}

/* =========================
   BUSCAR DURAÇÃO
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
    echo "<script>alert('Serviço inválido.'); history.back();</script>";
    exit;
}

$duracao = (int)$res->fetch_assoc()['duracao_min'];
$stmt->close();

/* =========================
   CALCULAR FIM
========================= */
$fim = clone $inicio;
$fim->modify("+{$duracao} minutes");

$horaInicio = $inicio->format('H:i:s');
$horaFim    = $fim->format('H:i:s');

/* =========================
   CONFLITO DE HORÁRIO
========================= */
$stmt = $conexao->prepare("
    SELECT id_agenda 
    FROM agenda
    WHERE id_barb = ?
      AND data_agenda = ?
      AND (
        hora_inicio < ?
        AND hora_fim > ?
      )
");
$stmt->bind_param("isss", $idBarb, $data, $horaFim, $horaInicio);
$stmt->execute();
$conflito = $stmt->get_result();

if ($conflito->num_rows > 0) {
    echo "<script>alert('Horário indisponível.'); history.back();</script>";
    exit;
}
$stmt->close();

/* =========================
   INSERT FINAL
========================= */
$stmt = $conexao->prepare("
    INSERT INTO agenda 
    (data_agenda, hora_inicio, hora_fim, id_cliente, id_barb, id_servico)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "sssiii",
    $data,
    $horaInicio,
    $horaFim,
    $idCliente,
    $idBarb,
    $idServico
);
$stmt->execute();
$stmt->close();

header("Location: agendados.php");
exit;
