<?php

   if (!empty($_GET['id_agenda'])) {

    include_once('config.php');

    
    $id = intval($_GET['id_agenda']);

    
    $sqlDelete = "DELETE FROM agenda WHERE id_agenda = $id";
    $resultDelete = $conexao->query($sqlDelete);

    if (!$resultDelete) {
        die("Erro ao deletar: " . $conexao->error);
    }
}
    header('Location: http://localhost/agendamento-barbearia/back-end/agendados.php');
?>