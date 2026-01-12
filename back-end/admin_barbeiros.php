<?php
session_start();
include_once("config.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* =========================
   EXCLUIR BARBEIRO
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_barbeiro'])) {
    $idBarb = intval($_POST['delete_barbeiro']);

    // Remove agendamentos
    $conexao->query("DELETE FROM agenda WHERE id_barb = $idBarb");

    // Remove login
    $conexao->query("DELETE FROM usuarios WHERE id_barb = $idBarb");

    // Remove barbeiro
    $conexao->query("DELETE FROM barbeiro WHERE id_barb = $idBarb");

    header("Location: admin_barbeiros.php");
    exit;
}

/* =========================
   LISTAGEM
========================= */
$result = $conexao->query("
    SELECT b.id_barb, b.nome_barb, u.email
    FROM barbeiro b
    LEFT JOIN usuarios u ON u.id_barb = b.id_barb
    ORDER BY b.nome_barb
");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Admin • Barbeiros</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

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

    .table-box {
        background: rgba(0, 0, 0, .75);
        padding: 20px;
        border-radius: 12px;
    }
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-black fixed-top">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold text-warning">
            Painel Administrativo
        </span>
        <a href="sair.php" class="btn btn-danger">Sair</a>
    </div>
</nav>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <a href="admin.php">Dashboard</a>
        <a href="admin.php">Agendamentos</a>
        <a href="admin_clientes.php">Clientes</a>
        <a href="admin_barbeiros.php" class="active">Barbeiros</a>
    </aside>

    <!-- CONTEÚDO -->
    <main class="admin-content">

        <div class="table-box table-responsive">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Barbeiros</h4>
                <a href="admin_barbeiro_novo.php" class="btn btn-success">
                    Novo Barbeiro
                </a>
            </div>

            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th style="width: 220px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($b = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['nome_barb']) ?></td>
                        <td><?= htmlspecialchars($b['email'] ?? '-') ?></td>
                        <td>
                            <a href="admin_barbeiro_editar.php?id=<?= $b['id_barb'] ?>"
                               class="btn btn-warning btn-sm">
                                Editar
                            </a>

                            <form method="POST" style="display:inline"
                                  onsubmit="return confirm(
                                      'ATENÇÃO!\n\n' +
                                      'Todos os agendamentos deste barbeiro serão apagados.\n\n' +
                                      'Deseja continuar?'
                                  );">
                                <input type="hidden" name="delete_barbeiro" value="<?= $b['id_barb'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        </div>

    </main>

</div>

</body>
</html>
