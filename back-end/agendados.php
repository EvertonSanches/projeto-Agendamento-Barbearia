<?php
session_start();
include_once('config.php');


if (!isset($_SESSION['id_cliente']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

$logado = $_SESSION['email'];
$idCliente = $_SESSION['id_cliente'];


$sql = "SELECT a.id_agenda, a.hora_agenda, a.data_agenda, b.nome_barb 
        FROM agenda a
        JOIN barbeiro b ON a.id_barb = b.id_barb
        WHERE a.id_cliente = $idCliente
        ORDER BY a.id_agenda ASC";

$result = $conexao->query($sql);

if (!$result) {
    die("Erro ao buscar agendamentos: " . $conexao->error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Agendamentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/agendamento-barbearia/css/agendados.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-secondary">
    <div class="container-fluid">
        <a class="navbar-brand" href="login.php">Home</a>
        <div class="d-flex">
            <a href="sair.php" class="btn btn-danger me-5">Sair</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1>Bem-vindo, <?= htmlspecialchars($logado) ?></h1>
    <table class="table table-striped table-dark mt-3">
        <thead>
            <tr>
                <th>Barbeiro</th>
                <th>Hora</th>
                <th>Dia</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nome_barb']) ?></td>
                    <td><?= $row['hora_agenda'] ?></td>
                    <td><?= $row['data_agenda'] ?></td>
                    <td>
                        <a class="btn btn-sm btn-primary" href="editar.php?id_agenda=<?= $row['id_agenda'] ?>">Editar</a>
                        <a class="btn btn-sm btn-danger" href="deleteAgendados.php?id_agenda=<?= $row['id_agenda'] ?>">Deletar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">Nenhum agendamento encontrado.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <a href="cadastroAgenda.php" class="btn btn-success">Novo Agendamento</a>
</div>
</body>
</html>
