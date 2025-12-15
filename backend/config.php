<?php
class Database {
   private static $host = 'localhost';
   private static $dbName = 'watchifydb';
   private static $username = 'root';
   private static $password = 'root717';
   private static $connection = null;


   public static function connect() {
       if (self::$connection === null) {
           try {
               self::$connection = new PDO(
                   "mysql:host=" . self::$host . ";dbname=" . self::$dbName,
                   self::$username,
                   self::$password,
                   [
                       PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                       PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                   ]
               );
           } catch (PDOException $e) {
               die("Connection failed: " . $e->getMessage());
           }
       }
       return self::$connection;
   }
}

class Config {
    // JWT secret key
    public static function JWT_SECRET() {
        return 'watchify_jwt_secret_key_2024_secure_random_string_change_in_production';
    }

    // JWT time-to-live in seconds (24 hours)
    public static function JWT_TTL_SECONDS() {
        return 60 * 60 * 24;
    }


    public static function PUBLIC_ROUTES() {
        return [
            ['method' => 'GET',  'path' => '/'],
            ['method' => 'POST', 'path' => '/auth/login'],
            ['method' => 'POST', 'path' => '/auth/register'],
        ];
    }

    // URL prefixes that should be public (match by "starts with")
    public static function PUBLIC_URL_PREFIXES() {
        return [
            '/docs',
            '/public',
            '/public/v1/docs',
            // Public product listing APIs
            '/api/products/available',
            '/api/products/category',
            '/api/products/brand',
            '/api/products/brands',
            '/api/categories',
        ];
    }
}