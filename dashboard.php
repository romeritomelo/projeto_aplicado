<?php

declare(strict_types=1);

require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/app/Controllers/DashboardController.php';

$controller = new DashboardController();

$controller->index();
