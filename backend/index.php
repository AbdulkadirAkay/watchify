<?php
require 'vendor/autoload.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON content type for API responses
header('Content-Type: application/json');

// Enable CORS (Cross-Origin Resource Sharing) if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authentication');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Register services using Flight::register()
require_once __DIR__ . '/services/UserService.php';
require_once __DIR__ . '/services/ProductService.php';
require_once __DIR__ . '/services/CategoryService.php';
require_once __DIR__ . '/services/OrderService.php';
require_once __DIR__ . '/services/AuthService.php';
require_once __DIR__ . '/services/StatisticsService.php';
require_once __DIR__ . '/services/FileUploadService.php';

Flight::register('userService', 'UserService');
Flight::register('productService', 'ProductService');
Flight::register('categoryService', 'CategoryService');
Flight::register('orderService', 'OrderService');
Flight::register('authService', 'AuthService');
Flight::register('statisticsService', 'StatisticsService');
Flight::register('fileUploadService', 'FileUploadService');

// Register routes
require_once __DIR__ . '/routes/UserRoutes.php';
require_once __DIR__ . '/routes/ProductRoutes.php';
require_once __DIR__ . '/routes/CategoryRoutes.php';
require_once __DIR__ . '/routes/OrderRoutes.php';
require_once __DIR__ . '/routes/AuthRoutes.php';
require_once __DIR__ . '/routes/StatisticsRoutes.php';
require_once __DIR__ . '/routes/UploadRoutes.php';

// Load middleware
require_once __DIR__ . '/middleware/AuthMiddleware.php';

// Apply authentication middleware to all requests (except defined public URLs)
// Using Flight::before so middleware runs before route matching
Flight::before('start', function() {
    AuthMiddleware::handle();
});

// Initialize route classes
$userRoutes = new UserRoutes();
$productRoutes = new ProductRoutes();
$categoryRoutes = new CategoryRoutes();
$orderRoutes = new OrderRoutes();
$authRoutes = new AuthRoutes();
$statisticsRoutes = new StatisticsRoutes();
$uploadRoutes = new UploadRoutes();

// Register all routes (middleware route above will run first, then these routes will be matched)
$authRoutes->register(); // Register auth routes first
$userRoutes->register();
$productRoutes->register();
$categoryRoutes->register();
$orderRoutes->register();
$statisticsRoutes->register();
$uploadRoutes->register();

// Root route - API information
Flight::route('/', function() {
    Flight::json([
        'success' => true,
        'message' => 'Watchify REST API',
        'version' => '1.0.0',
        'endpoints' => [
            'auth' => [
                'register' => '/auth/register',
                'login' => '/auth/login'
            ],
            'users' => '/api/users',
            'products' => '/api/products',
            'categories' => '/api/categories',
            'orders' => '/api/orders',
            'statistics' => '/api/statistics'
        ]
    ]);
});




// 404 handler
Flight::map('notFound', function() {
    Flight::json([
        'success' => false,
        'message' => 'Endpoint not found'
    ], 404);
});

// Error handler
Flight::map('error', function(Exception $ex) {
    Flight::json([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $ex->getMessage()
    ], 500);
});

// Start FlightPHP
Flight::start();
?>