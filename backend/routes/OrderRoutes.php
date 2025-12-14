<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../data/Roles.php';

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="Order management endpoints"
 * )
 */
class OrderRoutes {
    /**
     * Register all order routes
     */
    public function register() {
        /**
         * @OA\Get(
         *     path="/api/orders",
         *     tags={"Orders"},
         *     summary="Get all orders with user info",
         *     description="Retrieve all orders with associated user information",
         *     security={{"ApiKey":{}}},
         *     @OA\Response(
         *         response=200,
         *         description="List of orders with user details",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/OrderWithUser"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/orders', function() {
            // Only admins can see all orders
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::orderService();
            $result = $service->getOrdersWithUserInfo();
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/orders/{id}",
         *     tags={"Orders"},
         *     summary="Get order by ID",
         *     description="Retrieve a specific order by ID",
         *     security={{"ApiKey":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Order ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Order details",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", ref="#/components/schemas/Order")
         *         )
         *     ),
         *     @OA\Response(response=404, description="Order not found", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/orders/@id', function($id) {
            $service = Flight::orderService();
            $result = $service->getById($id);

            if ($result['success'] && isset($result['data']['user_id'])) {
                // Admin can see any order, user only their own
                AuthMiddleware::authorizeCurrentUserOrAdmin($result['data']['user_id']);
            }

            Flight::json($result, $result['success'] ? 200 : ($result['message'] === 'Record not found' ? 404 : 400));
        });

        /**
         * @OA\Get(
         *     path="/api/orders/user/{user_id}",
         *     tags={"Orders"},
         *     summary="Get orders by user ID",
         *     description="Retrieve all orders for a specific user",
         *     security={{"ApiKey":{}}},
         *     @OA\Parameter(
         *         name="user_id",
         *         in="path",
         *         required=true,
         *         description="User ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="List of user orders",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/orders/user/@user_id', function($user_id) {
            // Admin can view any user's orders; regular users only their own
            AuthMiddleware::authorizeCurrentUserOrAdmin($user_id);

            $service = Flight::orderService();
            $result = $service->getByUserId($user_id);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/orders/status/{status}",
         *     tags={"Orders"},
         *     summary="Get orders by status",
         *     description="Retrieve all orders with a specific status",
         *     security={{"ApiKey":{}}},
         *     @OA\Parameter(
         *         name="status",
         *         in="path",
         *         required=true,
         *         description="Order status",
         *         @OA\Schema(type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"})
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="List of orders",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/orders/status/@status', function($status) {
            // Only admins should filter orders by status globally
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::orderService();
            $result = $service->getByStatus($status);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/orders/date-range",
         *     tags={"Orders"},
         *     summary="Get orders by date range",
         *     description="Retrieve orders within a specific date range",
         *     security={{"ApiKey":{}}},
         *     @OA\Parameter(
         *         name="start_date",
         *         in="query",
         *         required=true,
         *         description="Start date (YYYY-MM-DD)",
         *         @OA\Schema(type="string", format="date")
         *     ),
         *     @OA\Parameter(
         *         name="end_date",
         *         in="query",
         *         required=true,
         *         description="End date (YYYY-MM-DD)",
         *         @OA\Schema(type="string", format="date")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="List of orders",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/orders/date-range', function() {
            // Only admins can query arbitrary date ranges
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::orderService();
            $start_date = Flight::request()->query['start_date'] ?? null;
            $end_date = Flight::request()->query['end_date'] ?? null;
            $result = $service->getOrdersByDateRange($start_date, $end_date);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Post(
         *     path="/api/orders",
         *     tags={"Orders"},
         *     summary="Create a new order",
         *     description="Create a new order with products. Automatically decreases product quantities.",
         *     security={{"ApiKey":{}}},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"user_id", "total_price", "shipping_cost", "payment_method", "address", "status", "products"},
         *             @OA\Property(property="user_id", type="integer", example=1),
         *             @OA\Property(property="total_price", type="number", format="decimal", example=299.99),
         *             @OA\Property(property="shipping_cost", type="number", format="decimal", example=10.00, minimum=0),
         *             @OA\Property(property="payment_method", type="string", enum={"credit_card", "debit_card", "paypal", "cash_on_delivery", "bank_transfer"}, example="credit_card"),
         *             @OA\Property(property="address", type="string", example="123 Main St, City, Country", minLength=10),
         *             @OA\Property(property="status", type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"}, example="pending"),
         *             @OA\Property(
         *                 property="products",
         *                 type="array",
         *                 @OA\Items(
         *                     type="object",
         *                     required={"product_id", "quantity", "unit_price"},
         *                     @OA\Property(property="product_id", type="integer", example=1),
         *                     @OA\Property(property="quantity", type="integer", example=2, minimum=1),
         *                     @OA\Property(property="unit_price", type="number", format="decimal", example=149.99)
         *                 )
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="Order created successfully",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="message", type="string", example="Order created successfully"),
         *             @OA\Property(property="data", type="object", @OA\Property(property="id", type="integer", example=1))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Validation error or insufficient stock", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('POST /api/orders', function() {
            $service = Flight::orderService();
            $data = Flight::request()->data->getData();

            // Regular users can only create orders for themselves; admins can override user_id
            $currentUser = Flight::get('user');
            if ($currentUser && isset($currentUser->role) && $currentUser->role === Roles::USER) {
                $data['user_id'] = $currentUser->id ?? null;
            }

            $result = $service->create($data);
            Flight::json($result, $result['success'] ? 201 : 400);
        });

        /**
         * @OA\Put(
         *     path="/api/orders/{id}",
         *     tags={"Orders"},
         *     summary="Update order",
         *     description="Update order information",
         *     security={{"ApiKey":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Order ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             @OA\Property(property="total_price", type="number", format="decimal", example=349.99),
         *             @OA\Property(property="shipping_cost", type="number", format="decimal", example=15.00, minimum=0),
         *             @OA\Property(property="payment_method", type="string", enum={"credit_card", "debit_card", "paypal", "cash_on_delivery", "bank_transfer"}, example="paypal"),
         *             @OA\Property(property="address", type="string", example="456 New St, City, Country", minLength=10),
         *             @OA\Property(property="status", type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"}, example="processing")
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Order updated successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Success")
         *     ),
         *     @OA\Response(response=400, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=404, description="Order not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('PUT /api/orders/@id', function($id) {
            // Only admins can update orders
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::orderService();
            $data = Flight::request()->data->getData();
            $result = $service->update($id, $data);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Patch(
         *     path="/api/orders/{id}/status",
         *     tags={"Orders"},
         *     summary="Update order status",
         *     description="Update the status of an order",
         *     security={{"ApiKey":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Order ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"status"},
         *             @OA\Property(property="status", type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"}, example="shipped")
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Order status updated successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Success")
         *     ),
         *     @OA\Response(response=400, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=404, description="Order not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('PATCH /api/orders/@id/status', function($id) {
            // Only admins can change order status
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::orderService();
            $data = Flight::request()->data->getData();
            $status = $data['status'] ?? null;
            $result = $service->updateStatus($id, $status);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Delete(
         *     path="/api/orders/{id}",
         *     tags={"Orders"},
         *     summary="Delete order",
         *     description="Delete an order by ID",
         *     security={{"ApiKey":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Order ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Order deleted successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Success")
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=404, description="Order not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('DELETE /api/orders/@id', function($id) {
            // Only admins can delete orders
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::orderService();
            $result = $service->delete($id);
            Flight::json($result, $result['success'] ? 200 : 400);
        });
    }
}

/**
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="total_price", type="number", format="decimal", example=299.99),
 *     @OA\Property(property="shipping_cost", type="number", format="decimal", example=10.00),
 *     @OA\Property(property="payment_method", type="string", example="credit_card", enum={"credit_card", "debit_card", "paypal", "cash_on_delivery", "bank_transfer"}),
 *     @OA\Property(property="address", type="string", example="123 Main St, City, Country"),
 *     @OA\Property(property="status", type="string", example="pending", enum={"pending", "processing", "shipped", "delivered", "cancelled"}),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01 12:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01 12:00:00")
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrderWithUser",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Order"),
 *         @OA\Schema(
 *             @OA\Property(property="user_name", type="string", example="John Doe"),
 *             @OA\Property(property="user_email", type="string", format="email", example="john@example.com")
 *         )
 *     }
 * )
 */
?>