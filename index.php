<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Barbearia GC | Estilo, atitude e precisão</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">


    <link rel="stylesheet" href="/agendamento-barbearia/css/site.css">
</head>

<body class="bg-dark text-white">

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-glass">
        <div class="container">
            <a class="navbar-brand fw-bold text-warning" href="index.php">
                Barbearia GC
            </a>

            <div class="d-flex">
                <a href="/agendamento-barbearia/back-end/login.php" class="btn btn-outline-light me-2">
                    Login
                </a>
                <a href="/agendamento-barbearia/back-end/cadastro.php" class="btn btn-warning">
                    Cadastrar
                </a>
            </div>
        </div>
    </nav>


    <div style="height: 90px;"></div>


    <section class="container section text-center">
        <h1 class="text-warning mb-3">Barbearia Gabriel Cruz</h1>
        <h2 class="text-warning mb-3">Beleza, cosméticos e cuidados pessoais</h2>
        <p class="text-muted">
            Atendimento com hora marcada, ambiente climatizado.
        </p>

        <a href="/agendamento-barbearia/back-end/login.php" class="btn btn-warning mt-3">
            Agendar Horário
        </a>
    </section>


    <section class="services section bg-black text-center">
        <div class="container">
            <h2 class="text-warning mb-4">Serviços</h2>

            <div class="row text-center">
                <div class="col-md-4 mb-3">✂ Corte Masculino</div>
                <div class="col-md-4 mb-3">🧔 Barba</div>
                <div class="col-md-4 mb-3">💈 Corte + Barba</div>
            </div>
        </div>
    </section>

    <section class="container section text-center">
        <h2 class="text-warning mb-4">Instagram</h2>

        <div style="
        max-width: 1200px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,.6);
    ">
            <iframe
                src="https://www.instagram.com/barbearia.gc/embed"
                width="100%"
                height="820"
                frameborder="0"
                scrolling="no"
                allowtransparency="true">
            </iframe>
        </div>

        <a
            href="https://www.instagram.com/barbearia.gc/"
            target="_blank"
            class="btn btn-outline-warning mt-4">
            <i class="bi bi-instagram"></i> Ver mais no Instagram
        </a>
    </section>

    <section class="container section text-center">
        <h2 class="text-warning mb-4">Onde estamos</h2>

        <div style="
        max-width: 1200px;
        margin: 0 auto;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,.6);
    ">
            <iframe
                src="https://www.google.com/maps?q=Rua%20do%20Carmo%2C%2040%2C%20Santo%20Antonio%2C%20Aracaju%20SE%2C%2049060-080%2C%20Brasil&output=embed"
                width="100%"
                height="420"
                style="border:0"
                allowfullscreen
                loading="lazy">
            </iframe>
        </div>

        <p class="mt-3 text-muted">
            Rua do Carmo, 40 – Santo Antônio, Aracaju/SE
        </p>
    </section>

    <a
        href="https://wa.me/557988685210"
        target="_blank"
        style="
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #25d366;
        color: #fff;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        box-shadow: 0 5px 15px rgba(0,0,0,.5);
        z-index: 2000;
        text-decoration: none;
    "
        aria-label="WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>

    <footer class="footer">
        <p class="mb-1">© <?= date('Y') ?> Barbearia Gabriel Cruz</p>
        <small class="text-muted">Sistema desenvolvido sob medida Por: Everton Sanches</small>
    </footer>

</body>

</html>