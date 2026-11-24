<?php
class Usuario
{
    const PERFIL_COMUM = 'comum';
    const PERFIL_ADMINISTRADOR = 'administrador';
    const PERFIL_ROOT = 'root';

    private $id;
    private $nome;
    private $email;
    private $telefone;
    private $cpf;
    private $dataNascimento;
    private $cep;
    private $endereco;
    private $senha;
    private $perfil;

    public function __construct(
        $id = null,
        $nome = '',
        $email = '',
        $telefone = '',
        $cpf = '',
        $dataNascimento = '',
        $cep = '',
        $endereco = '',
        $senha = '',
        $perfil = self::PERFIL_COMUM
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->telefone = $telefone;
        $this->cpf = $cpf;
        $this->dataNascimento = $dataNascimento;
        $this->cep = $cep;
        $this->endereco = $endereco;
        $this->senha = $senha;
        $this->perfil = $perfil;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getTelefone()
    {
        return $this->telefone;
    }

    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function setCpf($cpf)
    {
        $this->cpf = $cpf;
    }

    public function getDataNascimento()
    {
        return $this->dataNascimento;
    }

    public function setDataNascimento($dataNascimento)
    {
        $this->dataNascimento = $dataNascimento;
    }

    public function getCep()
    {
        return $this->cep;
    }

    public function setCep($cep)
    {
        $this->cep = $cep;
    }

    public function getEndereco()
    {
        return $this->endereco;
    }

    public function setEndereco($endereco)
    {
        $this->endereco = $endereco;
    }

    public function getSenha()
    {
        return $this->senha;
    }

    public function setSenha($senha)
    {
        $this->senha = $senha;
    }

    public function getPerfil()
    {
        return $this->perfil;
    }

    public function setPerfil($perfil)
    {
        $this->perfil = $perfil;
    }
}
?>
