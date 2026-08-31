<?php

declare(strict_types=1);

/**
 * Controller responsável pelos mecanismos de segurança
 * relacionados à sessão e às requisições HTTP.
 *
 * Responsabilidades:
 *
 * - configurar os parâmetros de segurança da sessão;
 * - iniciar a sessão com segurança;
 * - aplicar headers HTTP de segurança;
 * - controlar o tempo de inatividade da sessão;
 * - controlar o tempo máximo de uma sessão;
 * - destruir sessões expiradas;
 * - redirecionar o usuário quando a sessão expirar.
 */
class SecurityController
{
    /**
     * Tempo máximo permitido sem atividade do usuário.
     *
     * 1800 segundos = 30 minutos.
     */
    private const SESSAO_INATIVIDADE_SEGUNDOS = 1800;

    /**
     * Tempo máximo absoluto de uma sessão.
     *
     * 28800 segundos = 8 horas.
     */
    private const SESSAO_MAXIMA_SEGUNDOS = 28800;

    /**
     * Configura os parâmetros de segurança
     * utilizados pelo cookie da sessão.
     *
     * Essas configurações devem ser aplicadas
     * antes de session_start().
     */
    public function configurarSessao(): void
    {
        ini_set(
            'session.use_strict_mode',
            '1'
        );

        ini_set(
            'session.use_only_cookies',
            '1'
        );

        ini_set(
            'session.cookie_httponly',
            '1'
        );

        ini_set(
            'session.cookie_secure',
            '1'
        );

        ini_set(
            'session.cookie_samesite',
            'Lax'
        );
    }

    /**
     * Inicia a sessão caso ainda não exista
     * uma sessão ativa.
     */
    public function iniciarSessao(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Aplica headers HTTP destinados a reduzir
     * diferentes tipos de ataques no navegador.
     *
     * Os headers utilizados são:
     *
     * - X-Content-Type-Options;
     * - X-Frame-Options;
     * - Referrer-Policy;
     * - Permissions-Policy;
     * - Content-Security-Policy;
     * - Strict-Transport-Security.
     */
    public function aplicarHeadersSeguranca(): void
    {
        header(
            'X-Content-Type-Options: nosniff'
        );

        header(
            'X-Frame-Options: DENY'
        );

        header(
            'Referrer-Policy: strict-origin-when-cross-origin'
        );

        header(
            'Permissions-Policy: camera=(), microphone=(), geolocation=()'
        );

        header(
            "Content-Security-Policy: "
            . "default-src 'self'; "
            . "style-src 'self'; "
            . "script-src 'self'; "
            . "img-src 'self' data:; "
            . "font-src 'self'; "
            . "form-action 'self'; "
            . "frame-ancestors 'none'; "
            . "base-uri 'self'; "
            . "object-src 'none'"
        );

        /*
         * HSTS determina que o navegador deverá utilizar
         * HTTPS durante o período definido.
         *
         * Esta aplicação já utiliza HTTPS através do Traefik.
         */
        header(
            'Strict-Transport-Security: max-age=31536000; includeSubDomains'
        );
    }

    /**
     * Verifica se a sessão autenticada ainda está dentro
     * dos limites de tempo estabelecidos.
     *
     * São verificados dois limites:
     *
     * 1. tempo máximo de inatividade;
     * 2. tempo máximo absoluto da sessão.
     */
    public function verificarExpiracaoSessao(): void
    {
        /*
         * Se não existir usuário autenticado,
         * não há sessão protegida para verificar.
         */
        if (!isset($_SESSION['usuario_id'])) {
            return;
        }

        $agora = time();

        $ultimoAcesso = (int) (
            $_SESSION['last_activity'] ?? 0
        );

        $inicioSessao = (int) (
            $_SESSION['login_time'] ?? 0
        );

        /*
         * Verifica se o usuário ficou tempo demais
         * sem realizar nenhuma atividade.
         */
        if (
            $ultimoAcesso > 0
            && ($agora - $ultimoAcesso)
                > self::SESSAO_INATIVIDADE_SEGUNDOS
        ) {
            $this->encerrarSessao(
                'inatividade'
            );

            return;
        }

        /*
         * Verifica se a sessão atingiu seu tempo
         * máximo absoluto.
         */
        if (
            $inicioSessao > 0
            && ($agora - $inicioSessao)
                > self::SESSAO_MAXIMA_SEGUNDOS
        ) {
            $this->encerrarSessao(
                'tempo_maximo'
            );

            return;
        }

        /*
         * Registra a última atividade do usuário.
         */
        $_SESSION['last_activity'] = $agora;
    }

    /**
     * Encerra uma sessão expirada e redireciona
     * o usuário para a tela de login.
     *
     * O motivo da expiração é enviado através
     * de um parâmetro na URL.
     */
    private function encerrarSessao(
        string $motivo
    ): void {
        /*
         * Remove todos os dados armazenados
         * na sessão atual.
         */
        $_SESSION = [];

        /*
         * Remove o cookie da sessão do navegador.
         */
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        /*
         * Destrói a sessão no servidor.
         */
        session_destroy();

        /*
         * Redireciona para o login informando
         * que a sessão foi encerrada.
         */
        header(
            'Location: /login.php?expirada=1&motivo='
            . urlencode($motivo)
        );

        exit;
    }

    /**
     * Executa a sequência básica de segurança
     * necessária para uma requisição da aplicação.
     *
     * Este método permite que as páginas utilizem
     * apenas uma chamada para inicializar a segurança.
     */
    public function iniciarSeguranca(): void
    {
        /*
         * Configura o cookie antes de iniciar a sessão.
         */
        $this->configurarSessao();

        /*
         * Inicia a sessão.
         */
        $this->iniciarSessao();

        /*
         * Aplica os headers HTTP de segurança.
         */
        $this->aplicarHeadersSeguranca();

        /*
         * Verifica se uma sessão autenticada
         * expirou por tempo.
         */
        $this->verificarExpiracaoSessao();
    }
}
?>

