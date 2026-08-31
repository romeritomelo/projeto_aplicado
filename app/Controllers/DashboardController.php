<?php

declare(strict_types=1);

class DashboardController
{
    /**
     * Exibe a página principal do dashboard.
     *
     * A autenticação e o controle de expiração
     * da sessão são realizados pelo security.php.
     */
    public function index(): void
    {
        /*
         * Verifica se existe um usuário autenticado.
         *
         * Caso contrário, redireciona para o login.
         */
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login.php');
            exit;
        }

        /*
         * Dados básicos do usuário autenticado.
         *
         * Posteriormente esses dados poderão vir
         * de um Model ou de um Service.
         */
        $usuario = [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'] ?? '',
            'email' => $_SESSION['usuario_email'] ?? '',
            'perfil' => $_SESSION['usuario_perfil'] ?? ''
        ];

        /*
         * Carrega a View do dashboard.
         *
         * A View recebe os dados através da variável
         * $usuario e fica responsável somente pela
         * apresentação das informações.
         */
        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
