<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../data/Roles.php';

/**
 * @OA\Tag(
 *     name="Statistics",
 *     description="Dashboard statistics endpoints"
 * )
 */
class StatisticsRoutes {
    /**
     * Register all statistics routes
     */
    public function register() {
        /**
         * @OA\Get(
         *     path="/api/statistics/dashboard",
         *     tags={"Statistics"},
         *     summary="Get dashboard statistics",
         *     description="Retrieve dashboard statistics including total products, orders, revenue, and customers",
         *     security={{"ApiKey":{}}},
         *     @OA\Response(
         *         response=200,
         *         description="Dashboard statistics",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(
         *                 property="data",
         *                 type="object",
         *                 @OA\Property(property="total_products", type="integer", example=24),
         *                 @OA\Property(property="total_orders", type="integer", example=156),
         *                 @OA\Property(property="total_revenue", type="number", example=12450.50),
         *                 @OA\Property(property="total_customers", type="integer", example=89),
         *                 @OA\Property(
         *                     property="recent_orders",
         *                     type="array",
         *                     @OA\Items(
         *                         type="object",
         *                         @OA\Property(property="id", type="integer"),
         *                         @OA\Property(property="user_name", type="string"),
         *                         @OA\Property(property="user_email", type="string"),
         *                         @OA\Property(property="total_price", type="number"),
         *                         @OA\Property(property="status", type="string"),
         *                         @OA\Property(property="created_at", type="string")
         *                     )
         *                 )
         *             )
         *         )
         *     ),
         *     @OA\Response(response=401, description="Unauthorized"),
         *     @OA\Response(response=403, description="Forbidden - Admin access required")
         * )
         */
        Flight::route('GET /api/statistics/dashboard', function() {
            // Only admins can view statistics
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::statisticsService();
            $result = $service->getDashboardStats();
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/statistics/orders/status",
         *     tags={"Statistics"},
         *     summary="Get order statistics by status",
         *     description="Retrieve order counts and totals grouped by status",
         *     security={{"ApiKey":{}}},
         *     @OA\Response(
         *         response=200,
         *         description="Order statistics by status",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(
         *                 property="data",
         *                 type="array",
         *                 @OA\Items(
         *                     type="object",
         *                     @OA\Property(property="status", type="string", example="pending"),
         *                     @OA\Property(property="count", type="integer", example=5),
         *                     @OA\Property(property="total_amount", type="number", example=1250.00)
         *                 )
         *             )
         *         )
         *     ),
         *     @OA\Response(response=401, description="Unauthorized"),
         *     @OA\Response(response=403, description="Forbidden - Admin access required")
         * )
         */
        Flight::route('GET /api/statistics/orders/status', function() {
            // Only admins can view statistics
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::statisticsService();
            $result = $service->getOrderStatsByStatus();
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/statistics/products/category",
         *     tags={"Statistics"},
         *     summary="Get product statistics by category",
         *     description="Retrieve product counts and stock totals grouped by category",
         *     security={{"ApiKey":{}}},
         *     @OA\Response(
         *         response=200,
         *         description="Product statistics by category",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(
         *                 property="data",
         *                 type="array",
         *                 @OA\Items(
         *                     type="object",
         *                     @OA\Property(property="category", type="string", example="Men"),
         *                     @OA\Property(property="product_count", type="integer", example=15),
         *                     @OA\Property(property="total_stock", type="integer", example=150)
         *                 )
         *             )
         *         )
         *     ),
         *     @OA\Response(response=401, description="Unauthorized"),
         *     @OA\Response(response=403, description="Forbidden - Admin access required")
         * )
         */
        Flight::route('GET /api/statistics/products/category', function() {
            // Only admins can view statistics
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::statisticsService();
            $result = $service->getProductStatsByCategory();
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/statistics/sales",
         *     tags={"Statistics"},
         *     summary="Get sales statistics",
         *     description="Retrieve sales statistics for a date range (defaults to current month)",
         *     security={{"ApiKey":{}}},
         *     @OA\Parameter(
         *         name="start_date",
         *         in="query",
         *         description="Start date (YYYY-MM-DD)",
         *         @OA\Schema(type="string", format="date")
         *     ),
         *     @OA\Parameter(
         *         name="end_date",
         *         in="query",
         *         description="End date (YYYY-MM-DD)",
         *         @OA\Schema(type="string", format="date")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Sales statistics",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(
         *                 property="data",
         *                 type="object",
         *                 @OA\Property(property="total_orders", type="integer", example=50),
         *                 @OA\Property(property="total_revenue", type="number", example=5250.00),
         *                 @OA\Property(property="average_order_value", type="number", example=105.00),
         *                 @OA\Property(property="total_shipping", type="number", example=250.00)
         *             )
         *         )
         *     ),
         *     @OA\Response(response=401, description="Unauthorized"),
         *     @OA\Response(response=403, description="Forbidden - Admin access required")
         * )
         */
        Flight::route('GET /api/statistics/sales', function() {
            // Only admins can view statistics
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $request = Flight::request();
            $start_date = $request->query->start_date ?? null;
            $end_date = $request->query->end_date ?? null;

            $service = Flight::statisticsService();
            $result = $service->getSalesStats($start_date, $end_date);
            Flight::json($result, $result['success'] ? 200 : 400);
        });
    }
}
?>


