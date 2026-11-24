<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>UmHelp - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="login-wrapper">
        <div class="login-box">
            <div class="auth-symbol">●</div>
            <h1>UmHelp</h1>
            <p class="subtitle">Bem-vindo!</p>
            <?php if (!empty($flash)): ?>
                <p class="notice" role="status"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="index.php" method="POST">
                <input type="hidden" name="action" value="autenticar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <label for="email">E-mail:</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Digite seu e-mail"
                    required
                >


                <label for="senha">Senha:</label>

                <input
                    type="password"
                    id="senha"
                    name="senha"
                    placeholder="Digite sua senha"
                    required
                >


                <button type="submit">Entrar</button>

            </form>

            <p class="register-text">
                Ainda não possui uma conta?
                <a href="index.php?action=cadastro">Criar conta</a>
            </p>

        </div>
    </main>
    <!-- Conteúdo da página inicial -->

    <?php require __DIR__ . '/footer.php'; ?>

</body>

</html>