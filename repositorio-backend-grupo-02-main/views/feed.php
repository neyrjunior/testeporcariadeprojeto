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
    <title><?= $paginaPesquisa ? 'Pesquisar' : 'Início' ?> - UmHelp</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require __DIR__ . '/navbar.php'; ?>

    <a class="top-login" href="index.php?action=<?= $autenticado ? 'perfil' : 'login' ?>"><span>●</span> <?= $autenticado ? 'Meu perfil' : 'Login' ?></a>
    <div class="welcome-line"><span></span><h2>Bem-Vindo a <strong>UmHelp</strong></h2><span></span></div>
    <div class="layout-container">
        <main class="main-content">
            <header class="feed-header">
                <div>
                    <h1><?= $paginaPesquisa ? 'Pesquisar relatos' : 'Página inicial' ?></h1>
                    <p>Achados e perdidos da comunidade</p>
                </div>
            </header>

            <?php if ($flash !== ''): ?>
                <p class="notice" role="status"><?= $escape($flash) ?></p>
            <?php endif; ?>

            <form class="search-form-header" method="get" action="index.php">
                <input type="hidden" name="action" value="pesquisar">
                <label class="visually-hidden" for="search-query">Buscar relatos</label>
                <input id="search-query" type="search" name="q" value="<?= $escape($termo) ?>" placeholder="Buscar por item, local ou pessoa">
                <select name="tipo" aria-label="Filtrar por tipo">
                    <option value="">Todos os relatos</option>
                    <option value="perdido" <?= $tipoFiltro === 'perdido' ? 'selected' : '' ?>>Perdidos</option>
                    <option value="achado" <?= $tipoFiltro === 'achado' ? 'selected' : '' ?>>Achados</option>
                </select>
                <button type="submit">Buscar</button>
            </form>

            <?php if ($autenticado): ?>
                <section class="create-post" id="novo-relato">
                    <div class="post-input-container">
                        <h2>Compartilhe um achado ou perda</h2>
                        <form action="index.php" method="post" class="report-form">
                            <input type="hidden" name="action" value="publicar">
                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                            <label for="report-status">O que aconteceu?</label>
                            <select id="report-status" name="status" required>
                                <option value="perdido">Perdi um item</option>
                                <option value="achado">Encontrei um item</option>
                            </select>

                            <label for="report-location">Onde?</label>
                            <input id="report-location" type="text" name="local" maxlength="100" placeholder="Ex.: biblioteca, bloco A" required>

                            <label for="report-description">Descreva o item</label>
                            <textarea id="report-description" name="descricao" maxlength="2000" placeholder="Inclua detalhes que ajudem a identificar o item." required></textarea>

                            <button type="submit" class="submit-btn">Publicar relato</button>
                        </form>
                    </div>
                </section>
            <?php else: ?>
                <section class="create-post" id="novo-relato">
                    <p><a href="index.php?action=login">Entre</a> ou <a href="index.php?action=cadastro">crie uma conta</a> para publicar um relato.</p>
                </section>
            <?php endif; ?>

            <section class="timeline" aria-label="Relatos">
                <?php if ($relatos === []): ?>
                    <p class="empty-state"><?= $paginaPesquisa ? 'Nenhum relato corresponde à sua busca.' : 'Ainda não há relatos. Seja o primeiro a compartilhar um achado ou perda.' ?></p>
                <?php else: ?>
                    <?php foreach ($relatos as $relato): ?>
                        <article class="post-card">
                            <header class="post-meta">
                                <a class="post-author" href="index.php?action=perfil&id=<?= (int) ($relato['usuario_id'] ?? 0) ?>">
                                    <strong><?= $escape($relato['nome'] ?? 'Usuário') ?></strong>
                                </a>
                                <span><?= $escape($relato['handle'] ?? '') ?></span>
                                <span class="post-type <?= $escape($relato['status'] ?? '') ?>">
                                    <?= ($relato['status'] ?? '') === 'achado' ? 'Encontrado' : (($relato['status'] ?? '') === 'recuperado' ? 'Recuperado' : 'Perdido') ?>
                                </span>
                            </header>
                            <p><?= nl2br($escape($relato['descricao'] ?? '')) ?></p>
                            <footer class="post-footer">
                                <span>Local: <?= $escape($relato['local'] ?? '') ?></span>
                                <time datetime="<?= $escape($relato['criado_em'] ?? '') ?>">
                                    <?= $escape(date('d/m/Y H:i', strtotime($relato['criado_em'] ?? 'now'))) ?>
                                </time>
                                <?php if (!$autenticado): ?>
                                    <a href="index.php?action=login">Entre para enviar mensagem privada</a>
                                <?php elseif ((int) $_SESSION['user_id'] !== (int) $relato['usuario_id']): ?>
                                    <a href="index.php?action=mensagens&amp;usuario=<?= (int) $relato['usuario_id'] ?>&amp;relato=<?= (int) $relato['id'] ?>">Enviar mensagem privada</a>
                                <?php endif; ?>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </main>

        <aside class="sidebar-right">
            <section class="widget-card">
                <h2>Resumo da comunidade</h2>
                <p><span class="summary-icon">▣</span> Itens perdidos <strong><?= $totalPerdidos ?></strong></p>
                <p><span class="summary-icon">✓</span> Itens encontrados <strong><?= $totalAchados ?></strong></p>
            </section>
        </aside>
    </div>
</body>
</html>
