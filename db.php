<?php
/**
 * db.php - PDO MySQL Connection
 *
 * Automatically works in BOTH environments:
 *  - Laragon (local)  → uses hardcoded fallback values below
 *  - Railway (live)   → reads environment variables automatically
 *
 * No changes needed when switching between local and Railway!
 */

function getDBConnection(): PDO
{
    // Railway injects these env vars automatically.
    // The fallback values (after ?:) are your Laragon local credentials.
    $host     = getenv('MYSQL_HOST')     ?: 'localhost';
    $dbname   = getenv('MYSQL_DATABASE') ?: 'gamedb';
    $user     = getenv('MYSQL_USER')     ?: 'root';
    $password = getenv('MYSQL_PASSWORD') ?: '';
    $port     = getenv('MYSQL_PORT')     ?: '3306';

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
            Check your credentials or Railway environment variables.
        </div>');
    }
}
