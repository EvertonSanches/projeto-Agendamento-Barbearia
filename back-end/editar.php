<?php
session_start();
include_once('config.php');


if (!isset($_SESSION['tipo'])) {
    header("Location: login.php");
    exit();
}

$tipo = $_SESSION['tipo'];
$idClienteSessao = $_SESSION['id_cliente'] ?? null;
$idBarbSessao    = $_SESSION['id_barb'] ?? null;


if (!isset($_GET['id_agenda'])) {
    header("Location: agendados.php");
    exit();
}

$id = intval($_GET['id_agenda']);




if ($tipo === 'admin') {

    $sqlSelect = "SELECT * FROM agenda WHERE id_agenda = $id";

} elseif ($tipo === 'barbeiro') {

    $sqlSelect = "SELECT * FROM agenda WHERE id_agenda = $id AND id_barb = $idBarbSessao";

} else {

    $sqlSelect = "SELECT * FROM agenda WHERE id_agenda = $id AND id_cliente = $idClienteSessao";
}

$result = $conexao->query($sqlSelect);


if ($result->num_rows == 0) {
    header("Location: agendados.php");
    exit();
}

$user_data = $result->fetch_assoc();

$data      = $user_data['data_agenda'];
$hora      = $user_data['hora_agenda'];
$idBarb    = $user_data['id_barb'];


$sqlBarb = "SELECT * FROM barbeiro";
$result2 = $conexao->query($sqlBarb);

if (!$result2) {
    die("Erro ao carregar barbeiros: " . $conexao->error);
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/agendamento-barbearia/css/estiloBasico.css">
    <title>Editar Agendamento</title>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <div class="d-flex">
            <a href="javascript:history.back()" class="btn btn-light btn-lg">
                Voltar
            </a>
        </div>
    </div>    
</nav>

<div id="logo">
    <img src="/agendamento-barbearia/imagem/undraw_barber_utly.svg">
</div>

<br><br>

<div class="box">
    <form action="salvarEdicao.php" method="POST" class="form">
        <fieldset>
            <legend><b>Editar Agendamento</b></legend>
            <br>

           
            <div class="inputBox">
                <label><b>Nova Data</b></label>
                <input type="date" name="dia_corte" required value="<?= $data ?>">
            </div>

            <br><br>

         
            <div class="inputBox">
                <label><b>Nova Hora</b></label>
                <input type="time" min="09:00" max="18:00" step="3600" name="hora_corte" required value="<?= $hora ?>">
            </div>

            <br><br>

      
            <label><b>Escolher Barbeiro</b></label><br>

            <?php while ($row = mysqli_fetch_assoc($result2)) { ?>
                <label>
                    <input type="radio" 
                        name="barbeiro_id"
                        value="<?= $row['id_barb'] ?>"
                        <?= ($row['id_barb'] == $idBarb) ? 'checked' : '' ?>>

                    <?= $row['nome_barb'] ?>
                </label>
                <br>
            <?php } ?>

            <br><br>

            
            <input type="hidden" name="id_agenda" value="<?= $id ?>">

            <input type="submit" name="submit" id="submit" value="Salvar Alterações">

        </fieldset>
    </form>
</div>

</body>
</html>
