<?php 
session_start();
include_once('config.php');

require_once("auth.php");
requireLogin();

if (getUserType() !== 'cliente') {
    header("Location: login.php");
    exit;
}

$idCliente = (int)$_SESSION['id_cliente'];

/* =========================
   FILTRO UX
========================= */
$filtro = $_GET['filtro'] ?? 'todos';
$whereFiltro = "";

$hoje = date('Y-m-d');

if ($filtro === 'hoje') {
    $whereFiltro = "AND a.data_agenda = '$hoje'";
}

if ($filtro === 'atrasados') {
    $whereFiltro = "AND a.data_agenda < '$hoje'";
}

$sql = "
    SELECT 
        a.id_agenda,
        a.data_agenda,
        a.hora_inicio,
        b.nome_barb
    FROM agenda a
    JOIN barbeiro b ON a.id_barb = b.id_barb
    WHERE a.id_cliente = $idCliente
    $whereFiltro
    ORDER BY a.data_agenda, a.hora_inicio
";

$result = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Meus Agendamentos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="/agendamento-barbearia/css/estiloBasico.css">

<style>
/* =========================
   SKELETON
========================= */
.skeleton {
    background: linear-gradient(90deg,#2b2b2b 25%,#3a3a3a 37%,#2b2b2b 63%);
    background-size: 400% 100%;
    animation: shimmer 1.4s ease infinite;
    height: 20px;
    border-radius: 4px;
}
@keyframes shimmer {
    0% { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}

/* =========================
   STATUS VISUAL
========================= */
.tr-atrasado {
    background-color: rgba(220,53,69,.15) !important;
}
.tr-hoje {
    background-color: rgba(255,193,7,.15) !important;
}
.tr-futuro {
    background-color: rgba(13,202,240,.10) !important;
}
</style>
</head>

<body class="list-bg">

<nav class="navbar navbar-dark bg-secondary">
    <div class="container-fluid">
        <a class="navbar-brand" href="login.php">Home</a>
        <a href="sair.php" class="btn btn-danger">Sair</a>
    </div>
</nav>

<div class="container table-container table-bg mt-5">
<h1 class="mb-3">Meus Agendamentos</h1>

<div class="mb-3 d-flex gap-2">
    <a href="?filtro=todos" class="btn btn-outline-light btn-sm">
        <i class="bi bi-list"></i> Todos
    </a>
    <a href="?filtro=hoje" class="btn btn-outline-warning btn-sm">
        <i class="bi bi-calendar-event"></i> Hoje
    </a>
    <a href="?filtro=atrasados" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-exclamation-triangle"></i> Atrasados
    </a>
</div>

<table class="table table-dark table-striped table-hover align-middle">
<thead>
<tr>
    <th>Barbeiro</th>
    <th>Data</th>
    <th>Hora</th>
    <th>Status</th>
    <th>Ações</th>
</tr>
</thead>

<tbody id="tabela-agenda">

<?php while ($row = mysqli_fetch_assoc($result)) {

    $dataBanco = $row['data_agenda'];
    $horaBanco = $row['hora_inicio'];

    $dataFormatada = date('d/m/Y', strtotime($dataBanco));
    $horaFormatada = date('H:i', strtotime($horaBanco));

    $agendamento = new DateTime("$dataBanco $horaBanco");
    $agora = new DateTime();

    if ($agendamento < $agora) {
        $badge = 'ATRASADO';
        $badgeClass = 'danger';
        $tooltip = 'Este agendamento já passou';
        $rowClass = 'tr-atrasado';
        $icon = 'bi-clock-history';
    } elseif ($agendamento->format('Y-m-d') === $agora->format('Y-m-d')) {
        $badge = 'HOJE';
        $badgeClass = 'warning';
        $tooltip = 'Agendado para hoje';
        $rowClass = 'tr-hoje';
        $icon = 'bi-calendar-day';
    } else {
        $badge = 'AGENDADO';
        $badgeClass = 'info';
        $tooltip = 'Agendamento futuro';
        $rowClass = 'tr-futuro';
        $icon = 'bi-calendar-check';
    }
?>

<tr class="<?= $rowClass ?>">
<td><?= htmlspecialchars($row['nome_barb']) ?></td>
<td><?= $dataFormatada ?></td>
<td><?= $horaFormatada ?></td>

<td>
<span class="badge bg-<?= $badgeClass ?>"
      data-bs-toggle="tooltip"
      title="<?= $tooltip ?>">
    <i class="bi <?= $icon ?>"></i> <?= $badge ?>
</span>
</td>

<td>
<a class="btn btn-sm btn-warning"
   href="editar.php?id_agenda=<?= $row['id_agenda'] ?>">
   <i class="bi bi-pencil"></i>
</a>

<a class="btn btn-sm btn-danger"
   href="/agendamento-barbearia/back-end/deleteAgendados.php?id_agenda=<?= $row['id_agenda'] ?>"
   onclick="return confirm('Deseja excluir este agendamento?');">
   <i class="bi bi-trash"></i>
</a>
</td>
</tr>

<?php } ?>

</tbody>
</table>

<a href="cadastroAgenda.php" class="btn btn-success mt-3">
    <i class="bi bi-plus-circle"></i> Novo Agendamento
</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
</script>

</body>
</html>
