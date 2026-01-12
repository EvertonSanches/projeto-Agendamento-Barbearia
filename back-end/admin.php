<?php
session_start();
include_once("config.php");

require_once("auth.php");
requireLogin();

if (getUserType() !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* ======================
   DELETE DE AGENDAMENTO
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);

    $stmt = $conexao->prepare("DELETE FROM agenda WHERE id_agenda = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin.php");
    exit;
}

$hoje = date('Y-m-d');

/* ======================
   MÉTRICAS
====================== */
$agHoje = $conexao->query("
    SELECT COUNT(*) total FROM agenda WHERE data_agenda = '$hoje'
")->fetch_assoc()['total'];

$agAtrasados = $conexao->query("
    SELECT COUNT(*) total FROM agenda WHERE data_agenda < '$hoje'
")->fetch_assoc()['total'];

$agSemana = $conexao->query("
    SELECT COUNT(*) total 
    FROM agenda 
    WHERE YEARWEEK(data_agenda, 1) = YEARWEEK(CURDATE(), 1)
")->fetch_assoc()['total'];

$barbeiros = $conexao->query("
    SELECT COUNT(*) total FROM barbeiro
")->fetch_assoc()['total'];

/* ======================
   FILTRO
====================== */
$filtro = $_GET['filtro'] ?? '';
$where = "";

if ($filtro === 'hoje') {
    $where = "WHERE a.data_agenda = '$hoje'";
}
if ($filtro === 'atrasados') {
    $where = "WHERE a.data_agenda < '$hoje'";
}
if ($filtro === 'semana') {
    $where = "WHERE YEARWEEK(a.data_agenda, 1) = YEARWEEK(CURDATE(), 1)";
}

/* ======================
   LISTAGEM
====================== */
$sql = "
    SELECT 
        a.id_agenda,
        a.data_agenda,
        a.hora_inicio,
        c.nome AS nome_cliente,
        b.nome_barb AS nome_barbeiro
    FROM agenda a
    JOIN cliente c ON a.id_cliente = c.id_cliente
    JOIN barbeiro b ON a.id_barb = b.id_barb
    $where
    ORDER BY a.data_agenda, a.hora_inicio
";

$result = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #0e0e0e;
            color: #fff;
            padding-top: 60px;
        }

        .admin-layout {
            display: flex;
            min-height: calc(100vh - 60px);
        }

        .admin-sidebar {
            width: 220px;
            background: #111;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .admin-sidebar a {
            color: #ccc;
            text-decoration: none;
            padding: 10px;
            border-radius: 6px;
        }

        .admin-sidebar a.active,
        .admin-sidebar a:hover {
            background: #ffc107;
            color: #000;
            font-weight: bold;
        }

        .admin-content {
            flex: 1;
            padding: 30px;
        }

        .card-metric {
            background: #111;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .card-metric h2 {
            font-size: 2.2rem;
            margin: 0;
        }

        .card-metric span {
            color: #aaa;
        }

        .data-passada {
            color: #ff4d4d;
            font-weight: bold;
        }

        .data-hoje {
            color: #ffc107;
            font-weight: bold;
        }

        .table-box {
            background: rgba(0, 0, 0, .75);
            padding: 20px;
            border-radius: 12px;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark bg-black fixed-top">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold text-warning">
            Painel Administrativo
        </span>
        <a href="sair.php" class="btn btn-danger">Sair</a>
    </div>
</nav>

<div class="admin-layout">

    <aside class="admin-sidebar">
        <a href="admin.php" class="<?= !$filtro ? 'active' : '' ?>">Dashboard</a>
        <a href="admin.php">Agendamentos</a>
        <a href="admin_clientes.php">Clientes</a>
        <a href="admin_barbeiros.php">Barbeiros</a>
    </aside>

    <main class="admin-content">

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <a href="admin.php?filtro=hoje" class="text-decoration-none">
                    <div class="card-metric">
                        <h2><?= $agHoje ?></h2>
                        <span>Hoje</span>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="admin.php?filtro=semana" class="text-decoration-none">
                    <div class="card-metric">
                        <h2 class="text-warning"><?= $agSemana ?></h2>
                        <span>Semana</span>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="admin.php?filtro=atrasados" class="text-decoration-none">
                    <div class="card-metric">
                        <h2 class="text-danger"><?= $agAtrasados ?></h2>
                        <span>Atrasados</span>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="admin_barbeiros.php" class="text-decoration-none">
                    <div class="card-metric">
                        <h2><?= $barbeiros ?></h2>
                        <span>Barbeiros</span>
                    </div>
                </a>
            </div>
        </div>

        <div class="table-box table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Barbeiro</th>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) {

                        $classe = '';
                        if ($row['data_agenda'] < $hoje) {
                            $classe = 'data-passada';
                        } elseif ($row['data_agenda'] === $hoje) {
                            $classe = 'data-hoje';
                        }

                        // 🔹 CORREÇÃO SOMENTE DE EXIBIÇÃO
                        $dataFormatada = date('d/m/Y', strtotime($row['data_agenda']));
                        $horaFormatada = date('H:i', strtotime($row['hora_inicio']));
                    ?>
                        <tr>
                            <td><?= $row['nome_cliente'] ?></td>
                            <td><?= $row['nome_barbeiro'] ?></td>
                            <td class="<?= $classe ?>"><?= $dataFormatada ?></td>
                            <td><?= $horaFormatada ?></td>
                            <td>
                                <a href="editar.php?id_agenda=<?= $row['id_agenda'] ?>"
                                   class="btn btn-sm btn-warning">Editar</a>

                                <form method="POST" style="display:inline"
                                      onsubmit="return confirm('Deseja deletar este agendamento?');">
                                    <input type="hidden" name="delete_id" value="<?= $row['id_agenda'] ?>">
                                    <button class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>
