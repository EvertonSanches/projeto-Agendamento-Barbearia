<?php
session_start();
include_once("config.php");

if (!isset($_POST['email'], $_POST['senha'])) {
    header("Location: back-end/login.php");
    exit;
}

$email = trim($_POST['email']);
$senha = trim($_POST['senha']);

$stmt = $conexao->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Email ou senha incorretos!";
    exit;
}

$usuario = $result->fetch_assoc();


if (!password_verify($senha, $usuario['senha'])) 
{ echo "Email ou senha incorretos!";
    exit;
}


$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['nome']       = $usuario['nome'];
$_SESSION['email']      = $usuario['email'];
$_SESSION['tipo']       = $usuario['tipo'];

if ($usuario['tipo'] === 'cliente') {
    $_SESSION['id_cliente'] = $usuario['id_cliente'];
    header("Location: agendados.php");
    exit;
}

if ($usuario['tipo'] === 'barbeiro') {
    $_SESSION['id_barb'] = $usuario['id_barb'];
    header("Location: barbeiro.php");
    exit;
}

if ($usuario['tipo'] === 'admin') {
    header("Location: admin.php");
    exit;
}

echo "Erro inesperado: tipo de usuário inválido.";
