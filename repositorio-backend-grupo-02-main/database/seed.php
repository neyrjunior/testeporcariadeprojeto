<?php

$pdo = require __DIR__ . '/../config/database.php';
$pdo->beginTransaction();

function demoHandle(string $email): string
{
    $localPart = (string) strstr($email, '@', true);
    return '@' . substr($localPart, 0, 62) . '-' . substr(hash('sha256', $email), 0, 16);
}

try {
    $demoPassword = getenv('UMHELP_DEMO_PASSWORD') ?: 'Demo@UmHelp2026';
    $passwordHash = password_hash($demoPassword, PASSWORD_DEFAULT);

    $users = [
        [
            'nome' => 'Administrador UmHelp',
            'email' => 'root@umhelp.local',
            'telefone' => '21990000001',
            'cpf' => '00000000001',
            'data_nascimento' => '1990-01-15',
            'cep' => '20040002',
            'endereco' => 'Rua da Assembleia, 10',
            'bairro' => 'Centro',
            'cidade' => 'Rio de Janeiro',
            'estado' => 'RJ',
            'perfil' => 'root',
        ],
        [
            'nome' => 'Marina Costa',
            'email' => 'marina.costa@example.test',
            'telefone' => '21987654321',
            'cpf' => '00000000002',
            'data_nascimento' => '2002-05-18',
            'cep' => '20271110',
            'endereco' => 'Rua Haddock Lobo, 210',
            'bairro' => 'Tijuca',
            'cidade' => 'Rio de Janeiro',
            'estado' => 'RJ',
            'perfil' => 'comum',
        ],
        [
            'nome' => 'Rafael Almeida',
            'email' => 'rafael.almeida@example.test',
            'telefone' => '21981234567',
            'cpf' => '00000000003',
            'data_nascimento' => '2001-11-03',
            'cep' => '20735001',
            'endereco' => 'Rua Dias da Cruz, 85',
            'bairro' => 'Méier',
            'cidade' => 'Rio de Janeiro',
            'estado' => 'RJ',
            'perfil' => 'comum',
        ],
        [
            'nome' => 'Camila Santos',
            'email' => 'camila.santos@example.test',
            'telefone' => '21989876543',
            'cpf' => '00000000004',
            'data_nascimento' => '2003-08-27',
            'cep' => '23575005',
            'endereco' => 'Estrada do Mendanha, 400',
            'bairro' => 'Campo Grande',
            'cidade' => 'Rio de Janeiro',
            'estado' => 'RJ',
            'perfil' => 'comum',
        ],
    ];

    $findUser = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
    $insertUser = $pdo->prepare(
        'INSERT INTO usuarios
            (nome, email, telefone, cpf, data_nascimento, cep, endereco, bairro, cidade, estado, senha, perfil, handle)
         VALUES
            (:nome, :email, :telefone, :cpf, :data_nascimento, :cep, :endereco, :bairro, :cidade, :estado, :senha, :perfil, :handle)'
    );
    $userIds = [];

    foreach ($users as $user) {
        $findUser->execute([$user['email']]);
        $existingId = $findUser->fetchColumn();

        if ($existingId !== false) {
            $userIds[$user['email']] = (int) $existingId;
            continue;
        }

        $insertUser->execute($user + [
            'senha' => $passwordHash,
            'handle' => demoHandle($user['email']),
        ]);
        $userIds[$user['email']] = (int) $pdo->lastInsertId();
    }

    $sampleReports = [
        [
            'email' => 'marina.costa@example.test',
            'status' => 'perdido',
            'local' => 'Biblioteca - bloco B',
            'descricao' => 'Perdi um estojo azul-marinho com calculadora científica e duas canetas. Acredito que tenha ficado nas mesas do segundo andar.',
        ],
        [
            'email' => 'rafael.almeida@example.test',
            'status' => 'achado',
            'local' => 'Cantina principal',
            'descricao' => 'Encontrei uma garrafa térmica verde sobre uma das mesas perto da janela. Entreguei na recepção do bloco A.',
        ],
        [
            'email' => 'camila.santos@example.test',
            'status' => 'perdido',
            'local' => 'Laboratório de informática 3',
            'descricao' => 'Estou procurando um carregador USB-C branco, com uma pequena etiqueta azul no cabo. Usei o laboratório na aula da tarde.',
        ],
        [
            'email' => 'marina.costa@example.test',
            'status' => 'recuperado',
            'local' => 'Recepção do bloco A',
            'descricao' => 'Chaves com chaveiro amarelo recuperadas. Obrigada a quem encontrou e entregou na recepção!',
        ],
    ];

    $findReport = $pdo->prepare(
        'SELECT id FROM relatos WHERE usuario_id = ? AND descricao = ?'
    );
    $insertReport = $pdo->prepare(
        'INSERT INTO relatos (usuario_id, status, local, descricao, criado_em)
         VALUES (?, ?, ?, ?, ?)'
    );
    $reportIds = [];

    foreach ($sampleReports as $index => $report) {
        $userId = $userIds[$report['email']];
        $findReport->execute([$userId, $report['descricao']]);
        $existingId = $findReport->fetchColumn();

        if ($existingId !== false) {
            $reportIds[$index] = (int) $existingId;
            continue;
        }

        $daysAgo = count($sampleReports) - $index;
        $createdAt = date('Y-m-d H:i:s', strtotime('-' . $daysAgo . ' days'));
        $insertReport->execute([
            $userId,
            $report['status'],
            $report['local'],
            $report['descricao'],
            $createdAt,
        ]);
        $reportIds[$index] = (int) $pdo->lastInsertId();
    }

    $messageCheck = $pdo->prepare(
        'SELECT id FROM mensagens WHERE remetente_id = ? AND destinatario_id = ? AND mensagem = ?'
    );
    $messageInsert = $pdo->prepare(
        'INSERT INTO mensagens (remetente_id, destinatario_id, relato_id, mensagem)
         VALUES (?, ?, ?, ?)'
    );
    $senderId = $userIds['rafael.almeida@example.test'];
    $recipientId = $userIds['marina.costa@example.test'];
    $sampleMessage = 'Olá, você deixou um estojo azul na biblioteca? Posso verificar com a recepção.';
    $messageCheck->execute([$senderId, $recipientId, $sampleMessage]);

    if ($messageCheck->fetchColumn() === false) {
        $messageInsert->execute([$senderId, $recipientId, $reportIds[0], $sampleMessage]);
    }

    $pdo->commit();
    echo "Dados de demonstração inseridos/verificados.\n";
    echo "Usuário root: root@umhelp.local\n";
    echo "Senha de demonstração: {$demoPassword}\n";
    echo "Todos os usuários de demonstração usam essa senha.\n";
} catch (Throwable $error) {
    $pdo->rollBack();
    throw $error;
}
