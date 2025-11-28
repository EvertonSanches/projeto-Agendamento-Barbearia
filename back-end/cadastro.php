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
    <title>Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/agendamento-barbearia/css/estiloBasico.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <div class="d-flex">
            <a href="login.php" class="bi bi-card-checklist btn-light me-2 btn-lg"> Login</a>
            <a href="login.php" class="bi bi-arrow-left-square btn-light btn-lg"> Voltar</a>
        </div>
    </div>    
</nav>

<div class="logo">
    <img src="/agendamento-barbearia/imagem/undraw_barber_utly.svg" alt="barbeiro efetuando corte"/>
</div>

<div class="box">
    <form action="cadastro.php" method="POST" class="form">
        <fieldset>
            <legend><b>Cadastro de Clientes</b></legend>
            <br>
            <div class="inputBox">
                <input type="text" name="nome" class="inputUser" required>
                <label for="nome" class="labelInput">Nome</label>
            </div>
            <br><br>
            <div class="inputBox">
                <input type="password" name="senha" class="inputUser" required>
                <label for="senha" class="labelInput">Senha</label>
            </div>
            <br><br>
            <div class="inputBox">
                <input type="email" name="email" class="inputUser" required>
                <label for="email" class="labelInput">Email</label>
            </div>
            <br><br>
            <div class="inputBox">
                <input type="tel" name="telefone" class="inputUser" placeholder="(00)00000-0000" maxlength="14" required>
                <label for="telefone" class="labelInput">Telefone</label>
            </div>
            <br><br>
            <div class="inputBox">
                <input type="text" name="cpf" class="inputUser" placeholder="000.000.000-00" maxlength="14" required>
                <label for="cpf" class="labelInput">CPF</label>
            </div>
            <br><br>
            <label for="data_nascimento"><b>Data de Nascimento</b></label>
            <input type="date" name="data_nascimento" required>
            <br><br><br>
            <input type="submit" name="submit" value="Cadastrar" class="btn btn-primary">
        </fieldset>
    </form>
</div>
</body>
</html>
