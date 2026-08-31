<?php

declare(strict_types=1);

/**
 * Model responsável pelas operações relacionadas
 * às tentativas de autenticação.
 *
 * Responsabilidades:
 *
 * - verificar tentativas inválidas por e-mail;
 * - verificar tentativas inválidas por endereço IP;
 * - registrar tentativas de autenticação;
 * - centralizar o acesso à tabela tentativas_login.
 */
class LoginAttempt
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
     * Conta as tentativas inválidas de um endereço
     * de e-mail dentro da janela de tempo informada.
     */
    public function contarFalhasPorEmail(
        string $email,
        int $janelaMinutos
    ): int {

        $janelaMinutos = max(
            1,
            $janelaMinutos
        );

        $sql = "
            SELECT COUNT(*)
            FROM tentativas_login
            WHERE email = :email
              AND sucesso = 0
              AND criado_em >= DATE_SUB(
                  NOW(),
                  INTERVAL {$janelaMinutos} MINUTE
              )
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'email' => $email
        ]);

        return (int)$stmt->fetchColumn();
    }


    /**
     * Conta as tentativas inválidas de um endereço
     * IP dentro da janela de tempo informada.
     */
    public function contarFalhasPorIp(
        string $ip,
        int $janelaMinutos
    ): int {

        $janelaMinutos = max(
            1,
            $janelaMinutos
        );

        $sql = "
            SELECT COUNT(*)
            FROM tentativas_login
            WHERE ip_address = :ip
              AND sucesso = 0
              AND criado_em >= DATE_SUB(
                  NOW(),
                  INTERVAL {$janelaMinutos} MINUTE
              )
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'ip' => $ip
        ]);

        return (int)$stmt->fetchColumn();
    }


    /**
     * Registra uma tentativa de autenticação
     * realizada pelo usuário.
     */
    public function registrar(
        string $email,
        string $ip,
        bool $sucesso
    ): bool {

        $sql = "
            INSERT INTO tentativas_login
                (
                    email,
                    ip_address,
                    sucesso
                )
            VALUES
                (
                    :email,
                    :ip,
                    :sucesso
                )
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'email' => $email,
            'ip' => $ip,
            'sucesso' => $sucesso ? 1 : 0
        ]);
    }
}
