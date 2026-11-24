<?php

class Mensagem
{
    private $id;
    private $remetenteId;
    private $destinatarioId;
    private $relatoId;
    private $mensagem;
    private $criadoEm;
    private $lidaEm;

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getRemetenteId() { return $this->remetenteId; }
    public function setRemetenteId($remetenteId) { $this->remetenteId = $remetenteId; }
    public function getDestinatarioId() { return $this->destinatarioId; }
    public function setDestinatarioId($destinatarioId) { $this->destinatarioId = $destinatarioId; }
    public function getRelatoId() { return $this->relatoId; }
    public function setRelatoId($relatoId) { $this->relatoId = $relatoId; }
    public function getMensagem() { return $this->mensagem; }
    public function setMensagem($mensagem) { $this->mensagem = $mensagem; }
    public function getCriadoEm() { return $this->criadoEm; }
    public function setCriadoEm($criadoEm) { $this->criadoEm = $criadoEm; }
    public function getLidaEm() { return $this->lidaEm; }
    public function setLidaEm($lidaEm) { $this->lidaEm = $lidaEm; }
}
