<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$pdo = new PDO(
    'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'],
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD']
);

echo "=== VACCINES IN DATABASE ===\n";
$stmt = $pdo->query("SELECT DISTINCT vaccine_name FROM immunizations ORDER BY vaccine_name");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- " . $row['vaccine_name'] . "\n";
}
?>