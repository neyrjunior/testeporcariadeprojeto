<?php

session_start();

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function userHandle(string $email): string
{
    $localPart = (string) strstr($email, '@', true);
    return '@' . substr($localPart, 0, 62) . '-' . substr(hash('sha256', $email), 0, 16);
}

function redirectTo(string $action, array $parameters = []): void
{
    $query = http_build_query(array_merge(['action' => $action], $parameters));
    header('Location: index.php?' . $query, true, 303);
    exit;
}

function requireLogin(): int
{
    if (empty($_SESSION['user_id'])) {
        $_SESSION['flash'] = 'Entre na sua conta para continuar.';
        redirectTo('login');
    }

    return (int) $_SESSION['user_id'];
}

function requireRoot(): int
{
    $userId = requireLogin();
    if (($_SESSION['user_profile'] ?? '') !== 'root') {
        $_SESSION['flash'] = 'Esta área é exclusiva para a conta root.';
        redirectTo('home');
    }

    return $userId;
}

function setFlash(string $message): void
{
    $_SESSION['flash'] = $message;
}

try {
    $pdo = require __DIR__ . '/config/database.php';
} catch (Throwable $error) {
    error_log($error->getMessage());
    http_response_code(500);
    echo '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><title>Banco indisponível</title>';
    echo '<h1>Não foi possível conectar ao MySQL</h1>';
    echo '<p>Confira DB_HOST, DB_PORT, DB_NAME, DB_USER e DB_PASSWORD, execute database/schema.sql e tente novamente.</p>';
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'home';
$nome = $_SESSION['user_name'] ?? '';
$handle = $_SESSION['user_handle'] ?? '';
$autenticado = !empty($_SESSION['user_id']);
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $submittedToken = $_POST['csrf_token'] ?? '';
        if (!is_string($submittedToken) || !hash_equals($csrfToken, $submittedToken)) {
            throw new RuntimeException('Sua sessão expirou. Atualize a página e tente novamente.');
        }

        if ($action === 'registrar') {
            $nome = trim($_POST['nome'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $telefone = preg_replace('/\D+/', '', $_POST['telefone'] ?? '');
            $cpf = preg_replace('/\D+/', '', $_POST['cpf'] ?? '');
            $nascimento = trim($_POST['nascimento'] ?? '');
            $cep = preg_replace('/\D+/', '', $_POST['cep'] ?? '');
            $endereco = trim($_POST['endereco'] ?? '');
            $bairro = trim($_POST['bairro'] ?? '');
            $cidade = trim($_POST['cidade'] ?? '');
            $estado = strtoupper(trim($_POST['estado'] ?? ''));
            $senha = $_POST['senha'] ?? '';
            $confirmarSenha = $_POST['confirmar_senha'] ?? '';

            if (
                $nome === '' || strlen($nome) > 60 || strlen($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL)
                || strlen($telefone) < 10 || strlen($telefone) > 15 || strlen($cpf) !== 11 || strlen($cep) !== 8
                || $endereco === '' || $bairro === '' || $cidade === '' || !preg_match('/^[A-Z]{2}$/', $estado)
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $nascimento)
            ) {
                throw new RuntimeException('Confira os dados obrigatórios do cadastro.');
            }

            if (strlen($senha) < 8 || strlen($senha) > 72 || $senha !== $confirmarSenha) {
                throw new RuntimeException('A senha deve ter de 8 a 72 caracteres e ser confirmada corretamente.');
            }

            $statement = $pdo->prepare(
                'INSERT INTO usuarios
                    (nome, email, telefone, cpf, data_nascimento, cep, endereco, bairro, cidade, estado, senha, perfil, handle)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $statement->execute([
                $nome,
                $email,
                $telefone,
                $cpf,
                $nascimento,
                $cep,
                $endereco,
                $bairro,
                $cidade,
                $estado,
                password_hash($senha, PASSWORD_DEFAULT),
                'comum',
                userHandle($email),
            ]);

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            $_SESSION['user_name'] = $nome;
            $_SESSION['user_handle'] = userHandle($email);
            $_SESSION['user_profile'] = 'comum';
            setFlash('Conta criada. Você já pode publicar relatos.');
            redirectTo('home');
        }

        if ($action === 'autenticar') {
            $email = strtolower(trim($_POST['email'] ?? ''));
            $senha = $_POST['senha'] ?? '';
            $statement = $pdo->prepare('SELECT id, nome, email, senha, perfil, handle FROM usuarios WHERE email = ? LIMIT 1');
            $statement->execute([$email]);
            $usuario = $statement->fetch();

            if (!$usuario || !password_verify($senha, $usuario['senha'])) {
                throw new RuntimeException('E-mail ou senha incorretos.');
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $usuario['id'];
            $_SESSION['user_name'] = $usuario['nome'];
            $_SESSION['user_handle'] = $usuario['handle'];
            $_SESSION['user_profile'] = $usuario['perfil'];
            setFlash('Bem-vindo de volta!');
            redirectTo('home');
        }

        if ($action === 'publicar') {
            $userId = requireLogin();
            $status = trim($_POST['status'] ?? '');
            $local = trim($_POST['local'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            if (!in_array($status, ['perdido', 'achado'], true) || $local === '' || strlen($local) > 100) {
                throw new RuntimeException('Selecione se perdeu ou encontrou o item e informe o local.');
            }
            if ($descricao === '' || strlen($descricao) > 2000) {
                throw new RuntimeException('A descrição é obrigatória e deve ter até 2000 caracteres.');
            }

            $statement = $pdo->prepare(
                'INSERT INTO relatos (usuario_id, status, local, descricao) VALUES (?, ?, ?, ?)'
            );
            $statement->execute([$userId, $status, $local, $descricao]);
            setFlash('Relato publicado com sucesso.');
            redirectTo('home');
        }

        if ($action === 'atualizar_relato') {
            requireRoot();
            $reportId = filter_var($_POST['relato_id'] ?? null, FILTER_VALIDATE_INT);
            $status = trim($_POST['status'] ?? '');
            $local = trim($_POST['local'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            if (!$reportId || !in_array($status, ['perdido', 'achado', 'recuperado'], true)) {
                throw new RuntimeException('Publicação ou status inválido.');
            }
            if ($local === '' || strlen($local) > 100 || $descricao === '' || strlen($descricao) > 2000) {
                throw new RuntimeException('Informe um local e uma descrição com até 2000 caracteres.');
            }

            $statement = $pdo->prepare('UPDATE relatos SET status = ?, local = ?, descricao = ? WHERE id = ?');
            $statement->execute([$status, $local, $descricao, $reportId]);
            if ($statement->rowCount() === 0) {
                $check = $pdo->prepare('SELECT id FROM relatos WHERE id = ?');
                $check->execute([$reportId]);
                if (!$check->fetchColumn()) {
                    throw new RuntimeException('A publicação não foi encontrada.');
                }
            }
            setFlash('Publicação atualizada.');
            redirectTo('admin');
        }

        if ($action === 'excluir_relato') {
            requireRoot();
            $reportId = filter_var($_POST['relato_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$reportId) {
                throw new RuntimeException('Publicação inválida.');
            }

            $statement = $pdo->prepare('DELETE FROM relatos WHERE id = ?');
            $statement->execute([$reportId]);
            if ($statement->rowCount() !== 1) {
                throw new RuntimeException('A publicação não foi encontrada.');
            }
            setFlash('Publicação excluída.');
            redirectTo('admin');
        }

        if ($action === 'perfil_salvar') {
            $userId = requireLogin();
            $nome = trim($_POST['nome'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $telefone = preg_replace('/\D+/', '', $_POST['telefone'] ?? '');

            if (
                $nome === '' || strlen($nome) > 60 || strlen($email) > 190
                || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($telefone) < 10 || strlen($telefone) > 15
            ) {
                throw new RuntimeException('Informe um nome, e-mail e telefone válidos.');
            }

            $handle = userHandle($email);
            $statement = $pdo->prepare(
                'UPDATE usuarios SET nome = ?, email = ?, telefone = ?, handle = ? WHERE id = ?'
            );
            $statement->execute([$nome, $email, $telefone, $handle, $userId]);
            $_SESSION['user_name'] = $nome;
            $_SESSION['user_handle'] = $handle;
            setFlash('Perfil atualizado.');
            redirectTo('perfil');
        }

        if ($action === 'enviar_mensagem') {
            $senderId = requireLogin();
            $recipientId = filter_var($_POST['destinatario_id'] ?? null, FILTER_VALIDATE_INT);
            $reportId = filter_var($_POST['relato_id'] ?? null, FILTER_VALIDATE_INT);
            $message = trim($_POST['mensagem'] ?? '');

            if (!$recipientId || $recipientId === $senderId || $message === '' || strlen($message) > 2000) {
                throw new RuntimeException('A mensagem deve ter até 2000 caracteres e um destinatário válido.');
            }

            $recipientCheck = $pdo->prepare('SELECT id FROM usuarios WHERE id = ?');
            $recipientCheck->execute([$recipientId]);
            if (!$recipientCheck->fetchColumn()) {
                throw new RuntimeException('O destinatário não foi encontrado.');
            }

            if ($reportId) {
                $reportCheck = $pdo->prepare('SELECT usuario_id FROM relatos WHERE id = ?');
                $reportCheck->execute([$reportId]);
                if ((int) $reportCheck->fetchColumn() !== $recipientId) {
                    throw new RuntimeException('A publicação não pertence ao destinatário selecionado.');
                }
            } else {
                $reportId = null;
            }

            $statement = $pdo->prepare(
                'INSERT INTO mensagens (remetente_id, destinatario_id, relato_id, mensagem) VALUES (?, ?, ?, ?)'
            );
            $statement->execute([$senderId, $recipientId, $reportId, $message]);
            setFlash('Mensagem enviada em privado.');
            redirectTo('mensagens', ['usuario' => $recipientId]);
        }

        if ($action === 'sair') {
            $_SESSION = [];
            session_destroy();
            redirectTo('home');
        }

        throw new RuntimeException('Ação inválida.');
    } catch (PDOException $error) {
        error_log($error->getMessage());
        setFlash('Não foi possível concluir a operação. Verifique os dados informados e tente novamente.');
        if ($action === 'registrar') {
            redirectTo('cadastro');
        }
        if ($action === 'perfil_salvar') {
            redirectTo('perfil');
        }
        if ($action === 'enviar_mensagem') {
            redirectTo('mensagens');
        }
        redirectTo(in_array($action, ['atualizar_relato', 'excluir_relato'], true) ? 'admin' : 'home');
    } catch (RuntimeException $error) {
        setFlash($error->getMessage());
        if ($action === 'registrar') {
            redirectTo('cadastro');
        }
        if ($action === 'autenticar') {
            redirectTo('login');
        }
        if ($action === 'perfil_salvar') {
            redirectTo('perfil');
        }
        if ($action === 'enviar_mensagem') {
            $recipientId = filter_var($_POST['destinatario_id'] ?? null, FILTER_VALIDATE_INT);
            redirectTo('mensagens', $recipientId ? ['usuario' => $recipientId] : []);
        }
        redirectTo(in_array($action, ['atualizar_relato', 'excluir_relato'], true) ? 'admin' : 'home');
    }
}

if ($action === 'sair') {
    redirectTo('home');
}

$flash = $_SESSION['flash'] ?? $flash;
unset($_SESSION['flash']);

if ($action === 'login' || $action === 'cadastro') {
    require __DIR__ . '/views/' . ($action === 'login' ? 'login.php' : 'cadastro.php');
    exit;
}

if ($action === 'perfil') {
    $requestedId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$requestedId && !$autenticado) {
        setFlash('Entre para acessar seu perfil.');
        redirectTo('login');
    }

    $profileId = $requestedId ?: (int) $_SESSION['user_id'];
    $statement = $pdo->prepare(
        'SELECT id, nome, email, telefone, cpf, data_nascimento, cep, endereco, bairro, cidade, estado, perfil, handle, criado_em
         FROM usuarios WHERE id = ?'
    );
    $statement->execute([$profileId]);
    $perfil = $statement->fetch();

    if (!$perfil) {
        http_response_code(404);
        $flash = 'Perfil não encontrado.';
        redirectTo('home');
    }

    $perfilProprio = $autenticado && (int) $_SESSION['user_id'] === (int) $perfil['id'];
    if (!$perfilProprio) {
        unset($perfil['email'], $perfil['telefone'], $perfil['cpf'], $perfil['data_nascimento'], $perfil['cep'], $perfil['endereco'], $perfil['bairro'], $perfil['cidade'], $perfil['estado']);
    }
    $statement = $pdo->prepare(
        'SELECT r.*, u.nome, u.handle FROM relatos r JOIN usuarios u ON u.id = r.usuario_id
         WHERE r.usuario_id = ? ORDER BY r.criado_em DESC'
    );
    $statement->execute([$profileId]);
    $relatos = $statement->fetchAll();
    require __DIR__ . '/views/perfil.php';
    exit;
}

if ($action === 'mensagens') {
    $currentUserId = requireLogin();
    $conversationId = filter_var($_GET['usuario'] ?? null, FILTER_VALIDATE_INT);
    $conversationUser = null;
    $messages = [];
    $conversas = [];

    if ($conversationId && $conversationId !== $currentUserId) {
        $statement = $pdo->prepare('SELECT id, nome, handle FROM usuarios WHERE id = ?');
        $statement->execute([$conversationId]);
        $conversationUser = $statement->fetch();
        if (!$conversationUser) {
            http_response_code(404);
            $flash = 'Usuário não encontrado.';
            $conversationId = null;
        } else {
            $statement = $pdo->prepare(
                'UPDATE mensagens SET lida_em = NOW()
                 WHERE remetente_id = ? AND destinatario_id = ? AND lida_em IS NULL'
            );
            $statement->execute([$conversationId, $currentUserId]);
            $statement = $pdo->prepare(
                'SELECT m.*, remetente.nome AS remetente_nome
                 FROM mensagens m JOIN usuarios remetente ON remetente.id = m.remetente_id
                 WHERE (m.remetente_id = ? AND m.destinatario_id = ?)
                    OR (m.remetente_id = ? AND m.destinatario_id = ?)
                 ORDER BY m.criado_em ASC'
            );
            $statement->execute([$currentUserId, $conversationId, $conversationId, $currentUserId]);
            $messages = $statement->fetchAll();
        }
    }

    if (!$conversationId || !$conversationUser) {
        $statement = $pdo->prepare(
            'SELECT m.*, remetente.nome AS remetente_nome, destinatario.nome AS destinatario_nome
             FROM mensagens m
             JOIN usuarios remetente ON remetente.id = m.remetente_id
             JOIN usuarios destinatario ON destinatario.id = m.destinatario_id
             WHERE m.remetente_id = ? OR m.destinatario_id = ?
             ORDER BY m.criado_em DESC'
        );
        $statement->execute([$currentUserId, $currentUserId]);
        $rows = $statement->fetchAll();
        foreach ($rows as $row) {
            $otherId = (int) $row['remetente_id'] === $currentUserId
                ? (int) $row['destinatario_id']
                : (int) $row['remetente_id'];
            if (!isset($conversas[$otherId])) {
                $row['outro_id'] = $otherId;
                $row['outro_nome'] = (int) $row['remetente_id'] === $currentUserId
                    ? $row['destinatario_nome']
                    : $row['remetente_nome'];
                $conversas[$otherId] = $row;
            }
        }
    }

    require __DIR__ . '/views/mensagens.php';
    exit;
}

if ($action === 'admin') {
    requireRoot();
    $statement = $pdo->query(
        'SELECT r.*, u.nome, u.handle FROM relatos r JOIN usuarios u ON u.id = r.usuario_id
         ORDER BY r.criado_em DESC'
    );
    $relatos = $statement->fetchAll();
    require __DIR__ . '/views/admin.php';
    exit;
}

if ($action !== 'home' && $action !== 'pesquisar') {
    http_response_code(404);
    $flash = 'Página não encontrada.';
    $action = 'home';
}

$termo = trim($_GET['q'] ?? '');
$tipoFiltro = $_GET['tipo'] ?? '';
$paginaPesquisa = $action === 'pesquisar';
$sql = 'SELECT r.*, u.nome, u.handle FROM relatos r JOIN usuarios u ON u.id = r.usuario_id';
$conditions = [];
$parameters = [];

if ($paginaPesquisa && $termo !== '') {
    $conditions[] = '(r.descricao LIKE ? OR r.local LIKE ? OR u.nome LIKE ?)';
    $searchTerm = '%' . $termo . '%';
    array_push($parameters, $searchTerm, $searchTerm, $searchTerm);
}
if ($paginaPesquisa && in_array($tipoFiltro, ['perdido', 'achado'], true)) {
    $conditions[] = 'r.status = ?';
    $parameters[] = $tipoFiltro;
}
if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY r.criado_em DESC';
$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$relatos = $statement->fetchAll();

$summary = $pdo->query(
    "SELECT
        SUM(status = 'perdido') AS perdidos,
        SUM(status = 'achado') AS achados
     FROM relatos"
)->fetch();
$totalPerdidos = (int) ($summary['perdidos'] ?? 0);
$totalAchados = (int) ($summary['achados'] ?? 0);
require __DIR__ . '/views/feed.php';
