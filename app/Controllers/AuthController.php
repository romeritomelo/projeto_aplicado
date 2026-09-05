<?php

declare(strict_types=1);

class AuthController
{
    /**
     * Model responsável pelos usuários.
     */
    private User $user;


    /**
     * Model responsável pelas tentativas
     * de autenticação.
     */
    private LoginAttempt $loginAttempt;


    /**
     * Número máximo de tentativas inválidas
     * permitidas para uma mesma conta.
     */
    private const MAX_TENTATIVAS_CONTA = 5;


    /**
     * Número máximo de tentativas inválidas
     * permitidas para um mesmo endereço IP.
     */
    private const MAX_TENTATIVAS_IP = 20;


    /**
     * Período utilizado para contabilizar
     * as tentativas inválidas.
     */
    private const JANELA_MINUTOS = 10;


    /**
     * Recebe os Models necessários para
     * executar o processo de autenticação.
     */
    public function __construct(
        User $user,
        LoginAttempt $loginAttempt
    ) {
        $this->user = $user;

        $this->loginAttempt = $loginAttempt;
    }


    /**
     * Processa o acesso à página de login.
     *
     * GET:
     * Exibe o formulário.
     *
     * POST:
     * Processa a tentativa de autenticação.
     */
    public function login(): void
    {
        if ($this->usuarioAutenticado()) {
            header('Location: /dashboard.php');
            exit;
        }

        /*
         * Garante que a sessão possua
         * um token CSRF.
         */
        $this->gerarTokenCsrf();

        $erro = '';

        $email = trim(
            (string)($_POST['email'] ?? '')
        );

        $senha = (string)(
            $_POST['senha'] ?? ''
        );

        /*
         * Obtém o endereço IP real do cliente.
         */
        $ip = $this->obterIpCliente();

        /*
         * Processa somente requisições POST.
         */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $erro = $this->processarLogin(
                $email,
                $senha,
                $ip
            );
        }

        /*
         * Carrega a View responsável
         * pela apresentação do formulário.
         */
        require __DIR__ . '/../Views/auth/login.php';
    }


    /**
     * Verifica se existe um usuário autenticado
     * na sessão atual.
     */
    private function usuarioAutenticado(): bool
    {
        return isset(
            $_SESSION['usuario_id']
        );
    }


    /**
     * Cria o token CSRF caso ainda
     * não exista na sessão.
     */
    private function gerarTokenCsrf(): void
    {
        if (empty($_SESSION['csrf_token'])) {

            $_SESSION['csrf_token'] = bin2hex(
                random_bytes(32)
            );
        }
    }


    /**
     * Processa todas as etapas de uma
     * tentativa de autenticação.
     */
    private function processarLogin(
        string $email,
        string $senha,
        string $ip
    ): string {

        /*
         * Valida o token CSRF enviado
         * pelo formulário.
         */
        if (!$this->validarCsrf()) {
            return 'E-mail ou senha inválidos.';
        }

        /*
         * Valida os dados básicos enviados
         * pelo usuário.
         */
        if (
            !$this->validarDadosLogin(
                $email,
                $senha
            )
        ) {
            return 'E-mail ou senha inválidos.';
        }

        /*
         * Verifica o limite de tentativas
         * para a conta.
         */
        if ($this->contaBloqueada($email)) {
            return 'E-mail ou senha inválidos.';
        }

        /*
         * Verifica o limite de tentativas
         * para o endereço IP.
         */
        if ($this->ipBloqueado($ip)) {
            return 'E-mail ou senha inválidos.';
        }

        /*
         * Solicita ao Model o usuário
         * correspondente ao e-mail informado.
         */
        $usuario = $this->user->buscarPorEmail(
            $email
        );

        /*
         * Verifica as credenciais fornecidas.
         */
        if (
            $usuario !== null &&
            (int)$usuario['ativo'] === 1 &&
            password_verify(
                $senha,
                $usuario['senha']
            )
        ) {

            /*
             * Registra a tentativa bem-sucedida.
             */
            $this->loginAttempt->registrar(
                $email,
                $ip,
                true
            );

            /*
             * Cria a sessão autenticada.
             */
            $this->criarSessaoAutenticada(
                $usuario
            );

            /*
             * Atualiza o hash da senha caso
             * seja necessário utilizar parâmetros
             * mais atuais.
             */
            $this->atualizarHashSeNecessario(
                $usuario,
                $senha
            );

            header('Location: /dashboard.php');
            exit;
        }

        /*
         * Registra a tentativa inválida.
         */
        $this->loginAttempt->registrar(
            $email,
            $ip,
            false
        );

        /*
         * Pequeno atraso para dificultar
         * ataques automatizados.
         */
        usleep(300000);

        return 'E-mail ou senha inválidos.';
    }


    /**
     * Valida o token CSRF enviado
     * pelo formulário de login.
     */
    private function validarCsrf(): bool
    {
        $token = (string)(
            $_POST['csrf_token'] ?? ''
        );

        return (
            $token !== '' &&
            isset($_SESSION['csrf_token']) &&
            hash_equals(
                $_SESSION['csrf_token'],
                $token
            )
        );
    }


    /**
     * Valida os dados básicos utilizados
     * durante a autenticação.
     */
    private function validarDadosLogin(
        string $email,
        string $senha
    ): bool {

        if (strlen($email) > 150) {
            return false;
        }

        if (
            $email === '' ||
            $senha === ''
        ) {
            return false;
        }

        if (
            filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            ) === false
        ) {
            return false;
        }

        return true;
    }


    /**
     * Obtém o endereço IP real do cliente.
     *
     * Quando a requisição passa pelo Traefik,
     * REMOTE_ADDR corresponde ao IP do Traefik.
     *
     * Nesse caso utilizamos X-Real-IP,
     * conforme configurado no ambiente.
     */
    private function obterIpCliente(): string
    {
        $remoteAddr =
            $_SERVER['REMOTE_ADDR'] ?? '';

        if ($remoteAddr === '172.18.0.3') {

            $ip =
                $_SERVER['HTTP_X_REAL_IP'] ?? '';

            if (
                filter_var(
                    $ip,
                    FILTER_VALIDATE_IP
                ) !== false
            ) {
                return $ip;
            }
        }

        if (
            filter_var(
                $remoteAddr,
                FILTER_VALIDATE_IP
            ) !== false
        ) {
            return $remoteAddr;
        }

        return '0.0.0.0';
    }


    /**
     * Verifica se o endereço de e-mail
     * atingiu o limite de tentativas inválidas.
     */
    private function contaBloqueada(
        string $email
    ): bool {

        $quantidade =
            $this->loginAttempt
                ->contarFalhasPorEmail(
                    $email,
                    self::JANELA_MINUTOS
                );

        return $quantidade
            >= self::MAX_TENTATIVAS_CONTA;
    }


    /**
     * Verifica se o endereço IP atingiu
     * o limite de tentativas inválidas.
     */
    private function ipBloqueado(
        string $ip
    ): bool {

        $quantidade =
            $this->loginAttempt
                ->contarFalhasPorIp(
                    $ip,
                    self::JANELA_MINUTOS
                );

        return $quantidade
            >= self::MAX_TENTATIVAS_IP;
    }


    /**
     * Cria a sessão do usuário após
     * uma autenticação bem-sucedida.
     *
     * A regeneração do ID impede ataques
     * de session fixation.
     */
    private function criarSessaoAutenticada(
        array $usuario
    ): void {

        session_regenerate_id(true);

        /*
         * Gera um novo token CSRF depois
         * da autenticação.
         */
        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );

        $_SESSION['usuario_id'] =
            $usuario['id'];

        $_SESSION['usuario_nome'] =
            $usuario['nome'];

        $_SESSION['usuario_email'] =
            $usuario['email'];

        $_SESSION['usuario_perfil'] =
            $usuario['perfil'];

        /*
         * Registra os tempos utilizados
         * pelo controle de expiração da sessão.
         */
        $_SESSION['login_time'] = time();

        $_SESSION['last_activity'] = time();
    }


    /**
     * Atualiza o hash da senha caso o algoritmo
     * ou seus parâmetros tenham evoluído.
     *
     * A operação de banco é realizada pelo
     * Model User.
     */
    private function atualizarHashSeNecessario(
        array $usuario,
        string $senha
    ): void {

        if (
            !password_needs_rehash(
                $usuario['senha'],
                PASSWORD_DEFAULT
            )
        ) {
            return;
        }

        $novoHash = password_hash(
            $senha,
            PASSWORD_DEFAULT
        );

        $this->user->atualizarSenha(
            (int)$usuario['id'],
            $novoHash
        );
    }


    /**
     * Encerra a sessão do usuário.
     *
     * Remove os dados da sessão, invalida
     * o cookie de sessão e destrói a sessão
     * existente no servidor.
     */
    public function logout(): void
    {
        /*
         * O encerramento de sessão altera o estado da aplicação;
         * por isso, aceita somente POST protegido por token CSRF.
         */
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            exit;
        }

        if (!$this->validarCsrf()) {
            http_response_code(403);
            exit;
        }

        /*
         * Remove todos os dados armazenados
         * na sessão atual.
         */
        $_SESSION = [];

        /*
         * Remove o cookie de sessão
         * do navegador.
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
         * Destrói a sessão no servidor.
         */
        session_destroy();

        /*
         * Retorna o usuário para
         * a tela de login.
         */
        header('Location: /login.php');

        exit;
    }
}
