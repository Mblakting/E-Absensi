<?php
$file = __DIR__ . '/../vendor/laravel/framework/config/database.php';

if (!file_exists($file)) exit(0);

$content = file_get_contents($file);
$patched = str_replace('PDO::MYSQL_ATTR_SSL_CA', 'Pdo\\Mysql::ATTR_SSL_CA', $content);

if ($content !== $patched) {
    file_put_contents($file, $patched);
    echo "Patched: vendor/laravel/framework/config/database.php\n";
}
