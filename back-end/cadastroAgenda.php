<?php
session_start();
include_once("config.php");

require_once("auth.php");
requireLogin();

if (getUserType() !== 'cliente') {
    header("Location: login.php");
    exit;
}

$idCliente = (int)$_SESSION['id_cliente'];

$sqlBarb = "SELECT id_barb, nome_barb FROM barbeiro WHERE ativo = 1";
$resBarb = $conexao->query($sqlBarb);

$sqlServ = "SELECT id_servico, nome FROM servicos";
$resServ = $conexao->query($sqlServ);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Novo Agendamento</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
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

        .dia-btn:hover,
        .hora-btn:hover {
            background: #2a2a2a;
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
            background: linear-gradient(90deg,
                    #2a2a2a 25%,
                    #3a3a3a 37%,
                    #2a2a2a 63%);
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
            <a class="navbar-brand" href="agendados.php">
                <i class="bi bi-arrow-left-circle"></i> Voltar
            </a>
            <a href="sair.php" class="btn btn-danger">
                <i class="bi bi-box-arrow-right"></i> Sair
            </a>
        </div>
    </nav>

    <div class="container table-container table-bg mt-5" style="max-width:600px;">
        <h1 class="mb-4"><i class="bi bi-calendar-plus"></i> Novo Agendamento</h1>

        <form action="salvarAgendamento.php" method="POST" id="form-agendamento">

            <div class="mb-3">
                <label class="form-label"><i class="bi bi-person-circle"></i> Barbeiro</label>
                <select name="id_barb" id="id_barb" class="form-control" required>
                    <option value="">Selecione</option>
                    <?php while ($b = $resBarb->fetch_assoc()) { ?>
                        <option value="<?= $b['id_barb'] ?>"><?= htmlspecialchars($b['nome_barb']) ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="bi bi-scissors"></i> Serviço</label>
                <select name="id_servico" id="id_servico" class="form-control" required>
                    <option value="">Selecione</option>
                    <?php while ($s = $resServ->fetch_assoc()) { ?>
                        <option value="<?= $s['id_servico'] ?>"><?= htmlspecialchars($s['nome']) ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="bi bi-calendar-week"></i> Dias disponíveis</label>
                <div id="dias-semana"></div>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="bi bi-calendar-event"></i> Data</label>
                <input type="date" name="data_agenda" id="data_agenda"
                    class="form-control"
                    min="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label"><i class="bi bi-clock"></i> Horários disponíveis</label>
                <div id="horarios-disponiveis"></div>
                <small id="erro-horario" class="text-danger d-none">
                    Selecione um horário para continuar
                </small>
            </div>

            <select name="hora_inicio" id="hora_inicio" required style="display:none;"></select>

            <button type="submit" class="btn btn-warning w-100">
                <i class="bi bi-check-circle"></i> Confirmar Agendamento
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
        const form = document.getElementById('form-agendamento');
        const erroHorario = document.getElementById('erro-horario');

        function gerarDiasSemana() {
            diasContainer.innerHTML = '';
            const agora = new Date();
            const horaAtual = agora.getHours();
            let dataBase = new Date(agora);

            if (horaAtual >= 18 || dataBase.getDay() === 0) {
                dataBase.setDate(dataBase.getDate() + 1);
            }

            for (let i = 0; i < 7; i++) {
                const data = new Date(dataBase);
                data.setDate(dataBase.getDate() + i);
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
            horaSelect.innerHTML = '';
            erroHorario.classList.add('d-none');
            horariosContainer.classList.remove('horario-erro');

            for (let i = 0; i < 4; i++) {
                const sk = document.createElement('div');
                sk.className = 'skeleton';
                horariosContainer.appendChild(sk);
            }

            const response = await fetch(
                `horariosDisponiveis.php?data=${dataInput.value}&id_barb=${barbeiroSel.value}&id_servico=${servicoSel.value}`
            );

            const horarios = await response.json();
            horariosContainer.innerHTML = '';

            if (!horarios.length) {
                horariosContainer.innerHTML = '<small class="text-muted">Nenhum horário disponível</small>';
                return;
            }

            horarios.forEach((hora, index) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'hora-btn';
                btn.textContent = hora;

                if (index === 0) btn.classList.add('proximo');

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

        servicoSel.addEventListener('change', () => {
            if (!barbeiroSel.value) return;
            gerarDiasSemana();
        });

        barbeiroSel.addEventListener('change', carregarHorarios);
        dataInput.addEventListener('change', carregarHorarios);
    </script>

</body>

</html>