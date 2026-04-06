<?php
/**
 * db.php - PDO MySQL Connection
 * Works on both Laragon (local) and Railway (production)
 */

function getDBConnection(): PDO
{
    // Try multiple ways to read env vars (Railway compatibility)
    $host     = $_ENV['MYSQL_HOST']     ?? getenv('MYSQL_HOST')     ?? 'localhost';
    $dbname   = $_ENV['MYSQL_DATABASE'] ?? getenv('MYSQL_DATABASE') ?? 'gamedb';
    $user     = $_ENV['MYSQL_USER']     ?? getenv('MYSQL_USER')     ?? 'root';
    $password = $_ENV['MYSQL_PASSWORD'] ?? getenv('MYSQL_PASSWORD') ?? '';
    $port     = $_ENV['MYSQL_PORT']     ?? getenv('MYSQL_PORT')     ?? '3306';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log('DB Connection Error: ' . $e->getMessage());
        die('<div style="font-family:monospace;padding:2rem;background:#0a0e1a;color:#e84545;min-height:100vh;">
            <strong>⚠ Database connection failed.</strong><br><br>
            Error: ' . htmlspecialchars($e->getMessage()) . '
        </div>');
    }
}
