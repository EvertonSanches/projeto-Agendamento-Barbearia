<?php
session_start();
include_once("config.php");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_barbeiros.php");
    exit;
}

$idBarb = intval($_GET['id']);

/* =========================
   SALVAR ALTERAÇÕES
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome']);
    $email = trim($_POST['email']);

    // Atualiza barbeiro
    $stmt1 = $conexao->prepare("
        UPDATE barbeiro SET nome_barb = ?
        WHERE id_barb = ?
    ");
    $stmt1->bind_param("si", $nome, $idBarb);
    $stmt1->execute();
    $stmt1->close();

    // Atualiza email do usuário
    $stmt2 = $conexao->prepare("
        UPDATE usuarios SET email = ?
        WHERE id_barb = ?
    ");
    $stmt2->bind_param("si", $email, $idBarb);
    $stmt2->execute();
    $stmt2->close();

    header("Location: admin_barbeiros.php");
    exit;
}

/* =========================
   DADOS ATUAIS
========================= */
$stmt = $conexao->prepare("
    SELECT b.nome_barb, u.email
    FROM barbeiro b
    LEFT JOIN usuarios u ON u.id_barb = b.id_barb
    WHERE b.id_barb = ?
");
$stmt->bind_param("i", $idBarb);
$stmt->execute();
$result = $stmt->get_result();
$barbeiro = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Editar Barbeiro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/agendamento-barbearia/css/estiloBasico.css">
</head>

<body class="bg-dark text-white">

<nav class="navbar navbar-dark bg-warning px-4">
    <span class="navbar-brand fw-bold text-dark">Editar Barbeiro</span>
    <a href="admin_barbeiros.php" class="btn btn-dark">Voltar</a>
</nav>

<div class="container d-flex justify-content-center align-items-center mt-5">
    <div class="card cadastro-card">
        <div class="card-body">

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" class="form-control"
                           value="<?= htmlspecialchars($barbeiro['nome_barb']) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($barbeiro['email']) ?>" required>
                </div>

                <button type="submit" class="btn btn-warning w-100">
                    Salvar Alterações
                </button>

            </form>

        </div>
    </div>
</div>

</body>
</html>
