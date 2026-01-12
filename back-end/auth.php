<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("config.php");

function requireLogin()
{
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: ../login.php");
        exit;
    }

    global $conexao;

    $idUsuario = (int) $_SESSION['id_usuario'];

    $stmt = $conexao->prepare("
        SELECT ativo, tipo
        FROM usuarios
        WHERE id_usuario = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        session_destroy();
        header("Location: ../login.php");
        exit;
    }

    $usuario = $result->fetch_assoc();

    if ((int)$usuario['ativo'] !== 1) {
        session_destroy();
        header("Location: ../login.php");
        exit;
    }

    $_SESSION['tipo'] = $usuario['tipo'];
}

function getUserType()
{
    return $_SESSION['tipo'] ?? null;
}
