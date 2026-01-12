<?php
session_start();
include_once("config.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']);

$sql = "
    SELECT 
        a.data_agenda,
        a.hora_agenda,
        c.nome AS cliente
    FROM agenda a
    JOIN cliente c ON a.id_cliente = c.id_cliente
    WHERE a.id_barb = $id
    ORDER BY a.data_agenda, a.hora_agenda
";

$result = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Agenda do Barbeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark text-white">
<div class="container mt-5">

<h2>Agenda do Barbeiro</h2>

<table class="table table-dark table-hover">
<thead>
<tr>
    <th>Cliente</th>
    <th>Data</th>
    <th>Hora</th>
</tr>
</thead>

<tbody>
<?php while ($row = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td><?= $row['cliente'] ?></td>
    <td><?= $row['data_agenda'] ?></td>
    <td><?= $row['hora_agenda'] ?></td>
</tr>
<?php } ?>
</tbody>
</table>

<a href="admin_barbeiros.php" class="btn btn-secondary">Voltar</a>

</div>
</body>
</html>
