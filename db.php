<?php
$host    = preg_replace('#^https?://|/$#', '', getenv('DB_HOST') ?: 'auth-db941.hstgr.io');
$db      = getenv('DB_NAME') ?: 'u237055794_team04';
$user    = getenv('DB_USER') ?: 'u237055794_team04';
$pass    = getenv('DB_PASS') ?: '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
