<?php
$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= $escape($perfil['nome']) ?> - UmHelp</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require __DIR__ . '/navbar.php'; ?>
    <main class="page-content">
        <header class="feed-header">
            <div>
                <h1>Perfil de <?= $escape($perfil['nome']) ?></h1>
                <p><?= $escape($perfil['handle']) ?> · Participante desde <?= $escape(date('d/m/Y', strtotime($perfil['criado_em']))) ?></p>
            </div>
        </header>

        <?php if ($flash !== ''): ?>
            <p class="notice" role="status"><?= $escape($flash) ?></p>
        <?php endif; ?>

        <?php if ($perfilProprio): ?>
            <section class="post-input-container profile-card">
                <h2>Seus dados</h2>
                <form action="index.php" method="post" class="report-form profile-form">
                    <input type="hidden" name="action" value="perfil_salvar">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <label for="profile-name">Nome</label>
                    <input id="profile-name" type="text" name="nome" maxlength="60" value="<?= $escape($perfil['nome']) ?>" required>
                    <label for="profile-email">E-mail</label>
                    <input id="profile-email" type="email" name="email" value="<?= $escape($perfil['email']) ?>" required>
                    <label for="profile-phone">Telefone</label>
                    <input id="profile-phone" type="tel" name="telefone" value="<?= $escape($perfil['telefone']) ?>" required>
                    <button type="submit">Salvar alterações</button>
                </form>
                <p class="private-details">Endereço e documentos não são exibidos publicamente.</p>
            </section>
        <?php elseif ($autenticado): ?>
            <p class="profile-contact">
                <a class="action-link" href="index.php?action=mensagens&amp;usuario=<?= (int) $perfil['id'] ?>">Enviar mensagem privada</a>
            </p>
        <?php else: ?>
            <p class="profile-contact"><a href="index.php?action=login">Entre para enviar uma mensagem privada.</a></p>
        <?php endif; ?>

        <section class="timeline" aria-label="Publicações do usuário">
            <h2 class="section-title">Publicações</h2>
            <?php if ($relatos === []): ?>
                <p class="empty-state">Este perfil ainda não tem publicações.</p>
            <?php else: ?>
                <?php foreach ($relatos as $relato): ?>
                    <article class="post-card">
                        <header class="post-meta">
                            <strong><?= $escape($relato['nome']) ?></strong>
                            <span><?= $escape($relato['handle']) ?></span>
                            <span class="post-type <?= $escape($relato['status']) ?>">
                                <?= $relato['status'] === 'achado' ? 'Encontrado' : ($relato['status'] === 'recuperado' ? 'Recuperado' : 'Perdido') ?>
                            </span>
                        </header>
                        <p><?= nl2br($escape($relato['descricao'])) ?></p>
                        <footer class="post-footer">
                            <span>Local: <?= $escape($relato['local']) ?></span>
                            <time datetime="<?= $escape($relato['criado_em']) ?>"><?= $escape(date('d/m/Y H:i', strtotime($relato['criado_em']))) ?></time>
                            <?php if ($autenticado && (int) $_SESSION['user_id'] !== (int) $perfil['id']): ?>
                                <a href="index.php?action=mensagens&amp;usuario=<?= (int) $perfil['id'] ?>&amp;relato=<?= (int) $relato['id'] ?>">Enviar mensagem privada</a>
                            <?php endif; ?>
                        </footer>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
