<?php

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="Category management endpoints"
 * )
 */
class CategoryRoutes {
    /**
     * Register all category routes
     */
    public function register() {
        /**
         * @OA\Get(
         *     path="/api/categories",
         *     tags={"Categories"},
         *     summary="Get all categories",
         *     description="Retrieve a list of all categories",
         *     @OA\Response(
         *         response=200,
         *         description="List of categories",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Category"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/categories', function() {
            $service = Flight::categoryService();
            $result = $service->getAll();
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/categories/with-count",
         *     tags={"Categories"},
         *     summary="Get categories with product count",
         *     description="Retrieve all categories with the number of products in each category",
         *     @OA\Response(
         *         response=200,
         *         description="List of categories with product counts",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CategoryWithCount"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/categories/with-count', function() {
            $service = Flight::categoryService();
            $result = $service->getWithProductCount();
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/categories/{id}",
         *     tags={"Categories"},
         *     summary="Get category by ID",
         *     description="Retrieve a specific category by ID",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Category ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Category details",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", ref="#/components/schemas/Category")
         *         )
         *     ),
         *     @OA\Response(response=404, description="Category not found", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/categories/@id', function($id) {
            $service = Flight::categoryService();
            $result = $service->getById($id);
            Flight::json($result, $result['success'] ? 200 : ($result['message'] === 'Record not found' ? 404 : 400));
        });

        /**
         * @OA\Get(
         *     path="/api/categories/name/{name}",
         *     tags={"Categories"},
         *     summary="Get category by name",
         *     description="Retrieve a category by its name",
         *     @OA\Parameter(
         *         name="name",
         *         in="path",
         *         required=true,
         *         description="Category name",
         *         @OA\Schema(type="string")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Category details",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", ref="#/components/schemas/Category")
         *         )
         *     ),
         *     @OA\Response(response=404, description="Category not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/categories/name/@name', function($name) {
            $service = Flight::categoryService();
            $result = $service->getByName($name);
            Flight::json($result, $result['success'] ? 200 : 404);
        });

        /**
         * @OA\Post(
         *     path="/api/categories",
         *     tags={"Categories"},
         *     summary="Create a new category",
         *     description="Add a new product category",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"name"},
         *             @OA\Property(property="name", type="string", example="Men", minLength=2, maxLength=45)
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="Category created successfully",
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
        Flight::route('POST /api/categories', function() {
            $service = Flight::categoryService();
            $data = Flight::request()->data->getData();
            $result = $service->create($data);
            Flight::json($result, $result['success'] ? 201 : 400);
        });

        /**
         * @OA\Put(
         *     path="/api/categories/{id}",
         *     tags={"Categories"},
         *     summary="Update category",
         *     description="Update category information",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Category ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             @OA\Property(property="name", type="string", example="Men's Watches", minLength=2, maxLength=45)
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Category updated successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Success")
         *     ),
         *     @OA\Response(response=400, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=404, description="Category not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('PUT /api/categories/@id', function($id) {
            $service = Flight::categoryService();
            $data = Flight::request()->data->getData();
            $result = $service->update($id, $data);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Delete(
         *     path="/api/categories/{id}",
         *     tags={"Categories"},
         *     summary="Delete category",
         *     description="Delete a category by ID. Cannot delete if category has associated products.",
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="Category ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Category deleted successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Success")
         *     ),
         *     @OA\Response(response=400, description="Bad request or category has products", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=404, description="Category not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('DELETE /api/categories/@id', function($id) {
            $service = Flight::categoryService();
            $result = $service->delete($id);
            Flight::json($result, $result['success'] ? 200 : 400);
        });
    }
}

/**
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Men"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01 12:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01 12:00:00")
 * )
 */

/**
 * @OA\Schema(
 *     schema="CategoryWithCount",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Category"),
 *         @OA\Schema(
 *             @OA\Property(property="product_count", type="integer", example=25)
 *         )
 *     }
 * )
 */
?>