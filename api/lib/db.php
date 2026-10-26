<?php
require_once __DIR__ . '/../config.php';

/**
 * Returns a shared PDO connection to the MySQL database.
 */
if (!function_exists('get_db_if_connected')) {
    function get_db_if_connected(): ?PDO { 
        return $GLOBALS['ci_database_connection'] ?? null; 
    }
}

if (!function_exists('get_db')) {
    function get_db(): PDO {
        $pdo = get_db_if_connected();
        if ($pdo !== null) {
            return $pdo;
        }

        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $pdo->exec("SET time_zone = '+00:00'");
            $GLOBALS['ci_database_connection'] = $pdo;
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'The database is unavailable. Please try again later.',
            ]);
            exit(1);
        }

        return $pdo;
    }
}