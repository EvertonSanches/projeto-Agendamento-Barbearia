<?php
session_start();
include_once("config.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);

    $stmt = $conexao->prepare("
        UPDATE cliente
        SET nome = ?, email = ?, telefone = ?
        WHERE id_cliente = ?
    ");
    $stmt->bind_param("sssi", $nome, $email, $telefone, $id);
    $stmt->execute();

    header("Location: admin_clientes.php");
    exit;
}

$cliente = $conexao->query("
    SELECT * FROM cliente WHERE id_cliente = $id
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark text-white">

<div class="container mt-5">
    <h2>Editar Cliente</h2>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label>Nome</label>
            <input type="text" name="nome" class="form-control" value="<?= $cliente['nome'] ?>" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= $cliente['email'] ?>" required>
        </div>

        <div class="mb-3">
            <label>Telefone</label>
            <input type="text" name="telefone" class="form-control" value="<?= $cliente['telefone'] ?>">
        </div>

        <button class="btn btn-warning">Salvar</button>
        <a href="admin_clientes.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

</body>
</html>
