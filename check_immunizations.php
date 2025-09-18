<?php
require 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Connect to database
$pdo = new PDO(
    'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'],
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD']
);

echo "=== IMMUNIZATION RECORDS SAMPLE ===\n";
echo "Vaccine Name | Dose | Schedule Date | Status | Next Due Date\n";
echo "--------------------------------------------------------\n";

$stmt = $pdo->query("SELECT vaccine_name, dose, schedule_date, status, next_due_date FROM immunizations LIMIT 10");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    printf("%s | %s | %s | %s | %s\n",
        $row['vaccine_name'] ?? 'NULL',
        $row['dose'] ?? 'NULL',
        $row['schedule_date'] ?? 'NULL',
        $row['status'] ?? 'NULL',
        $row['next_due_date'] ?? 'NULL'
    );
}
?>