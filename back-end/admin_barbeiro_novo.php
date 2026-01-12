<?php
session_start();
include_once("config.php");

require_once("auth.php");
requireLogin();

if (getUserType() !== 'admin') {
    header("Location: login.php");
    exit;
}

/* ======================
   CADASTRO DE BARBEIRO
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome  = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if ($nome === '' || $email === '' || $senha === '') {
        $erro = "Preencha todos os campos.";
    } else {

        // verifica email duplicado
        $check = $conexao->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $erro = "Email já cadastrado.";
        } else {

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            // cria barbeiro
            $stmtBarb = $conexao->prepare("
                INSERT INTO barbeiro (nome_barb, email, ativo)
                VALUES (?, ?, 1)
            ");
            $stmtBarb->bind_param("ss", $nome, $email);
            $stmtBarb->execute();

            $idBarb = $stmtBarb->insert_id;
            $stmtBarb->close();

            // cria usuário
            $stmtUser = $conexao->prepare("
                INSERT INTO usuarios (nome, email, senha, tipo, id_barb)
                VALUES (?, ?, ?, 'barbeiro', ?)
            ");
            $stmtUser->bind_param("sssi", $nome, $email, $senhaHash, $idBarb);
            $stmtUser->execute();
            $stmtUser->close();

            header("Location: admin_barbeiros.php?sucesso=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Barbeiro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark text-white">

<nav class="navbar navbar-dark bg-black">
    <div class="container-fluid">
        <a href="admin_barbeiros.php" class="btn btn-outline-light">Voltar</a>
        <span class="navbar-brand">Cadastrar Barbeiro</span>
    </div>
</nav>

<div class="container mt-5">
    <div class="card bg-secondary p-4 mx-auto" style="max-width:500px;">

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Senha inicial</label>
                <input type="password" name="senha" class="form-control" required>
            </div>

            <button class="btn btn-warning w-100">
                Criar Barbeiro
            </button>
        </form>

    </div>
</div>

</body>
</html>
