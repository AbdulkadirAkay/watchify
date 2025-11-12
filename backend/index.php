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
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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
require_once __DIR__ . '/services/OrderProductService.php';

Flight::register('userService', 'UserService');
Flight::register('productService', 'ProductService');
Flight::register('categoryService', 'CategoryService');
Flight::register('orderService', 'OrderService');
Flight::register('orderProductService', 'OrderProductService');

// Register routes
require_once __DIR__ . '/routes/UserRoutes.php';
require_once __DIR__ . '/routes/ProductRoutes.php';
require_once __DIR__ . '/routes/CategoryRoutes.php';
require_once __DIR__ . '/routes/OrderRoutes.php';
require_once __DIR__ . '/routes/OrderProductRoutes.php';

// Initialize route classes
$userRoutes = new UserRoutes();
$productRoutes = new ProductRoutes();
$categoryRoutes = new CategoryRoutes();
$orderRoutes = new OrderRoutes();
$orderProductRoutes = new OrderProductRoutes();

// Register all routes
$userRoutes->register();
$productRoutes->register();
$categoryRoutes->register();
$orderRoutes->register();
$orderProductRoutes->register();

// Root route - API information
Flight::route('/', function() {
    Flight::json([
        'success' => true,
        'message' => 'Watchify REST API',
        'version' => '1.0.0',
        'endpoints' => [
            'users' => '/api/users',
            'products' => '/api/products',
            'categories' => '/api/categories',
            'orders' => '/api/orders',
            'order-products' => '/api/order-products'
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