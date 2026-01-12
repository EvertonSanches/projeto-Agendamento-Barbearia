<?php
session_start();
include_once("config.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']);

$conexao->query("
    UPDATE cliente
    SET ativo = IF(ativo = 1, 0, 1)
    WHERE id_cliente = $id
");

header("Location: admin_clientes.php");
exit;
