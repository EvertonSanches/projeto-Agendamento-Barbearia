<?php
session_start();
include_once("config.php");


if (!isset($_POST['submit'])) {
    header("Location: agendados.php");
    exit();
}


$id       = intval($_POST['id_agenda'] ?? 0);
$data     = trim($_POST['dia_corte'] ?? '');
$hora     = trim($_POST['hora_corte'] ?? '');
$idBarb   = intval($_POST['barbeiro_id'] ?? 0);


if ($id <= 0 || $data === '' || $hora === '' || $idBarb <= 0) {
    echo "Dados inválidos. Verifique e tente novamente.";
    exit;
}


$hoje = date('Y-m-d');
if ($data < $hoje) {
    echo "<script>
        alert('A data não pode ser anterior a hoje!');
        window.history.back();
    </script>";
    exit;
}


$stmtCheck = $conexao->prepare("
    SELECT id_agenda 
    FROM agenda 
    WHERE data_agenda = ? 
      AND hora_agenda = ? 
      AND id_barb = ?
      AND id_agenda <> ?
");
if (!$stmtCheck) {
    echo "Erro no banco: " . $conexao->error;
    exit;
}
$stmtCheck->bind_param("ssii", $data, $hora, $idBarb, $id);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result();

if ($resCheck->num_rows > 0) {
    echo "<script>alert('Este horário já está ocupado por outro agendamento. Escolha outro horário.'); history.back();</script>";
    $stmtCheck->close();
    exit;
}
$stmtCheck->close();


$stmt = $conexao->prepare("
    UPDATE agenda
    SET data_agenda = ?, hora_agenda = ?, id_barb = ?
    WHERE id_agenda = ?
");
if (!$stmt) {
    echo "Erro ao preparar update: " . $conexao->error;
    exit;
}
$stmt->bind_param("ssii", $data, $hora, $idBarb, $id);

if (! $stmt->execute()) {
    echo "Erro ao atualizar: " . $stmt->error;
    $stmt->close();
    exit;
}
$stmt->close();


$tipo = $_SESSION['tipo'] ?? '';

if ($tipo === 'admin') {
    header("Location: admin.php");
    exit;
}

if ($tipo === 'barbeiro') {
    header("Location: barbeiro.php");
    exit;
}


header("Location: agendados.php");
exit;
