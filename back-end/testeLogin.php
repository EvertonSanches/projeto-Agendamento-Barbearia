<?php
session_start();
include_once("config.php");

/* =========================
   VALIDAÇÃO BÁSICA
========================= */
if (empty($_POST['email']) || empty($_POST['senha'])) {
    header("Location: login.php");
    exit;
}

$email = trim($_POST['email']);
$senha = trim($_POST['senha']);

/* =========================
   BUSCA USUÁRIO (BASE)
========================= */
$stmt = $conexao->prepare("
    SELECT id_usuario, email, senha, tipo, ativo
    FROM usuarios
    WHERE email = ?
    LIMIT 1
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Email ou senha incorretos!";
    exit;
}

$usuario = $result->fetch_assoc();

/* =========================
   SENHA
========================= */
if (!password_verify($senha, $usuario['senha'])) {
    echo "Email ou senha incorretos!";
    exit;
}

/* =========================
   ATIVO
========================= */
if ((int)$usuario['ativo'] !== 1) {
    echo "Usuário inativo. Procure a administração.";
    exit;
}

/* =========================
   SESSÃO BASE
========================= */
$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['email']      = $usuario['email'];
$_SESSION['tipo']       = $usuario['tipo'];

/* =========================
   CLIENTE
========================= */
if ($usuario['tipo'] === 'cliente') {

    $stmt = $conexao->prepare("
        SELECT id_cliente, nome
        FROM cliente
        WHERE email = ? AND ativo = 1
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resCliente = $stmt->get_result();

    if ($resCliente->num_rows !== 1) {
        echo "Cliente não encontrado.";
        exit;
    }

    $c = $resCliente->fetch_assoc();

    $_SESSION['id_cliente'] = $c['id_cliente'];
    $_SESSION['nome']       = $c['nome'];

    header("Location: agendados.php");
    exit;
}

/* =========================
   BARBEIRO
========================= */
if ($usuario['tipo'] === 'barbeiro') {

    $stmt = $conexao->prepare("
        SELECT id_barb, nome
        FROM usuarios
        WHERE id_usuario = ? AND ativo = 1
        LIMIT 1
    ");
    $stmt->bind_param("i", $usuario['id_usuario']);
    $stmt->execute();
    $resBarb = $stmt->get_result();

    if ($resBarb->num_rows !== 1) {
        echo "Barbeiro não encontrado.";
        exit;
    }

    $b = $resBarb->fetch_assoc();

    $_SESSION['id_barb'] = $b['id_barb'];
    $_SESSION['nome']    = $b['nome_barb'];

    header("Location: barbeiros.php");
    exit;
}
/* =========================
   ADMIN
========================= */
if ($usuario['tipo'] === 'admin') {
    header("Location: admin.php");
    exit;
}

/* =========================
   FALLBACK
========================= */
echo "Erro inesperado ao autenticar.";
exit;
