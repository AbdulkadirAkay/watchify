<?php

/**
 * @OA\Tag(
 *     name="Order Products",
 *     description="Order product relationship management endpoints"
 * )
 */
class OrderProductRoutes {
    /**
     * Register all order product routes
     */
    public function register() {
        /**
         * @OA\Get(
         *     path="/api/order-products",
         *     tags={"Order Products"},
         *     summary="Get all order products",
         *     description="Retrieve all order-product relationships",
         *     @OA\Response(
         *         response=200,
         *         description="List of order products",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/OrderProduct"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/order-products', function() {
            $service = Flight::orderProductService();
            $result = $service->getAll();
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/order-products/{id}",
         *     tags={"Order Products"},
         *     summary="Get order product by ID",
         *     description="Retrieve a specific order-product relationship by ID",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Order Product ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Order product details",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", ref="#/components/schemas/OrderProduct")
         *         )
         *     ),
         *     @OA\Response(response=404, description="Order product not found", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/order-products/@id', function($id) {
            $service = Flight::orderProductService();
            $result = $service->getById($id);
            Flight::json($result, $result['success'] ? 200 : ($result['message'] === 'Record not found' ? 404 : 400));
        });

        /**
         * @OA\Get(
         *     path="/api/order-products/order/{order_id}",
         *     tags={"Order Products"},
         *     summary="Get order products with details",
         *     description="Retrieve all products in an order with product details",
         *     @OA\Parameter(
         *         name="order_id",
         *         in="path",
         *         required=true,
         *         description="Order ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="List of order products with details",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/OrderProductWithDetails"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/order-products/order/@order_id', function($order_id) {
            $service = Flight::orderProductService();
            $result = $service->getOrderProductsWithDetails($order_id);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/order-products/order/{order_id}/total",
         *     tags={"Order Products"},
         *     summary="Get order total",
         *     description="Calculate the total price of all products in an order",
         *     @OA\Parameter(
         *         name="order_id",
         *         in="path",
         *         required=true,
         *         description="Order ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Order total",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="object",
         *                 @OA\Property(property="order_id", type="integer", example=1),
         *                 @OA\Property(property="total", type="number", format="decimal", example=299.99)
         *             )
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/order-products/order/@order_id/total', function($order_id) {
            $service = Flight::orderProductService();
            $result = $service->getOrderTotal($order_id);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Post(
         *     path="/api/order-products",
         *     tags={"Order Products"},
         *     summary="Create order product",
         *     description="Add a product to an order",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"order_id", "product_id", "quantity", "unit_price"},
         *             @OA\Property(property="order_id", type="integer", example=1),
         *             @OA\Property(property="product_id", type="integer", example=1),
         *             @OA\Property(property="quantity", type="integer", example=2, minimum=1),
         *             @OA\Property(property="unit_price", type="number", format="decimal", example=149.99)
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="Order product created successfully",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="message", type="string", example="Record created successfully"),
         *             @OA\Property(property="data", type="object", @OA\Property(property="id", type="integer", example=1))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('POST /api/order-products', function() {
            $service = Flight::orderProductService();
            $data = Flight::request()->data->getData();
            $result = $service->create($data);
            Flight::json($result, $result['success'] ? 201 : 400);
        });

        /**
         * @OA\Post(
         *     path="/api/order-products/add",
         *     tags={"Order Products"},
         *     summary="Add product to order",
         *     description="Convenience endpoint to add a product to an existing order",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"order_id", "product_id", "quantity", "unit_price"},
         *             @OA\Property(property="order_id", type="integer", example=1),
         *             @OA\Property(property="product_id", type="integer", example=1),
         *             @OA\Property(property="quantity", type="integer", example=2, minimum=1),
         *             @OA\Property(property="unit_price", type="number", format="decimal", example=149.99)
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="Product added to order successfully",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="message", type="string", example="Product added to order successfully"),
         *             @OA\Property(property="data", type="object", @OA\Property(property="id", type="integer", example=1))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('POST /api/order-products/add', function() {
            $service = Flight::orderProductService();
            $data = Flight::request()->data->getData();
            $order_id = $data['order_id'] ?? null;
            $product_id = $data['product_id'] ?? null;
            $quantity = $data['quantity'] ?? null;
            $unit_price = $data['unit_price'] ?? null;
            $result = $service->addProductToOrder($order_id, $product_id, $quantity, $unit_price);
            Flight::json($result, $result['success'] ? 201 : 400);
        });

        /**
         * @OA\Delete(
         *     path="/api/order-products/{id}",
         *     tags={"Order Products"},
         *     summary="Delete order product",
         *     description="Remove a product from an order",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Order Product ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Order product deleted successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Success")
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=404, description="Order product not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('DELETE /api/order-products/@id', function($id) {
            $service = Flight::orderProductService();
            $result = $service->delete($id);
            Flight::json($result, $result['success'] ? 200 : 400);
        });
    }
}

/**
 * @OA\Schema(
 *     schema="OrderProduct",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=1),
 *     @OA\Property(property="product_id", type="integer", example=1),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="unit_price", type="number", format="decimal", example=149.99),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01 12:00:00")
 * )
 */

/**
 * @OA\Schema(
 *     schema="OrderProductWithDetails",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/OrderProduct"),
 *         @OA\Schema(
 *             @OA\Property(property="product_name", type="string", example="Classic Watch"),
 *             @OA\Property(property="brand", type="string", example="Rolex"),
 *             @OA\Property(property="image_url", type="string", format="uri", example="https://example.com/watch.jpg")
 *         )
 *     }
 * )
 */
?>