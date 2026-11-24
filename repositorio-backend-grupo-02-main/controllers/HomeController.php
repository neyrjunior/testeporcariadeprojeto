<?php

namespace App\Controllers;

class HomeController
{
    public function index(): void
    {
        // Inicia a sessão
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Dados do usuário
        $nome = $_SESSION['user_name'] ?? 'Usuário Visitante';
        $handle = $_SESSION['user_handle'] ?? '@usuario';
        $avatar = $_SESSION['user_avatar'] ?? 'https://via.placeholder.com/150';

        // Dados das estatísticas
        $recuperados = 0;
        $pendentes = 0;

        // Carrega a página HTML/PHP
        require_once __DIR__ . '/../views/home.php';
    }
}