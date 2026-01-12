<?php
session_start();
include_once('config.php');

require_once("auth.php");
requireLogin();

$tipo = getUserType();

$idClienteSessao = $_SESSION['id_cliente'] ?? null;
$idBarbSessao    = $_SESSION['id_barb'] ?? null;

function homePorTipo($tipo)
{
    if ($tipo === 'barbeiro') return 'barbeiros.php';
    if ($tipo === 'admin') return 'admin.php';
    return 'agendados.php';
}

if (!isset($_GET['id_agenda'])) {
    header("Location: " . homePorTipo($tipo));
    exit;
}

$id = (int) $_GET['id_agenda'];

if ($tipo === 'admin') {

    $sql = "SELECT * FROM agenda WHERE id_agenda = $id";
} elseif ($tipo === 'barbeiro') {

    if (!$idBarbSessao) {
        header("Location: barbeiros.php");
        exit;
    }

    $sql = "
        SELECT *
        FROM agenda
        WHERE id_agenda = $id
          AND id_barb = $idBarbSessao
    ";
} else {

    if (!$idClienteSessao) {
        header("Location: agendados.php");
        exit;
    }

    $sql = "
        SELECT *
        FROM agenda
        WHERE id_agenda = $id
          AND id_cliente = $idClienteSessao
    ";
}

$res = $conexao->query($sql);
if (!$res || $res->num_rows === 0) {
    header("Location: " . homePorTipo($tipo));
    exit;
}

$agenda = $res->fetch_assoc();

$dataAgenda  = $agenda['data_agenda'];
$horaAgenda  = substr($agenda['hora_inicio'], 0, 5);
$idBarbAtual = $agenda['id_barb'];
$idServico   = $agenda['id_servico'];

$sqlBarb = "
    SELECT id_barb, nome_barb
    FROM barbeiro
    WHERE ativo = 1 OR id_barb = $idBarbAtual
";
$resBarb = $conexao->query($sqlBarb);

$sqlServ = "SELECT id_servico, nome FROM servicos";
$resServ = $conexao->query($sqlServ);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Agendamento</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/agendamento-barbearia/css/estiloBasico.css">

    <style>
        #dias-semana,
        #horarios-disponiveis {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .dia-btn,
        .hora-btn {
            padding: 8px 12px;
            border-radius: 8px;
            background: #1e1e1e;
            color: #fff;
            border: 1px solid #333;
            cursor: pointer;
            font-size: 14px;
        }

        .dia-btn.active,
        .hora-btn.active {
            background: #ffc107;
            color: #000;
            font-weight: bold;
        }

        .hora-btn.proximo {
            border: 2px solid #ffc107;
        }

        .skeleton {
            width: 70px;
            height: 36px;
            border-radius: 8px;
            background: linear-gradient(90deg, #2a2a2a 25%, #3a3a3a 37%, #2a2a2a 63%);
            background-size: 400% 100%;
            animation: skeleton 1.4s ease infinite;
        }

        @keyframes skeleton {
            0% {
                background-position: 100% 0;
            }

            100% {
                background-position: 0 0;
            }
        }

        .horario-erro {
            border: 2px solid #dc3545;
            padding: 10px;
            border-radius: 8px;
        }
    </style>
</head>

<body class="list-bg">

    <nav class="navbar navbar-dark bg-secondary">
        <div class="container-fluid">
            <a href="javascript:history.back()" class="navbar-brand">Voltar</a>
            <a href="sair.php" class="btn btn-danger">Sair</a>
        </div>
    </nav>

    <div class="container table-container table-bg mt-5" style="max-width:600px;">
        <h1 class="mb-4">Editar Agendamento</h1>

        <form action="salvarEdicao.php" method="POST" id="form-edicao">

            <div class="mb-3">
                <label class="form-label"><i class="bi bi-person"></i> Barbeiro</label>
                <select name="id_barb" id="id_barb" class="form-control" required>
                    <?php while ($b = $resBarb->fetch_assoc()) { ?>
                        <option value="<?= $b['id_barb'] ?>" <?= $b['id_barb'] == $idBarbAtual ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['nome_barb']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>


            <div class="mb-3">
                <label class="form-label"><i class="bi bi-scissors"></i> Serviço</label>
                <select name="id_servico" id="id_servico" class="form-control" required>
                    <?php while ($s = $resServ->fetch_assoc()) { ?>
                        <option value="<?= $s['id_servico'] ?>" <?= $s['id_servico'] == $idServico ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nome']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>


            <div class="mb-3">
                <label class="form-label"><i class="bi bi-calendar-week"></i> Dias disponíveis</label>
                <div id="dias-semana"></div>
            </div>


            <div class="mb-3">
                <label class="form-label"><i class="bi bi-calendar-event"></i> Data</label>
                <input type="date"
                    name="data_agenda"
                    id="data_agenda"
                    class="form-control"
                    value="<?= $dataAgenda ?>"
                    required>
            </div>


            <div class="mb-4">
                <label class="form-label"><i class="bi bi-clock"></i> Horários disponíveis</label>
                <div id="horarios-disponiveis"></div>
                <small id="erro-horario" class="text-danger d-none">
                    Selecione um horário para continuar
                </small>
            </div>

            <select name="hora_inicio" id="hora_inicio" required style="display:none;">
                <option value="<?= $horaAgenda ?>" selected><?= $horaAgenda ?></option>
            </select>

            <input type="hidden" name="id_agenda" value="<?= $id ?>">

            <button type="submit" class="btn btn-warning w-100">
                <i class="bi bi-save"></i> Salvar Alterações
            </button>

        </form>
    </div>

    <script>
        const dataInput = document.getElementById('data_agenda');
        const horaSelect = document.getElementById('hora_inicio');
        const barbeiroSel = document.getElementById('id_barb');
        const servicoSel = document.getElementById('id_servico');
        const diasContainer = document.getElementById('dias-semana');
        const horariosContainer = document.getElementById('horarios-disponiveis');
        const erroHorario = document.getElementById('erro-horario');
        const form = document.getElementById('form-edicao');


        const hoje = new Date();
        const horaAtual = hoje.getHours();

        let dataBaseDias = new Date(hoje);

        if (horaAtual >= 18 || dataBaseDias.getDay() === 0) {
            dataBaseDias.setDate(dataBaseDias.getDate() + 1);
        }

        while (dataBaseDias.getDay() === 0) {
            dataBaseDias.setDate(dataBaseDias.getDate() + 1);
        }


        function gerarDiasSemana() {
            diasContainer.innerHTML = '';

            for (let i = 0; i < 7; i++) {
                const data = new Date(dataBaseDias);
                data.setDate(dataBaseDias.getDate() + i);

                if (data.getDay() === 0) continue;

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'dia-btn';
                btn.textContent = data.toLocaleDateString('pt-BR', {
                    weekday: 'short',
                    day: '2-digit',
                    month: '2-digit'
                });

                btn.dataset.date = data.toISOString().split('T')[0];

                btn.onclick = () => {
                    document.querySelectorAll('.dia-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    dataInput.value = btn.dataset.date;
                    carregarHorarios();
                };

                diasContainer.appendChild(btn);
            }

            const primeiro = diasContainer.querySelector('.dia-btn');
            if (primeiro) primeiro.click();
        }


        async function carregarHorarios() {
            horariosContainer.innerHTML = '';
            erroHorario.classList.add('d-none');

            for (let i = 0; i < 4; i++) {
                const sk = document.createElement('div');
                sk.className = 'skeleton';
                horariosContainer.appendChild(sk);
            }

            const res = await fetch(
                `horariosDisponiveis.php?data=${dataInput.value}&id_barb=${barbeiroSel.value}&id_servico=${servicoSel.value}`
            );

            const horarios = await res.json();
            horariosContainer.innerHTML = '';

            if (!horarios.length) {
                horariosContainer.innerHTML = '<small class="text-muted">Nenhum horário disponível</small>';
                return;
            }

            horarios.forEach((hora, idx) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'hora-btn';
                btn.textContent = hora;

                if (hora === "<?= $horaAgenda ?>") btn.classList.add('active');
                if (idx === 0) btn.classList.add('proximo');

                btn.onclick = () => {
                    document.querySelectorAll('.hora-btn').forEach(h => h.classList.remove('active'));
                    btn.classList.add('active');
                    horaSelect.innerHTML = `<option value="${hora}" selected>${hora}</option>`;
                };

                horariosContainer.appendChild(btn);
            });
        }


        form.addEventListener('submit', e => {
            if (!horaSelect.value) {
                e.preventDefault();
                erroHorario.classList.remove('d-none');
                horariosContainer.classList.add('horario-erro');
            }
        });

        gerarDiasSemana();
        carregarHorarios();
    </script>

</body>

</html>