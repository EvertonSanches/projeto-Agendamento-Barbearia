<?php
session_start();
include_once('config.php');

require_once("auth.php");
requireLogin();

$tipo = getUserType();

$idClienteSessao = $_SESSION['id_cliente'] ?? null;
$idBarbSessao    = $_SESSION['id_barb'] ?? null;

function homePorTipo($tipo) {
    if ($tipo === 'barbeiro') return 'barbeiros.php';
    if ($tipo === 'admin') return 'admin.php';
    return 'agendados.php';
}

if (!isset($_GET['id_agenda'])) {
    header("Location: " . homePorTipo($tipo));
    exit;
}

$idAgenda = (int) $_GET['id_agenda'];

/* ===== PERMISSÃO ===== */
if ($tipo === 'admin') {

    $sql = "SELECT * FROM agenda WHERE id_agenda = $idAgenda";

} elseif ($tipo === 'barbeiro') {

    if (!$idBarbSessao) {
        header("Location: barbeiros.php");
        exit;
    }

    $sql = "
        SELECT * FROM agenda
        WHERE id_agenda = $idAgenda
          AND id_barb = $idBarbSessao
    ";

} else {

    if (!$idClienteSessao) {
        header("Location: agendados.php");
        exit;
    }

    $sql = "
        SELECT * FROM agenda
        WHERE id_agenda = $idAgenda
          AND id_cliente = $idClienteSessao
    ";
}

$res = $conexao->query($sql);

if (!$res || $res->num_rows === 0) {
    header("Location: " . homePorTipo($tipo));
    exit;
}

$agenda = $res->fetch_assoc();

/* ===== DADOS ===== */
$dataAtual        = $agenda['data_agenda'];
$horaAtual        = $agenda['hora_inicio'];
$idBarbAtual      = $agenda['id_barb'];
$idServicoAtual   = $agenda['id_servico'];

/* ===== BARBEIROS ===== */
$sqlBarb = "
    SELECT id_barb, nome_barb
    FROM barbeiro
    WHERE ativo = 1 OR id_barb = $idBarbAtual
";
$resBarb = $conexao->query($sqlBarb);

/* ===== SERVIÇOS ===== */
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
</style>
</head>

<body class="list-bg">

<nav class="navbar navbar-dark bg-secondary">
    <div class="container-fluid">
        <a href="<?= homePorTipo($tipo) ?>" class="navbar-brand">Voltar</a>
        <a href="sair.php" class="btn btn-danger">Sair</a>
    </div>
</nav>

<div class="container table-container table-bg mt-5" style="max-width:600px;">
<h1 class="mb-4">Editar Agendamento</h1>

<form action="salvarEdicao.php" method="POST">

<!-- BARBEIRO -->
<div class="mb-3">
<label class="form-label">Barbeiro</label>
<select name="id_barb" id="id_barb" class="form-control" required>
<?php while ($b = $resBarb->fetch_assoc()) { ?>
<option value="<?= $b['id_barb'] ?>" <?= $b['id_barb']==$idBarbAtual?'selected':'' ?>>
<?= htmlspecialchars($b['nome_barb']) ?>
</option>
<?php } ?>
</select>
</div>

<!-- SERVIÇO -->
<div class="mb-3">
<label class="form-label">Serviço</label>
<select name="id_servico" id="id_servico" class="form-control" required>
<?php while ($s = $resServ->fetch_assoc()) { ?>
<option value="<?= $s['id_servico'] ?>" <?= $s['id_servico']==$idServicoAtual?'selected':'' ?>>
<?= htmlspecialchars($s['nome']) ?>
</option>
<?php } ?>
</select>
</div>

<!-- DIAS -->
<div class="mb-3">
<label class="form-label">Dia</label>
<div id="diasSemana" class="dias-semana"></div>
<input type="date" name="data_agenda" id="data_agenda"
       class="form-control mt-2"
       value="<?= $dataAtual ?>" required>
</div>

<!-- HORÁRIOS -->
<div class="mb-4">
<label class="form-label">Horário</label>
<div id="horarios" class="horarios"></div>
<input type="hidden" name="hora_inicio" id="hora_inicio"
       value="<?= $horaAtual ?>" required>
</div>

<input type="hidden" name="id_agenda" value="<?= $idAgenda ?>">

<button type="submit" class="btn btn-warning w-100">
Salvar Alterações
</button>

</form>
</div>

<script>
const diasContainer = document.getElementById('diasSemana');
const horariosDiv  = document.getElementById('horarios');
const dataInput    = document.getElementById('data_agenda');
const horaInput    = document.getElementById('hora_inicio');
const barbeiroSel  = document.getElementById('id_barb');
const servicoSel   = document.getElementById('id_servico');

/* ===== DATA LOCAL (SEM UTC) ===== */
function formatarDataLocal(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth()+1).padStart(2,'0');
    const d = String(date.getDate()).padStart(2,'0');
    return `${y}-${m}-${d}`;
}

/* ===== BASE = DATA ATUAL DO AGENDAMENTO ===== */
const base = new Date(dataInput.value);

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

        if (btn.dataset.date === dataInput.value) {
            btn.classList.add('active');
        }

        btn.onclick = () => {
            document.querySelectorAll('.dia-btn')
                .forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            dataInput.value = btn.dataset.date;
            carregarHorarios();
        };

        diasContainer.appendChild(btn);
    }
}

/* ===== CARREGAR HORÁRIOS ===== */
async function carregarHorarios() {
    horariosDiv.innerHTML = '';
    horaInput.value = '';

    const res = await fetch(
        `horariosDisponiveis.php?data=${dataInput.value}&id_barb=${barbeiroSel.value}&id_servico=${servicoSel.value}`
    );

    const horarios = await res.json();

    if (!horarios.length) {
        horariosDiv.innerHTML = '<span class="text-muted">Nenhum horário disponível</span>';
        return;
    }

    horarios.forEach(h => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'horario-btn';
        btn.textContent = h;

        if (h === "<?= $horaAtual ?>") {
            btn.classList.add('active');
            horaInput.value = h;
        }

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

/* ===== INIT ===== */
gerarDias();
carregarHorarios();
</script>

</body>
</html>
