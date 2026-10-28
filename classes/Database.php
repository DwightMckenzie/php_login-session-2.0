<?php

    /**
     * Used with the config file out side of the root
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





  /**
   * Revised by AI 
   * 
   * 
   * 
  declare(strict_types=1);

  class Database
  {
      private static ?PDO $pdo = null;

      public static function getConn(): PDO
      {
          if (self::$pdo === null) {
              // Credentials come from the environment, never from source code.
              $dsn = sprintf(
                  'mysql:host=%s;dbname=%s;charset=utf8mb4',
                  getenv('DB_HOST') ?: 'localhost',
                  getenv('DB_NAME')
              );

              try {
                  self::$pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
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
  }*/


  /**
   * My original version
   * connect to database
   *
  class Database {
    public function getConn() {
      $db_host = "localhost";
      $db_user = "dwight1980";
      $db_pass = "Mckenzie1980%";
      $db_name = "session_db";

      // need to use PDO object string
      // preditermined property object >> $dsn << Data Source Name
      // pay attention to the string paramters each have a puprose!!
      $dsn = 'mysql:host=' . $db_host . ';dbname=' . $db_name . ';charset=utf8';

      try {
          $db = new PDO($dsn, $db_user, $db_pass);
          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          return $db;
      } catch (PDOException $e) {
          echo $e->getMessage();
          exit;
      }
    }
  }
  */


?>