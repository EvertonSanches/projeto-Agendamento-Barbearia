<?php
session_start();
include_once('config.php');

if (!isset($_SESSION['id_cliente'])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT * FROM barbeiro ORDER BY id_barb DESC";
$result2 = $conexao->query($sql);

if (isset($_POST["submit"])) {

    $hoje = date('Y-m-d');

    $dia        = trim($_POST["dia_corte"]);
    $hora       = trim($_POST["hora_corte"]);
    $barbSelect = intval($_POST["barbeiro_id"]);
    $cliente    = intval($_SESSION['id_cliente']);

    
    if ($dia < $hoje) {
        echo "<script>alert('A data não pode ser anterior a hoje!');</script>";
        exit;
    }

    
    $stmtCheck = $conexao->prepare("
        SELECT id_agenda 
        FROM agenda 
        WHERE data_agenda = ? 
          AND hora_agenda = ? 
          AND id_barb = ?
    ");
    $stmtCheck->bind_param("ssi", $dia, $hora, $barbSelect);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Horário já ocupado!');</script>";
        exit;
    }

    $stmtCheck->close();

    
    $stmtInsert = $conexao->prepare("
        INSERT INTO agenda (data_agenda, hora_agenda, id_cliente, id_barb)
        VALUES (?, ?, ?, ?)
    ");
    $stmtInsert->bind_param("ssii", $dia, $hora, $cliente, $barbSelect);

    if ($stmtInsert->execute()) {
        echo "<script>
            alert('Agendado com sucesso!');
            window.location.href = 'agendados.php';
        </script>";
    } else {
        echo "Erro ao agendar: " . $stmtInsert->error;
    }

    $stmtInsert->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/agendamento-barbearia/css/estiloBasico.css">
    <link rel="stylesheet" href="">
    <title>Cadastro</title>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false">  
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="d-flex">
            <a href="agendados.php" i class="bi bi-card-checklist btn-light me-2 btn-lg"> Agendamentos</a>
        </div>
        </div>    
    </nav>
    <div id="logo">
        <img src="/agendamento-barbearia/imagem/undraw_barber_utly.svg">
    </div>
    <br> <br>
    <div class="box">
        <form action="cadastroAgenda.php" method="POST" class="form">
            <fieldset>
                <legend><b>Agendamento</b></legend>
                <br>
                <div class="inputBox">
                    <label for="dia_corte"> <b> Dia Agendar</b></label>
                    <br>
                    <input type="date" name="dia_corte" id="data_nascimento" required>
                </div>
                <br> <br>
                <div class="inputBox">
                    <label for="hora_corte">Hora:</label>
                    <input type="time" min="09:00" max="18:00" step="3600" name="hora_corte" id="data_nascimento">
                </div>
                <br> <br>
                <label for="radio_barbeiro">Barbeiros</label>
                <br>
                <div id="select_Barb">
                    <?php
                    while ($barbeiro = mysqli_fetch_assoc($result2)) {
                        echo "
                        <label>
                            <input type='radio' name='barbeiro_id' value='{$barbeiro['id_barb']}'>
                            {$barbeiro['nome_barb']}
                        </label>";
                    }
                    ?>
                </div>
                <br><br>
                <input type="submit" name="submit" id="submit">
            </fieldset>
        </form>
    </div>
</body>

</html>