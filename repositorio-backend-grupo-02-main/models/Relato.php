<?php

class Relato 
{
    private $id;
    private $usuarioId;
    private $status;
    private $local;
    private $descricao;
    private $imagem;
    private $criadoEm;

    // Getters e Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getUsuarioId() { return $this->usuarioId; }
    public function setUsuarioId($usuarioId) { $this->usuarioId = $usuarioId; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; }

    public function getLocal() { return $this->local; }
    public function setLocal($local) { $this->local = $local; }

    public function getDescricao() { return $this->descricao; }
    public function setDescricao($descricao) { $this->descricao = $descricao; }

    public function getImagem() { return $this->imagem; }
    public function setImagem($imagem) { $this->imagem = $imagem; }

    public function getCriadoEm() { return $this->criadoEm; }
    public function setCriadoEm($criadoEm) { $this->criadoEm = $criadoEm; }
}