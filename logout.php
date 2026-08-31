<?php

declare(strict_types=1);

require_once __DIR__ . '/config/security.php';

require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/app/Models/User.php';

require_once __DIR__ . '/app/Models/LoginAttempt.php';

require_once __DIR__ . '/app/Controllers/AuthController.php';


/*
 * Cria o Model responsável pelos usuários.
 */
$user = new User($pdo);


/*
 * Cria o Model responsável pelas tentativas
 * de autenticação.
 */
$loginAttempt = new LoginAttempt($pdo);


/*
 * Cria o Controller responsável pelo processo
 * de autenticação.
 */
$controller = new AuthController(
    $user,
    $loginAttempt
);


/*
 * Encerra a sessão do usuário.
 */
$controller->logout();
