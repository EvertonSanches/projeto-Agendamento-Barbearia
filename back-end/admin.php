<?php
session_start();
include_once("config.php");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}


if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $conexao->query("DELETE FROM agenda WHERE id_agenda = $id");
}
$search = "";
if (!empty($_GET['search'])) {
    $search = $conexao->real_escape_string($_GET['search']);

    $sql = "
        SELECT 
            a.id_agenda,
            a.data_agenda,
            a.hora_agenda,
            c.nome AS nome_cliente,
            b.nome_barb AS nome_barbeiro
        FROM agenda a
        JOIN cliente c ON a.id_cliente = c.id_cliente
        JOIN barbeiro b ON a.id_barb = b.id_barb
        WHERE 
            a.id_agenda LIKE '%$search%'
            OR c.nome LIKE '%$search%'
            OR b.nome_barb LIKE '%$search%'
            OR DATE_FORMAT(a.data_agenda, '%d/%m/%Y') LIKE '%$search%'
            OR a.data_agenda LIKE '%$search%'
            OR a.hora_agenda LIKE '%$search%'
        ORDER BY a.data_agenda, a.hora_agenda
    ";
} else {
    $sql = "
        SELECT 
            a.id_agenda,
            a.data_agenda,
            a.hora_agenda,
            c.nome AS nome_cliente,
            b.nome_barb AS nome_barbeiro
        FROM agenda a
        JOIN cliente c ON a.id_cliente = c.id_cliente
        JOIN barbeiro b ON a.id_barb = b.id_barb
        ORDER BY a.data_agenda, a.hora_agenda
    ";
}

$result = $conexao->query($sql);



?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Administrador</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .box-search{
            display: flex;
        }
    </style>
</head>

<body class="bg-dark text-white">
    <nav class="navbar navbar-expand-lg navbar-dark bg-warning">
    <div class="container-fluid">
        <a class="navbar-brand"href="/agendamento-barbearia/login.html">Home</a> 
        <div class="d-flex">
            <a href="sair.php" class="btn btn-danger me-5">Sair</a>
        </div>
    </div>
    </nav>
    <div class="container mt-5">
        <h1>Controle de agendamentos</h1>
        <br><br>
        <div class="search-box d-flex align-items-center gap-2">
            <input type="search" class="form-control w-25" placeholder="Pesquisar agendamento" id="pesquisar">
            <button onclick="searchData()" class="btn btn-warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                </svg>
            </button>
        </div>
        <br>
        <table class="table table-dark table-bordered border-warning">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Barbeiro</th>
                    <th>Dia</th>
                    <th>Hora</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $row['nome_cliente'] ?></td>
                    <td><?= $row['nome_barbeiro'] ?></td>
                    <td><?= $row['data_agenda'] ?></td>
                    <td><?= $row['hora_agenda'] ?></td>

                    <td>
                        <a class="btn btn-primary"
                           href="editar.php?id_agenda=<?= $row['id_agenda'] ?>">
                           Editar
                        </a>
                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('Deseja realmente deletar este agendamento?');">
                            <input type="hidden" name="delete_id" value="<?= $row['id_agenda'] ?>">
                            <button type="submit" class="btn btn-danger">Deletar</button>
                        </form>

                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
<script>
    let search = document.getElementById('pesquisar');

    search.addEventListener("keydown", function(event){
        if (event.key === "Enter"){
            event.preventDefault(); 
            searchData();
        }
    });

    function searchData(){
        window.location = 'admin.php?search=' + encodeURIComponent(search.value);
    }
</script>

</html>
