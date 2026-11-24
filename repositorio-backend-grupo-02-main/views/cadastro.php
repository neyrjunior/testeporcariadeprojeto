<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta - UmHelp</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="login-wrapper">
        <div class="login-box signup-box">
            <div class="auth-symbol">♟</div>
            <h1>Criar conta</h1>
            <p class="subtitle">Faça parte da nossa comunidade!</p>
            <?php if ($flash !== ''): ?>
                <p class="notice" role="status"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="index.php" method="post">
                <input type="hidden" name="action" value="registrar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <label for="reg-name">Nome</label>
                <input id="reg-name" type="text" name="nome" maxlength="60" autocomplete="name" required>

                <label for="reg-email">E-mail</label>
                <input id="reg-email" type="email" name="email" autocomplete="email" required>

                <label for="reg-password">Senha (mínimo 8 caracteres)</label>
                <input id="reg-password" type="password" name="senha" minlength="8" maxlength="72" autocomplete="new-password" required>

                <label for="reg-confirm-password">Confirme a senha</label>
                <input id="reg-confirm-password" type="password" name="confirmar_senha" minlength="8" maxlength="72" autocomplete="new-password" required>

                <label for="reg-telefone">Telefone</label>
                <input id="reg-telefone" type="tel" name="telefone" maxlength="15" autocomplete="tel" required>

                <label for="reg-cpf">CPF</label>
                <input id="reg-cpf" type="text" name="cpf" maxlength="14" placeholder="000.000.000-00" required>

                <label for="reg-nascimento">Data de nascimento</label>
                <input id="reg-nascimento" type="date" name="nascimento" required>

                <label for="reg-cep">CEP</label>
                <input id="reg-cep" type="text" name="cep" maxlength="9" placeholder="00000-000" autocomplete="postal-code" required>
        
                <label for="reg-endereco">Endereço</label>
                <input id="reg-endereco" type="text" name="endereco" autocomplete="street-address" required>

                <label for="reg-bairro">Bairro</label>
                <input id="reg-bairro" type="text" name="bairro" required>

                <label for="reg-cidade">Cidade</label>
                <input id="reg-cidade" type="text" name="cidade" autocomplete="address-level2" required>

                <label for="reg-estado">Estado</label>
                <input id="reg-estado" type="text" name="estado" maxlength="2" placeholder="Ex: RJ" autocomplete="address-level1" required>

                <button type="submit">Criar conta</button>
            </form>

            <p class="login-redirect">
                Já tem uma conta? <a href="index.php?action=login">Entrar</a>
            </p>
        </div>
    </main>
== Rodapé Cadastro ==
<?php require __DIR__ . '/footer.php'; ?>

</body>
</html>
