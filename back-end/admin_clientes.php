<?php
session_start();
include_once("config.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* =========================
   BLOQUEAR / DESBLOQUEAR CLIENTE
========================= */
if (isset($_POST['toggle_cliente'])) {
    $idUsuario = intval($_POST['toggle_cliente']);

    $conexao->query("
        UPDATE usuarios 
        SET ativo = IF(ativo = 1, 0, 1)
        WHERE id_usuario = $idUsuario
    ");

    header("Location: admin_clientes.php");
    exit;
}

/* =========================
   EXCLUIR CLIENTE ÓRFÃO
========================= */
if (isset($_POST['delete_orfao'])) {
    $idCliente = intval($_POST['delete_orfao']);

    $check = $conexao->query("
        SELECT id_usuario FROM usuarios WHERE id_cliente = $idCliente
    ");

    if ($check->num_rows === 0) {
        $conexao->query("DELETE FROM cliente WHERE id_cliente = $idCliente");
    }

    header("Location: admin_clientes.php");
    exit;
}

/* =========================
   LISTAGEM
========================= */
$result = $conexao->query("
    SELECT 
        c.id_cliente,
        c.nome,
        c.email,
        c.telefone,
        u.id_usuario,
        u.ativo
    FROM cliente c
    LEFT JOIN usuarios u ON u.id_cliente = c.id_cliente
    ORDER BY c.nome
");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Admin • Clientes</title>
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
        <a href="admin_clientes.php" class="active">Clientes</a>
        <a href="admin_barbeiros.php">Barbeiros</a>
    </aside>

    <!-- CONTEÚDO -->
    <main class="admin-content">

        <div class="table-box table-responsive">

            <h4 class="mb-4">Clientes</h4>

            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Status</th>
                        <th style="width:260px">Ações</th>
                    </tr>
                </thead>
                <tbody>

                <?php while ($c = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nome']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= htmlspecialchars($c['telefone']) ?></td>

                        <td>
                            <?php if ($c['id_usuario']): ?>
                                <?php if ($c['ativo']): ?>
                                    <span class="badge bg-success">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Bloqueado</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">
                                    Sem login
                                </span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($c['id_usuario']): ?>

                                <a href="admin_cliente_editar.php?id=<?= $c['id_cliente'] ?>"
                                   class="btn btn-warning btn-sm">
                                    Editar
                                </a>

                                <form method="POST" style="display:inline"
                                      onsubmit="return confirm('Deseja alterar o status deste cliente?');">
                                    <input type="hidden" name="toggle_cliente"
                                           value="<?= $c['id_usuario'] ?>">
                                    <button class="btn btn-secondary btn-sm">
                                        <?= $c['ativo'] ? 'Bloquear' : 'Desbloquear' ?>
                                    </button>
                                </form>

                            <?php else: ?>

                                <form method="POST"
                                      onsubmit="return confirm(
                                          'Este cliente não possui usuário.\nDeseja remover este registro órfão?'
                                      );">
                                    <input type="hidden" name="delete_orfao"
                                           value="<?= $c['id_cliente'] ?>">
                                    <button class="btn btn-danger btn-sm">
                                        Excluir órfão
                                    </button>
                                </form>

                            <?php endif; ?>
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
