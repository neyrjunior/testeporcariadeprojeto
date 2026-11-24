<?php

class RelatoController
{
    private $relatoDAO;

    public function __construct($pdo)
    {
        $this->relatoDAO = new RelatoDAO($pdo);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    // ==============================
    // Página inicial
    // ==============================

    public function index()
    {
        $usuario = [
            'nome' => $_SESSION['user_name'] ?? 'Usuário Visitante',
            'handle' => $_SESSION['user_handle'] ?? '@usuario',
            'avatar' => $_SESSION['user_avatar'] ?? 'https://via.placeholder.com/150'
        ];

        $estatisticas = $this->relatoDAO->obterEstatisticas();

        $posts = $this->relatoDAO->listarTodos();

        require_once __DIR__ . '/../views/home.php';
    }


    // ==============================
    // Criar relato
    // ==============================

    public function criar()
    {
        header('Content-Type: application/json; charset=UTF-8');

        // Verifica se é POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            $this->responder(false, 'Método inválido.');
        }


        // Verifica se o usuário está logado
        if (!isset($_SESSION['user_id'])) {

            $this->responder(false, 'Você precisa estar logado para publicar.');
        }


        // Recebe os dados
        $status = trim($_POST['status'] ?? '');
        $local = trim($_POST['local'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        $imagemNome = null;


        // ==============================
        // Validação
        // ==============================

        $statusPermitidos = [
            'perdido',
            'achado'
        ];

        if (!in_array($status, $statusPermitidos)) {

            $this->responder(false, 'Tipo de relato inválido.');
        }


        if ($local === '') {

            $this->responder(false, 'Informe o local.');
        }


        if ($descricao === '') {

            $this->responder(false, 'Informe a descrição do item.');
        }


        // ==============================
        // Upload da imagem
        // ==============================

        if (
            isset($_FILES['imagem']) &&
            $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {

                $this->responder(false, 'Erro ao enviar a imagem.');
            }


            // Limite de 5 MB
            if ($_FILES['imagem']['size'] > 5 * 1024 * 1024) {

                $this->responder(
                    false,
                    'A imagem deve ter no máximo 5 MB.'
                );
            }


            // Verifica o tipo real do arquivo
            $tiposPermitidos = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            $tipo = mime_content_type(
                $_FILES['imagem']['tmp_name']
            );


            if (!isset($tiposPermitidos[$tipo])) {

                $this->responder(
                    false,
                    'Formato de imagem não permitido.'
                );
            }


            $extensao = $tiposPermitidos[$tipo];

            $imagemNome = uniqid('item_', true) . '.' . $extensao;


            // Caminho da pasta
            $pasta = __DIR__ . '/../../public/assets/img/';


            // Cria a pasta se não existir
            if (!is_dir($pasta)) {

                mkdir($pasta, 0777, true);
            }


            $destino = $pasta . $imagemNome;


            if (!move_uploaded_file(
                $_FILES['imagem']['tmp_name'],
                $destino
            )) {

                $this->responder(
                    false,
                    'Não foi possível salvar a imagem.'
                );
            }
        }


        // ==============================
        // Cria o relato
        // ==============================

        $relato = new Relato();

        $relato->setUsuarioId($_SESSION['user_id']);
        $relato->setStatus($status);
        $relato->setLocal($local);
        $relato->setDescricao($descricao);
        $relato->setImagem($imagemNome);


        // Salva no banco
        $salvo = $this->relatoDAO->inserir($relato);


        if (!$salvo) {

            // Remove a imagem caso o banco falhe
            if ($imagemNome !== null) {

                $arquivo = $pasta . $imagemNome;

                if (file_exists($arquivo)) {
                    unlink($arquivo);
                }
            }

            $this->responder(
                false,
                'Não foi possível publicar o relato.'
            );
        }


        // Sucesso
        $this->responder(
            true,
            'Relato publicado com sucesso!'
        );
    }


    // ==============================
    // Resposta JSON
    // ==============================

    private function responder(
        bool $sucesso,
        string $mensagem
    ): void {

        echo json_encode([
            'success' => $sucesso,
            'message' => $mensagem
        ]);

        exit;
    }
}   