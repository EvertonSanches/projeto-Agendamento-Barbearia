<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/agendamento-barbearia/css/estiloBasico.css">
</head>

<body class="page-bg">

    <nav class="navbar navbar-expand-lg navbar-dark bg-black">
        <div class="container">
            <a href="/agendamento-barbearia/index.php" class="navbar-brand">
                Barbearia GC
            </a>

            <div class="d-flex">
                <a href="cadastro.php" class="btn btn-outline-light me-2">
                    Cadastre-se
                </a>
                <a href="/agendamento-barbearia/index.php" class="btn btn-outline-light">
                    Voltar
                </a>
            </div>
        </div>
    </nav>

    <div class="container d-flex justify-content-center align-items-center min-vh-100">

        <div
            class="card cadastro-card login-card"
            data-state="hidden"
            style="opacity:0; transform: translateY(80px);">


            <div class="card-body">

                <h2 class="text-center mb-4">Login</h2>

                <form action="testeLogin.php" method="POST">

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>

                    <button type="submit" name="submit" class="btn btn-warning w-100">
                        Entrar
                    </button>

                </form>

            </div>
        </div>
    </div>

    <script src="/agendamento-barbearia/js/barbearia.js"></script>
</body>

</html>