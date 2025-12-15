<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../data/Roles.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    /**
     * Basic logging function
     */
    private static function log($message, $level = 'INFO') {
        $logFile = __DIR__ . '/../logs/api.log';
        $logDir = dirname($logFile);
        
        // Create logs directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Request validation
     */
    private static function validateRequest() {
        $method = Flight::request()->method;
        $url = Flight::request()->url;
        
        // Log request
        self::log("Request: $method $url");
        
        // Basic request validation
        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            $contentType = Flight::request()->type;
            if (strpos($contentType, 'application/json') === false && 
                strpos($contentType, 'application/x-www-form-urlencoded') === false) {
                self::log("Invalid content type: $contentType", 'WARNING');
            }
        }
        
        return true;
    }

    /**
     * Error handling wrapper
     */
    private static function handleError($message, $code = 401) {
        self::log("Error: $message", 'ERROR');
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit();
    }


    public static function handle() {
        try {
            // Request validation
            self::validateRequest();
            
            $method = Flight::request()->method;
            $url    = Flight::request()->url;


            $publicRoutes   = Config::PUBLIC_ROUTES();
            $publicPrefixes  = Config::PUBLIC_URL_PREFIXES();


            foreach ($publicRoutes as $route) {
                if (
                    isset($route['method'], $route['path']) &&
                    strtoupper($route['method']) === strtoupper($method) &&
                    $route['path'] === $url
                ) {
                    return true;
                }
            }

            // Prefix match check (for docs and static assets)
            $isPublic = false;
            foreach ($publicPrefixes as $prefix) {
                if (strpos($url, $prefix) === 0) {
                    $isPublic = true;
                    break;
                }
            }
            
            // Skip authentication for public routes
            if ($isPublic) {
                return true;
            }
            
            $authHeader = Flight::request()->getHeader('Authentication');

            if (!$authHeader) {
                self::handleError('Missing authentication header', 401);
            }
            
            $token = $authHeader;
            if (strpos($authHeader, 'Bearer ') === 0) {
                $token = substr($authHeader, 7);
            }
            
            if (empty($token)) {
                self::handleError('Invalid authentication token', 401);
                return false;
            }
            
            // Verify and decode JWT token
            try {
                $decoded = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));
                
                // Store user data in Flight for use in routes
                Flight::set('user', $decoded->user);
                Flight::set('jwt_token', $token);
                
                self::log("Authenticated user: " . ($decoded->user->email ?? 'unknown'));
                
                return true;
            } catch (\Firebase\JWT\ExpiredException $e) {
                self::handleError('Token has expired', 401);
                return false;
            } catch (\Firebase\JWT\SignatureInvalidException $e) {
                self::handleError('Invalid token signature', 401);
                return false;
            } catch (\Exception $e) {
                self::handleError('Token validation failed: ' . $e->getMessage(), 401);
                return false;
            }
            
        } catch (\Exception $e) {
            self::handleError('Middleware error: ' . $e->getMessage(), 500);
            return false;
        }
    }



    // Require a single role (e.g. admin-only endpoints)
    public static function authorizeRole($requiredRole) {
        $user = Flight::get('user');

        if (!$user || !isset($user->role) || $user->role !== $requiredRole) {
            self::handleError('Access denied: insufficient privileges', 403);
        }
    }

    // Require one of multiple roles
    public static function authorizeRoles($roles) {
        $user = Flight::get('user');

        if (
            !$user ||
            !isset($user->role) ||
            !in_array($user->role, $roles, true)
        ) {
            self::handleError('Forbidden: role not allowed', 403);
        }
    }

    // Optional permission-based check (for future fine-grained permissions)
    public static function authorizePermission($permission) {
        $user = Flight::get('user');

        if (
            !$user ||
            !isset($user->permissions) ||
            !is_array($user->permissions) ||
            !in_array($permission, $user->permissions, true)
        ) {
            self::handleError('Access denied: permission missing', 403);
        }
    }

    public static function authorizeCurrentUserOrAdmin($resourceUserId) {
        $user = Flight::get('user');

        if (!$user) {
            self::handleError('Unauthorized', 401);
        }

        // Admins can always access
        if (isset($user->role) && $user->role === Roles::ADMIN) {
            return;
        }

        // Regular users can only access their own data
        if (!isset($user->id) || (int)$user->id !== (int)$resourceUserId) {
            self::handleError('Access denied: cannot access other user data', 403);
        }
    }
}
