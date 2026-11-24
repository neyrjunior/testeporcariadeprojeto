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
    <title>Gerenciar publicações - UmHelp</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require __DIR__ . '/navbar.php'; ?>
    <main class="page-content">
        <header class="feed-header">
            <div><h1>Moderação root</h1><p>Gerencie qualquer publicação da plataforma.</p></div>
        </header>
        <?php if ($flash !== ''): ?>
            <p class="notice" role="status"><?= $escape($flash) ?></p>
        <?php endif; ?>

        <section class="admin-reports" aria-label="Todas as publicações">
            <?php if ($relatos === []): ?>
                <p class="empty-state">Nenhuma publicação para moderar.</p>
            <?php else: ?>
                <?php foreach ($relatos as $relato): ?>
                    <article class="admin-report">
                        <header class="post-meta">
                            <strong>#<?= (int) $relato['id'] ?> · <?= $escape($relato['nome']) ?></strong>
                            <span><?= $escape($relato['handle']) ?></span>
                            <time><?= $escape(date('d/m/Y H:i', strtotime($relato['criado_em']))) ?></time>
                        </header>
                        <form action="index.php" method="post" class="report-form admin-edit-form">
                            <input type="hidden" name="action" value="atualizar_relato">
                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                            <input type="hidden" name="relato_id" value="<?= (int) $relato['id'] ?>">
                            <label for="status-<?= (int) $relato['id'] ?>">Status</label>
                            <select id="status-<?= (int) $relato['id'] ?>" name="status">
                                <option value="perdido" <?= $relato['status'] === 'perdido' ? 'selected' : '' ?>>Perdido</option>
                                <option value="achado" <?= $relato['status'] === 'achado' ? 'selected' : '' ?>>Encontrado</option>
                                <option value="recuperado" <?= $relato['status'] === 'recuperado' ? 'selected' : '' ?>>Recuperado</option>
                            </select>
                            <label for="local-<?= (int) $relato['id'] ?>">Local</label>
                            <input id="local-<?= (int) $relato['id'] ?>" name="local" maxlength="100" value="<?= $escape($relato['local']) ?>" required>
                            <label for="descricao-<?= (int) $relato['id'] ?>">Descrição</label>
                            <textarea id="descricao-<?= (int) $relato['id'] ?>" name="descricao" maxlength="2000" required><?= $escape($relato['descricao']) ?></textarea>
                            <button type="submit">Salvar alterações</button>
                        </form>
                        <form action="index.php" method="post" class="admin-delete-form" onsubmit="return confirm('Excluir esta publicação permanentemente?')">
                            <input type="hidden" name="action" value="excluir_relato">
                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                            <input type="hidden" name="relato_id" value="<?= (int) $relato['id'] ?>">
                            <button type="submit" class="danger-button">Excluir publicação</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
