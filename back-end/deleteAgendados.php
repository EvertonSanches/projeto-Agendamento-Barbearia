<?php
session_start();
include_once("config.php");

/* =========================
   CHECAGEM MÍNIMA
========================= */
if (!isset($_SESSION['tipo'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id_agenda'])) {
    header("Location: agendados.php");
    exit;
}

$idAgenda = (int) $_GET['id_agenda'];

/* =========================
   DELETE (COMO SEMPRE FOI)
========================= */
$stmt = $conexao->prepare("DELETE FROM agenda WHERE id_agenda = ?");
$stmt->bind_param("i", $idAgenda);
$stmt->execute();
$stmt->close();

/* =========================
   REDIRECIONAMENTO CORRETO
   (VOLTA PRA ONDE VEIO)
========================= */
if (!empty($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

/* FALLBACK (CASO RARO) */
$tipo = $_SESSION['tipo'];

if ($tipo === 'barbeiro') {
    header("Location: barbeiros.php");
} elseif ($tipo === 'admin') {
    header("Location: admin.php");
} else {
    header("Location: agendados.php");
}

exit;
