<?php
session_start();
include_once("config.php");

if (isset($_POST["submit"])) {


    $nome       = $conexao->real_escape_string(trim($_POST["nome"]));
    $senhaHash  = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $email      = $conexao->real_escape_string(trim($_POST["email"]));
    $telefone   = $conexao->real_escape_string(trim($_POST["telefone"]));
    $cpf        = $conexao->real_escape_string(trim($_POST["cpf"]));
    $data_nasc  = $_POST["data_nascimento"];


    $stmtCheck = $conexao->prepare("SELECT id_cliente FROM cliente WHERE email = ?");
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        echo "Email já cadastrado!";
        exit();
    }
    $stmtCheck->close();



    $stmt = $conexao->prepare("
        INSERT INTO cliente (nome, senha, cpf, data_nasc, telefone, email)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssss", $nome, $senhaHash, $cpf, $data_nasc, $telefone, $email);

    if (!$stmt->execute()) {
        echo "Erro ao cadastrar cliente: " . $stmt->error;
        exit();
    }


    $id_cliente = $stmt->insert_id;
    $stmt->close();



    $stmtUser = $conexao->prepare("
        INSERT INTO usuarios (nome, email, senha, tipo, id_cliente)
        VALUES (?, ?, ?, 'cliente', ?)
    ");
    $stmtUser->bind_param("sssi", $nome, $email, $senhaHash, $id_cliente);

    if (!$stmtUser->execute()) {
        echo "Erro ao criar usuário: " . $stmtUser->error;
        exit();
    }

    $stmtUser->close();


    header("Location: login.php?cadastro=sucesso");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Cliente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/agendamento-barbearia/css/estiloBasico.css">
</head>

<body class="bg-dark">

    <nav class="navbar navbar-expand-lg navbar-dark bg-black">
        <div class="container">
            <a href="/agendamento-barbearia/index.php" class="navbar-brand">Barbearia GC</a>
            <a href="login.php" class="btn btn-outline-light">Voltar</a>
        </div>
    </nav>

    <div class="container d-flex justify-content-center align-items-center min-vh-100">

        <div class="card cadastro-card login-card">

            <div class="card-body">

                <h2 class="text-center mb-4">Cadastro de Cliente</h2>

                <form action="cadastro.php" method="POST">

                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="tel" name="telefone" class="form-control" placeholder="(00) 00000-0000" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">CPF</label>
                        <input type="text" name="cpf" class="form-control" placeholder="000.000.000-00" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Data de Nascimento</label>
                        <input type="date" name="data_nascimento" class="form-control" required>
                    </div>

                    <button type="submit" name="submit" class="btn btn-warning w-100">
                        Cadastrar
                    </button>

                </form>

            </div>
        </div>

    </div>

</body>

</html>