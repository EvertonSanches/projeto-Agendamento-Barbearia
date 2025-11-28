<?php
session_start();
include_once("config.php");


if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'barbeiro') {
    header("Location: login.php");
    exit;
}

$idBarbeiro = intval($_SESSION['id_barb']);


if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $conexao->query("DELETE FROM agenda WHERE id_agenda = $id AND id_barb = $idBarbeiro");
}


$sql = "
    SELECT 
        a.id_agenda,
        a.data_agenda,
        a.hora_agenda,
        c.nome AS nome_cliente,
        b.nome_barb AS nome_barbeiro
    FROM agenda a
    JOIN cliente c ON a.id_cliente = c.id_cliente
    JOIN barbeiro b ON a.id_barb = b.id_barb
    WHERE a.id_barb = $idBarbeiro
    ORDER BY a.data_agenda, a.hora_agenda
";

$result = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Barbeiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/agendamento-barbearia/css/agendados.css">    
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-secondary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Home</a>
        <div class="d-flex">
            <a href="sair.php" class="btn btn-danger me-5">Sair</a>
        </div>
    </div>
</nav>

    <div class="container mt-5">
        <h1>Atendimentos Agendados</h1>

        <br><br>
        <table class="table table-striped table-dark mt-3">
            <thead>
                <tr>
                    
                    <th>Cliente</th>
                    <th>Dia</th>
                    <th>Hora</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    
                    <td><?= $row['nome_cliente'] ?></td>
                    <td><?= $row['data_agenda'] ?></td>
                    <td><?= $row['hora_agenda'] ?></td>

                    <td>
                        <a class='btn btn-primary btn-sm'
                           href='editar.php?id_agenda=<?= $row["id_agenda"] ?>'>
                           Editar
                        </a>

                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('Deseja mesmo deletar este agendamento?');">
                            <input type="hidden" name="delete_id" value="<?= $row['id_agenda'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Deletar</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>
</body>
</html>