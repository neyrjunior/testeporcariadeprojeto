<?php
$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$relatedReportId = filter_var($_GET['relato'] ?? null, FILTER_VALIDATE_INT);
if ($conversationUser && $relatedReportId) {
    $reportCheck = $pdo->prepare('SELECT id FROM relatos WHERE id = ? AND usuario_id = ?');
    $reportCheck->execute([$relatedReportId, $conversationUser['id']]);
    if (!$reportCheck->fetchColumn()) {
        $relatedReportId = null;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens privadas - UmHelp</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require __DIR__ . '/navbar.php'; ?>
    <main class="page-content">
        <header class="feed-header">
            <div><h1>Mensagens privadas</h1><p>Converse diretamente com a comunidade.</p></div>
        </header>
        <?php if ($flash !== ''): ?>
            <p class="notice" role="status"><?= $escape($flash) ?></p>
        <?php endif; ?>

        <?php if ($conversationUser): ?>
            <section class="conversation">
                <h2>Conversa com <?= $escape($conversationUser['nome']) ?> <span><?= $escape($conversationUser['handle']) ?></span></h2>
                <?php if ($relatedReportId): ?>
                    <p class="conversation-context">Mensagem relacionada à publicação #<?= (int) $relatedReportId ?>.</p>
                <?php endif; ?>
                <div class="message-list" aria-live="polite">
                    <?php if ($messages === []): ?>
                        <p class="empty-state">Inicie a conversa com uma mensagem privada.</p>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <article class="message-bubble <?= (int) $message['remetente_id'] === $currentUserId ? 'message-own' : '' ?>">
                                <strong><?= $escape($message['remetente_nome']) ?></strong>
                                <p><?= nl2br($escape($message['mensagem'])) ?></p>
                                <time datetime="<?= $escape($message['criado_em']) ?>"><?= $escape(date('d/m/Y H:i', strtotime($message['criado_em']))) ?></time>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <form action="index.php" method="post" class="report-form message-form">
                    <input type="hidden" name="action" value="enviar_mensagem">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <input type="hidden" name="destinatario_id" value="<?= (int) $conversationUser['id'] ?>">
                    <input type="hidden" name="relato_id" value="<?= (int) ($relatedReportId ?: 0) ?>">
                    <label for="private-message">Sua mensagem</label>
                    <textarea id="private-message" name="mensagem" maxlength="2000" required placeholder="Escreva sua mensagem..."></textarea>
                    <button type="submit">Enviar mensagem</button>
                </form>
                <a class="back-link" href="index.php?action=mensagens">Voltar às conversas</a>
            </section>
        <?php else: ?>
            <section class="conversation-list">
                <h2>Suas conversas</h2>
                <?php if ($conversas === []): ?>
                    <p class="empty-state">Você ainda não tem mensagens. Use “Enviar mensagem privada” em uma publicação para iniciar uma conversa.</p>
                <?php else: ?>
                    <?php foreach ($conversas as $conversation): ?>
                        <a class="conversation-link" href="index.php?action=mensagens&amp;usuario=<?= (int) $conversation['outro_id'] ?>">
                            <strong><?= $escape($conversation['outro_nome']) ?></strong>
                            <span><?= $escape($conversation['mensagem']) ?></span>
                            <time><?= $escape(date('d/m/Y H:i', strtotime($conversation['criado_em']))) ?></time>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
