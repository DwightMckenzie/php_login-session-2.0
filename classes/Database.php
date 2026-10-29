<?php

    /**
     * Used with the config file outside of the root
     */
    declare(strict_types=1);

    class Database
    {
        private const CONFIG_FILE = '/etc/php-secured-session/db.php';

        private static ?PDO $pdo = null;

        public static function getConn(): PDO
        {
            if (self::$pdo === null) {
                if (!is_readable(self::CONFIG_FILE)) {
                    error_log('DB config not readable: ' . self::CONFIG_FILE);
                    http_response_code(500);
                    exit('Service temporarily unavailable.');
                }

                $config = require self::CONFIG_FILE;

                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=utf8mb4',
                    $config['host'],
                    $config['name']
                );

                try {
                    self::$pdo = new PDO($dsn, $config['user'], $config['pass'], [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                } catch (PDOException $e) {
                    error_log('DB connection failed: ' . $e->getMessage());
                    http_response_code(500);
                    exit('Service temporarily unavailable.');
                }
            }

            return self::$pdo;
        }
    }
?>