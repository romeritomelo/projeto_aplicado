<?php

declare(strict_types=1);

/**
 * Configurações centralizadas de segurança da aplicação.
 *
 * Responsabilidades:
 *
 * - configurar parâmetros seguros da sessão;
 * - configurar headers HTTP de segurança;
 * - iniciar a sessão;
 * - controlar expiração por inatividade;
 * - controlar tempo máximo da sessão.
 *
 * Este arquivo deve ser carregado antes de qualquer
 * saída HTML ou envio de headers HTTP.
 */


/*
 * =========================================================
 * CONFIGURAÇÕES DA SESSÃO
 * =========================================================
 */

/**
 * Tempo máximo de inatividade permitido.
 *
 * 1800 segundos = 30 minutos.
 */
const SESSAO_INATIVIDADE_SEGUNDOS = 1800;


/**
 * Tempo máximo absoluto de uma sessão autenticada.
 *
 * 28800 segundos = 8 horas.
 */
const SESSAO_MAXIMA_SEGUNDOS = 28800;


/*
 * =========================================================
 * CONFIGURAÇÃO DOS COOKIES DA SESSÃO
 * =========================================================
 *
 * Estas configurações precisam ser realizadas
 * antes de session_start().
 */


/**
 * Impede a utilização de um identificador de sessão
 * que não tenha sido criado pelo servidor.
 */
ini_set(
    'session.use_strict_mode',
    '1'
);


/**
 * Permite que o identificador da sessão seja enviado
 * somente através de cookie.
 *
 * Isso impede o uso de IDs de sessão na URL.
 */
ini_set(
    'session.use_only_cookies',
    '1'
);


/**
 * Impede que JavaScript tenha acesso ao cookie
 * da sessão.
 */
ini_set(
    'session.cookie_httponly',
    '1'
);


/**
 * Permite o envio do cookie somente através
 * de conexões HTTPS.
 */
ini_set(
    'session.cookie_secure',
    '1'
);


/**
 * Reduz o risco de envio automático do cookie
 * em requisições originadas de outros sites.
 */
ini_set(
    'session.cookie_samesite',
    'Lax'
);


/*
 * =========================================================
 * CONFIGURAÇÕES GERAIS DO PHP
 * =========================================================
 */


/**
 * Não informa ao navegador que a aplicação
 * está utilizando PHP.
 */
ini_set(
    'expose_php',
    '0'
);


/*
 * =========================================================
 * HEADERS HTTP DE SEGURANÇA
 * =========================================================
 */


/**
 * Impede que o navegador interprete um conteúdo
 * utilizando um MIME type diferente daquele declarado.
 */
header(
    'X-Content-Type-Options: nosniff'
);


/**
 * Impede que a aplicação seja carregada
 * dentro de frames.
 *
 * Proteção contra ataques de clickjacking.
 */
header(
    'X-Frame-Options: DENY'
);


/**
 * Controla quais informações de referência
 * podem ser enviadas pelo navegador.
 */
header(
    'Referrer-Policy: strict-origin-when-cross-origin'
);


/**
 * Desabilita recursos do navegador que não são
 * utilizados pela aplicação.
 */
header(
    'Permissions-Policy: camera=(), microphone=(), geolocation=()'
);


/*
 * =========================================================
 * CONTENT SECURITY POLICY
 * =========================================================
 *
 * Permite somente recursos provenientes da própria
 * aplicação.
 *
 * A aplicação utiliza:
 *
 * /css/style.css
 * /js/app.js
 *
 * Não são permitidos scripts ou estilos externos.
 */
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
 * =========================================================
 * HTTP STRICT TRANSPORT SECURITY
 * =========================================================
 *
 * Informa ao navegador que a aplicação deve ser
 * acessada exclusivamente através de HTTPS.
 *
 * O domínio já está funcionando através de HTTPS,
 * portanto este header pode ser utilizado.
 */
header(
    'Strict-Transport-Security: max-age=31536000; includeSubDomains'
);


/*
 * =========================================================
 * INICIALIZAÇÃO DA SESSÃO
 * =========================================================
 *
 * A sessão somente será iniciada se ainda não estiver
 * ativa.
 *
 * Isso evita:
 *
 * "session_start(): Ignoring session_start()
 * because a session is already active"
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/*
 * =========================================================
 * CONTROLE DE EXPIRAÇÃO DA SESSÃO
 * =========================================================
 *
 * O controle abaixo somente é executado para usuários
 * que já estejam autenticados.
 */
if (isset($_SESSION['usuario_id'])) {

    $agora = time();

    $ultimoAcesso =
        (int)($_SESSION['last_activity'] ?? 0);

    $inicioSessao =
        (int)($_SESSION['login_time'] ?? 0);


    /*
     * =====================================================
     * EXPIRAÇÃO POR INATIVIDADE
     * =====================================================
     *
     * Se o usuário permanecer mais de 30 minutos
     * sem realizar nenhuma requisição autenticada,
     * a sessão será encerrada.
     */
    if (
        $ultimoAcesso > 0 &&
        ($agora - $ultimoAcesso)
            > SESSAO_INATIVIDADE_SEGUNDOS
    ) {

        destruirSessao();

        header(
            'Location: /login.php?expirada=1'
        );

        exit;
    }


    /*
     * =====================================================
     * EXPIRAÇÃO PELO TEMPO MÁXIMO
     * =====================================================
     *
     * Mesmo que o usuário continue ativo,
     * a sessão não poderá ultrapassar 8 horas.
     */
    if (
        $inicioSessao > 0 &&
        ($agora - $inicioSessao)
            > SESSAO_MAXIMA_SEGUNDOS
    ) {

        destruirSessao();

        header(
            'Location: /login.php?expirada=1'
        );

        exit;
    }


    /*
     * =====================================================
     * ATUALIZAÇÃO DA ATIVIDADE
     * =====================================================
     *
     * A cada requisição autenticada, registra o momento
     * do último acesso.
     */
    $_SESSION['last_activity'] = $agora;
}


/*
 * =========================================================
 * FUNÇÃO DE DESTRUIÇÃO DA SESSÃO
 * =========================================================
 *
 * Centraliza o procedimento utilizado quando uma sessão
 * precisa ser encerrada.
 */
function destruirSessao(): void
{
    /*
     * Remove todos os dados armazenados na sessão.
     */
    $_SESSION = [];


    /*
     * Remove o cookie da sessão no navegador.
     */
    if (ini_get('session.use_cookies')) {

        $params =
            session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool)$params['secure'],
            (bool)$params['httponly']
        );
    }


    /*
     * Destrói a sessão existente no servidor.
     */
    session_destroy();
}
