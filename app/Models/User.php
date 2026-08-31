<?php

declare(strict_types=1);

/**
 * Model responsável pelas operações relacionadas
 * aos usuários da aplicação.
 *
 * Responsabilidades:
 *
 * - localizar usuário pelo e-mail;
 * - atualizar senha;
 * - centralizar o acesso à tabela usuarios.
 */
class User
{
    /**
     * Conexão com o banco de dados.
     */
    private PDO $pdo;


    /**
     * Recebe a conexão PDO utilizada pelo Model.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    /**
     * Localiza um usuário através do endereço de e-mail.
     *
     * Retorna os dados necessários para o processo
     * de autenticação.
     *
     * Retorna null quando o usuário não existe.
     */
    public function buscarPorEmail(string $email): ?array
    {
        $sql = "
            SELECT
                id,
                nome,
                email,
                senha,
                perfil,
                ativo
            FROM usuarios
            WHERE email = :email
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'email' => $email
        ]);

        $usuario = $stmt->fetch();

        if ($usuario === false) {
            return null;
        }

        return $usuario;
    }


    /**
     * Atualiza o hash da senha de um usuário.
     *
     * Utilizado quando password_needs_rehash()
     * identificar que o algoritmo ou os parâmetros
     * utilizados para criar o hash foram atualizados.
     */
    public function atualizarSenha(
        int $id,
        string $novoHash
    ): bool {

        $sql = "
            UPDATE usuarios
            SET senha = :senha
            WHERE id = :id
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'senha' => $novoHash,
            'id' => $id
        ]);
    }
}
