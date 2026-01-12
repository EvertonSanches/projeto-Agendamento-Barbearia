<?php
session_start();
include_once("config.php");

require_once("auth.php");
requireLogin();

if (getUserType() !== 'cliente') {
    header("Location: login.php");
    exit;
}

$idCliente = (int) $_SESSION['id_cliente'];

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
/* ===== DIAS (SEM SCROLL) ===== */
.dias-semana {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
}

.dia-btn {
    background: #1e1e1e;
    border: 1px solid #333;
    color: #fff;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
    cursor: pointer;
}

.dia-btn.active {
    background: #ffc107;
    color: #000;
    font-weight: bold;
}

/* ===== HORÁRIOS ===== */
.horarios {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.horario-btn {
    background: #1e1e1e;
    border: 1px solid #333;
    color: #fff;
    border-radius: 6px;
    padding: 8px 12px;
    cursor: pointer;
}

.horario-btn.active {
    background: #ffc107;
    color: #000;
    font-weight: bold;
}

.horario-btn.proximo {
    border: 2px solid #ffc107;
}

/* ===== SKELETON ===== */
.skeleton {
    width: 70px;
    height: 36px;
    border-radius: 6px;
    background: linear-gradient(90deg,#2a2a2a 25%,#3a3a3a 37%,#2a2a2a 63%);
    background-size: 400% 100%;
    animation: skeleton 1.4s ease infinite;
}

@keyframes skeleton {
    0% { background-position: 100% 0; }
    100% { background-position: 0 0; }
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

<form action="salvarAgendamento.php" method="POST" id="formAgendamento">

<!-- BARBEIRO -->
<div class="mb-3">
<label class="form-label"><i class="bi bi-person-circle"></i> Barbeiro</label>
<select name="id_barb" id="id_barb" class="form-control" required>
<option value="">Selecione</option>
<?php while ($b = $resBarb->fetch_assoc()) { ?>
<option value="<?= $b['id_barb'] ?>"><?= htmlspecialchars($b['nome_barb']) ?></option>
<?php } ?>
</select>
</div>

<!-- SERVIÇO -->
<div class="mb-3">
<label class="form-label"><i class="bi bi-scissors"></i> Serviço</label>
<select name="id_servico" id="id_servico" class="form-control" required>
<option value="">Selecione</option>
<?php while ($s = $resServ->fetch_assoc()) { ?>
<option value="<?= $s['id_servico'] ?>"><?= htmlspecialchars($s['nome']) ?></option>
<?php } ?>
</select>
</div>

<!-- DIAS -->
<div class="mb-3">
<label class="form-label"><i class="bi bi-calendar-event"></i> Dia</label>
<div id="diasSemana" class="dias-semana"></div>
<input type="date" name="data_agenda" id="data_agenda" class="form-control mt-2" required>
</div>

<!-- HORÁRIOS -->
<div class="mb-4">
<label class="form-label"><i class="bi bi-clock"></i> Horário</label>
<div id="horarios" class="horarios"></div>
<input type="hidden" name="hora_inicio" id="hora_inicio" required>
</div>

<button type="submit" class="btn btn-warning w-100">
<i class="bi bi-check-circle"></i> Confirmar Agendamento
</button>

</form>
</div>

<script>
const diasContainer   = document.getElementById('diasSemana');
const horariosDiv     = document.getElementById('horarios');
const dataInput       = document.getElementById('data_agenda');
const horaInput       = document.getElementById('hora_inicio');
const barbeiroSel     = document.getElementById('id_barb');
const servicoSel      = document.getElementById('id_servico');

/* ===== FORMATA DATA LOCAL (SEM UTC) ===== */
function formatarDataLocal(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth()+1).padStart(2,'0');
    const d = String(date.getDate()).padStart(2,'0');
    return `${y}-${m}-${d}`;
}

/* ===== BASE DE DIAS ===== */
const hoje = new Date();
let base = new Date(hoje);

if (base.getHours() >= 18) {
    base.setDate(base.getDate() + 1);
}

while (base.getDay() === 0) {
    base.setDate(base.getDate() + 1);
}

/* ===== GERAR DIAS ===== */
function gerarDias() {
    diasContainer.innerHTML = '';

    for (let i = 0; i < 7; i++) {
        const d = new Date(base);
        d.setDate(base.getDate() + i);

        if (d.getDay() === 0) continue;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'dia-btn';
        btn.textContent = d.toLocaleDateString('pt-BR', {
            weekday:'short', day:'2-digit', month:'2-digit'
        });

        btn.dataset.date = formatarDataLocal(d);

        btn.onclick = () => {
            document.querySelectorAll('.dia-btn')
                .forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            dataInput.value = btn.dataset.date;
            carregarHorarios();
        };

        diasContainer.appendChild(btn);
    }

    const primeiro = diasContainer.querySelector('.dia-btn');
    if (primeiro) primeiro.click();
}

/* ===== CARREGAR HORÁRIOS ===== */
async function carregarHorarios() {
    horariosDiv.innerHTML = '';
    horaInput.value = '';

    if (!dataInput.value || !barbeiroSel.value || !servicoSel.value) return;

    for (let i=0;i<6;i++){
        const sk = document.createElement('div');
        sk.className = 'skeleton';
        horariosDiv.appendChild(sk);
    }

    const res = await fetch(
        `horariosDisponiveis.php?data=${dataInput.value}&id_barb=${barbeiroSel.value}&id_servico=${servicoSel.value}`
    );

    const horarios = await res.json();
    horariosDiv.innerHTML = '';

    if (!horarios.length) {
        horariosDiv.innerHTML = '<span class="text-muted">Nenhum horário disponível</span>';
        return;
    }

    horarios.forEach((h, i) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'horario-btn';
        btn.textContent = h;

        if (i === 0) btn.classList.add('proximo');

        btn.onclick = () => {
            document.querySelectorAll('.horario-btn')
                .forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            horaInput.value = h;
        };

        horariosDiv.appendChild(btn);
    });
}

/* ===== EVENTOS ===== */
barbeiroSel.addEventListener('change', carregarHorarios);
servicoSel.addEventListener('change', carregarHorarios);

gerarDias();
</script>

</body>
</html>
