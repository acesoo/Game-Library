<?php
/**
 * db.php - PDO MySQL Connection
 * Pre-configured for Laragon local development.
 *
 * LARAGON defaults:
 *   host: 127.0.0.1 | user: root | password: (empty)
 *
 * When deploying to InfinityFree, swap these values with
 * your InfinityFree MySQL credentials.
 */

$config = [
    'host'     => '127.0.0.1',   // Laragon local MySQL
    'dbname'   => 'gamedb',      // Database name
    'user'     => 'root',        // Laragon default
    'password' => '',            // Laragon default (empty)
    'charset'  => 'utf8mb4',
];

/*
 * -------------------------------------------------------
 * WHEN DEPLOYING TO INFINITYFREE, change the values above:
 *   'host'     => 'sql200.infinityfree.com'
 *   'dbname'   => 'if0_12345678_gamedb'
 *   'user'     => 'if0_12345678_gamedb'
 *   'password' => 'your_db_password'
 * -------------------------------------------------------
 */

function getDBConnection(): PDO
{
    global $config;

    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

    try {
        $pdo = new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log('DB Connection Error: ' . $e->getMessage());
        die('<div style="font-family:monospace;padding:2rem;background:#0a0e1a;color:#e8294c;min-height:100vh;">
            <strong>⚠ Database connection failed.</strong><br><br>
            Check your credentials in db.php.<br>
            Make sure MySQL is running in Laragon.
        </div>');
    }
}
