<?php

/**
 * @OA\Tag(
 *     name="Products",
 *     description="Product management endpoints"
 * )
 */
class ProductRoutes {
    /**
     * Register all product routes
     */
    public function register() {
        /**
         * @OA\Get(
         *     path="/api/products",
         *     tags={"Products"},
         *     summary="Get all products",
         *     description="Retrieve a list of all products",
         *     @OA\Response(
         *         response=200,
         *         description="List of products",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/products', function() {
            $service = Flight::productService();
            $result = $service->getAll();
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/products/{id}",
         *     tags={"Products"},
         *     summary="Get product by ID",
         *     description="Retrieve a specific product by ID",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Product ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Product details",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", ref="#/components/schemas/Product")
         *         )
         *     ),
         *     @OA\Response(response=404, description="Product not found", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/products/@id', function($id) {
            $service = Flight::productService();
            $result = $service->getById($id);
            Flight::json($result, $result['success'] ? 200 : ($result['message'] === 'Record not found' ? 404 : 400));
        });

        /**
         * @OA\Get(
         *     path="/api/products/category/{category_id}",
         *     tags={"Products"},
         *     summary="Get products by category",
         *     description="Retrieve all products in a specific category",
         *     @OA\Parameter(
         *         name="category_id",
         *         in="path",
         *         required=true,
         *         description="Category ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="List of products",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/products/category/@category_id', function($category_id) {
            $service = Flight::productService();
            $result = $service->getByCategory($category_id);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/products/brand/{brand}",
         *     tags={"Products"},
         *     summary="Get products by brand",
         *     description="Retrieve all products from a specific brand",
         *     @OA\Parameter(
         *         name="brand",
         *         in="path",
         *         required=true,
         *         description="Brand name",
         *         @OA\Schema(type="string")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="List of products",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/products/brand/@brand', function($brand) {
            $service = Flight::productService();
            $result = $service->getByBrand($brand);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/products/available",
         *     tags={"Products"},
         *     summary="Get available products",
         *     description="Retrieve all products with quantity > 0",
         *     @OA\Response(
         *         response=200,
         *         description="List of available products",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/products/available', function() {
            $service = Flight::productService();
            $result = $service->getAvailableProducts();
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/products/popular",
         *     tags={"Products"},
         *     summary="Get popular products",
         *     description="Retrieve most popular products based on order quantity",
         *     @OA\Parameter(
         *         name="limit",
         *         in="query",
         *         required=false,
         *         description="Number of products to return",
         *         @OA\Schema(type="integer", default=10, minimum=1)
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="List of popular products",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/products/popular', function() {
            $service = Flight::productService();
            $limit = Flight::request()->query['limit'] ?? 10;
            $result = $service->getPopularProducts($limit);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/products/new-arrivals",
         *     tags={"Products"},
         *     summary="Get new arrivals",
         *     description="Retrieve recently added products",
         *     @OA\Parameter(
         *         name="limit",
         *         in="query",
         *         required=false,
         *         description="Number of products to return",
         *         @OA\Schema(type="integer", default=10, minimum=1)
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="List of new arrival products",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/products/new-arrivals', function() {
            $service = Flight::productService();
            $limit = Flight::request()->query['limit'] ?? 10;
            $result = $service->getNewArrivals($limit);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Post(
         *     path="/api/products",
         *     tags={"Products"},
         *     summary="Create a new product",
         *     description="Add a new product to the catalog",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"name", "brand", "price", "quantity", "category_id", "image_url", "description"},
         *             @OA\Property(property="name", type="string", example="Classic Watch", maxLength=100),
         *             @OA\Property(property="brand", type="string", example="Rolex", maxLength=100),
         *             @OA\Property(property="price", type="number", format="decimal", example=299.99),
         *             @OA\Property(property="quantity", type="integer", example=50, minimum=0),
         *             @OA\Property(property="category_id", type="integer", example=1),
         *             @OA\Property(property="image_url", type="string", format="uri", example="https://example.com/watch.jpg", maxLength=255),
         *             @OA\Property(property="description", type="string", example="A classic timepiece", maxLength=500)
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="Product created successfully",
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
        Flight::route('POST /api/products', function() {
            $service = Flight::productService();
            $data = Flight::request()->data->getData();
            $result = $service->create($data);
            Flight::json($result, $result['success'] ? 201 : 400);
        });

        /**
         * @OA\Put(
         *     path="/api/products/{id}",
         *     tags={"Products"},
         *     summary="Update product",
         *     description="Update product information",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Product ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             @OA\Property(property="name", type="string", example="Classic Watch Updated", maxLength=100),
         *             @OA\Property(property="brand", type="string", example="Rolex", maxLength=100),
         *             @OA\Property(property="price", type="number", format="decimal", example=349.99),
         *             @OA\Property(property="quantity", type="integer", example=45, minimum=0),
         *             @OA\Property(property="category_id", type="integer", example=1),
         *             @OA\Property(property="image_url", type="string", format="uri", example="https://example.com/watch-updated.jpg", maxLength=255),
         *             @OA\Property(property="description", type="string", example="Updated description", maxLength=500)
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Product updated successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Success")
         *     ),
         *     @OA\Response(response=400, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=404, description="Product not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('PUT /api/products/@id', function($id) {
            $service = Flight::productService();
            $data = Flight::request()->data->getData();
            $result = $service->update($id, $data);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Patch(
         *     path="/api/products/{id}/quantity",
         *     tags={"Products"},
         *     summary="Update product quantity",
         *     description="Update the stock quantity of a product",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Product ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"quantity"},
         *             @OA\Property(property="quantity", type="integer", example=100, minimum=0)
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Quantity updated successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Success")
         *     ),
         *     @OA\Response(response=400, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=404, description="Product not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('PATCH /api/products/@id/quantity', function($id) {
            $service = Flight::productService();
            $data = Flight::request()->data->getData();
            $quantity = $data['quantity'] ?? null;
            $result = $service->updateQuantity($id, $quantity);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Delete(
         *     path="/api/products/{id}",
         *     tags={"Products"},
         *     summary="Delete product",
         *     description="Delete a product by ID",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Product ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Product deleted successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Success")
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=404, description="Product not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('DELETE /api/products/@id', function($id) {
            $service = Flight::productService();
            $result = $service->delete($id);
            Flight::json($result, $result['success'] ? 200 : 400);
        });
    }
}

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Classic Watch"),
 *     @OA\Property(property="brand", type="string", example="Rolex"),
 *     @OA\Property(property="price", type="number", format="decimal", example=299.99),
 *     @OA\Property(property="quantity", type="integer", example=50),
 *     @OA\Property(property="image_url", type="string", format="uri", example="https://example.com/watch.jpg"),
 *     @OA\Property(property="description", type="string", example="A classic timepiece"),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01 12:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01 12:00:00")
 * )
 */
?>