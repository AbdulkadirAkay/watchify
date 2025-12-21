<?php
class Database {
   private static $connection = null;

   public static function connect() {
       if (self::$connection === null) {
           try {
               self::$connection = new PDO(
                    "mysql:host=" . Config::DB_HOST() . ";port=" . Config::DB_PORT() . ";dbname=" . Config::DB_NAME(),                   Config::DB_USER(),
                   Config::DB_PASSWORD(),
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

    public static function DB_NAME() {
        return Config::get_env("DB_NAME", "watchifydb");
    }
    public static function DB_PORT() {
        return Config::get_env("DB_PORT", 3306);
    }
    public static function DB_USER() {
        return Config::get_env("DB_USER", 'root');
    }
    public static function DB_PASSWORD() {
        return Config::get_env("DB_PASSWORD", 'root717');
    }
    public static function DB_HOST() {
        return Config::get_env("DB_HOST", '127.0.0.1');
    }

    
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
    public static function get_env($name, $default){
        return isset($_ENV[$name]) && trim($_ENV[$name]) != "" ? $_ENV[$name] : $default;
    } 
}