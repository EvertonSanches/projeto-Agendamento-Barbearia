<?php
session_start();

require_once("config.php");
require_once("auth.php");

requireLogin();

// Proteção de perfil
if (getUserType() !== 'barbeiro') {
    header("Location: login.php");
    exit;
}

// ID do barbeiro vem do perfil, não do login
if (!isset($_SESSION['id_barb'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$idBarbeiro = (int) $_SESSION['id_barb'];

/* FILTRO UX */
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
        c.nome AS nome_cliente
    FROM agenda a
    JOIN cliente c ON a.id_cliente = c.id_cliente
    WHERE a.id_barb = $idBarbeiro
    $whereFiltro
    ORDER BY a.data_agenda, a.hora_inicio
";

$result = $conexao->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Painel do Barbeiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/agendamento-barbearia/css/estiloBasico.css">
</head>

<body class="list-bg">

    <nav class="navbar navbar-dark bg-secondary">
        <div class="container-fluid">
            <span class="navbar-brand">Painel do Barbeiro</span>
            <a href="sair.php" class="btn btn-danger">Sair</a>
        </div>
    </nav>

    <div class="container table-container table-bg mt-5">
        <h1 class="mb-3">Atendimentos Agendados</h1>

        <div class="mb-3 d-flex gap-2">
            <a href="?filtro=todos" class="btn btn-outline-light btn-sm">Todos</a>
            <a href="?filtro=hoje" class="btn btn-outline-warning btn-sm">Hoje</a>
            <a href="?filtro=atrasados" class="btn btn-outline-danger btn-sm">Atrasados</a>
        </div>

        <table class="table table-dark table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>

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
                    } elseif ($agendamento->format('Y-m-d') === $agora->format('Y-m-d')) {
                        $badge = 'HOJE';
                        $badgeClass = 'warning';
                    } else {
                        $badge = 'AGENDADO';
                        $badgeClass = 'info';
                    }
                ?>

                    <tr>
                        <td><?= htmlspecialchars($row['nome_cliente']) ?></td>
                        <td><?= $dataFormatada ?></td>
                        <td><?= $horaFormatada ?></td>
                        <td><span class="badge bg-<?= $badgeClass ?>"><?= $badge ?></span></td>
                        <td>
                            <a class="btn btn-sm btn-warning" href="editar.php?id_agenda=<?= $row['id_agenda'] ?>">Editar</a>

                            <a href="/agendamento-barbearia/back-end/deleteAgendados.php?id_agenda=<?= $row['id_agenda'] ?>"
                                onclick="return confirm('Deseja excluir este agendamento?');"
                                class="btn btn-danger btn-sm">
                                Excluir
                            </a>

                        </td>
                    </tr>

                <?php } ?>

            </tbody>
        </table>
    </div>

</body>

</html>