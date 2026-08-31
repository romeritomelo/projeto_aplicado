<?php

header('Content-Type: text/plain');

echo "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . PHP_EOL;
echo "X-Real-IP: " . ($_SERVER['HTTP_X_REAL_IP'] ?? 'N/A') . PHP_EOL;
echo "X-Forwarded-For: " . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'N/A') . PHP_EOL;
echo "X-Forwarded-Proto: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'N/A') . PHP_EOL;

?>
