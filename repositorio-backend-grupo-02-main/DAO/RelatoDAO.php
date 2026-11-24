<?php

class RelatoDAO 
{
    private $pdo;

    public function __construct($pdo) 
    {
        $this->pdo = $pdo;
    }

    public function inserir(Relato $relato) 
    {
        $sql = "INSERT INTO relatos (usuario_id, status, local, descricao, imagem, criado_em) 
                VALUES (:usuario_id, :status, :local, :descricao, :imagem, NOW())";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':usuario_id' => $relato->getUsuarioId(),
            ':status'     => $relato->getStatus(),
            ':local'      => $relato->getLocal(),
            ':descricao'  => $relato->getDescricao(),
            ':imagem'     => $relato->getImagem()
        ]);
    }

    public function listarTodos() 
    {
        $sql = "SELECT r.*, u.nome, u.handle, u.avatar 
                FROM relatos r 
                LEFT JOIN usuarios u ON r.usuario_id = u.id 
                ORDER BY r.criado_em DESC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obterEstatisticas() 
    {
        $sqlRecuperados = "SELECT COUNT(*) as total FROM relatos WHERE status = 'recuperado' AND DATE(criado_em) = CURDATE()";
        $sqlPendentes   = "SELECT COUNT(*) as total FROM relatos WHERE status IN ('perdido', 'achado')";

        $recuperados = $this->pdo->query($sqlRecuperados)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        $pendentes   = $this->pdo->query($sqlPendentes)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        return [
            'recuperados_hoje' => $recuperados,
            'pendentes'        => $pendentes
        ];
    }
}