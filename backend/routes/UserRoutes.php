<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../data/Roles.php';

/**
 * @OA\Tag(
 *     name="Users",
 *     description="User management endpoints"
 * )
 */
class UserRoutes {
    /**
     * Register all user routes
     */
    public function register() {
        /**
         * @OA\Get(
         *     path="/api/users",
         *     tags={"Users"},
         *     summary="Get all users",
         *     description="Retrieve a list of all users",
         *     security={{"ApiKey":{}}},
         *     @OA\Response(
         *         response=200,
         *         description="List of users",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User"))
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/users', function() {
            // Only admins can list all users
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::userService();
            $result = $service->getAll();
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Get(
         *     path="/api/users/{id}",
         *     tags={"Users"},
         *     summary="Get user by ID",
         *     description="Retrieve a specific user by their ID",
         *     security={{"ApiKey":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="User ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="User details",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", ref="#/components/schemas/User")
         *         )
         *     ),
         *     @OA\Response(response=404, description="User not found", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/users/@id', function($id) {
            // Admin can access any user; regular users only their own record
            AuthMiddleware::authorizeCurrentUserOrAdmin($id);

            $service = Flight::userService();
            $result = $service->getById($id);
            Flight::json($result, $result['success'] ? 200 : ($result['message'] === 'Record not found' ? 404 : 400));
        });

        /**
         * @OA\Get(
         *     path="/api/users/email/{email}",
         *     tags={"Users"},
         *     summary="Get user by email",
         *     description="Retrieve a user by their email address",
         *     security={{"ApiKey":{}}},
         *     @OA\Parameter(
         *         name="email",
         *         in="path",
         *         required=true,
         *         description="User email address",
         *         @OA\Schema(type="string", format="email")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="User details",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", ref="#/components/schemas/User")
         *         )
         *     ),
         *     @OA\Response(response=404, description="User not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('GET /api/users/email/@email', function($email) {
            // Only admins should look up users by arbitrary email
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::userService();
            $result = $service->getByEmail($email);
            Flight::json($result, $result['success'] ? 200 : 404);
        });

        /**
         * @OA\Post(
         *     path="/api/users",
         *     tags={"Users"},
         *     summary="Create a new user",
         *     description="Register a new user account",
         *     security={{"ApiKey":{}}},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"name", "email", "password"},
         *             @OA\Property(property="name", type="string", example="John Doe", minLength=2, maxLength=100),
         *             @OA\Property(property="email", type="string", format="email", example="john@example.com", maxLength=100),
         *             @OA\Property(property="password", type="string", format="password", example="password123", minLength=6),
         *             @OA\Property(property="is_admin", type="integer", example=0, enum={0, 1}),
         *             @OA\Property(property="phone", type="string", example="+1 555-123-4567", maxLength=50, nullable=true),
         *             @OA\Property(property="address", type="string", example="123 Main St", maxLength=255, nullable=true),
         *             @OA\Property(property="city", type="string", example="New York", maxLength=100, nullable=true),
         *             @OA\Property(property="zip_code", type="string", example="10001", maxLength=20, nullable=true)
         *         )
         *     ),
         *     @OA\Response(
         *         response=201,
         *         description="User created successfully",
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
        Flight::route('POST /api/users', function() {
            // Only admins can create users via this endpoint (self-register uses /auth/register)
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::userService();
            $data = Flight::request()->data->getData();
            $result = $service->create($data);
            Flight::json($result, $result['success'] ? 201 : 400);
        });

        /**
         * @OA\Put(
         *     path="/api/users/{id}",
         *     tags={"Users"},
         *     summary="Update user",
         *     description="Update user information",
         *     security={{"ApiKey":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="User ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             @OA\Property(property="name", type="string", example="John Doe Updated", minLength=2, maxLength=100),
         *             @OA\Property(property="email", type="string", format="email", example="john.updated@example.com", maxLength=100),
         *             @OA\Property(property="is_admin", type="integer", example=0, enum={0, 1}),
         *             @OA\Property(property="phone", type="string", example="+1 555-123-4567", maxLength=50, nullable=true),
         *             @OA\Property(property="address", type="string", example="123 Main St", maxLength=255, nullable=true),
         *             @OA\Property(property="city", type="string", example="New York", maxLength=100, nullable=true),
         *             @OA\Property(property="zip_code", type="string", example="10001", maxLength=20, nullable=true)
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="User updated successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Success")
         *     ),
         *     @OA\Response(response=400, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=404, description="User not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('PUT /api/users/@id', function($id) {
            // Admin can update any user; regular users only themselves
            AuthMiddleware::authorizeCurrentUserOrAdmin($id);

            $service = Flight::userService();
            $data = Flight::request()->data->getData();
            $result = $service->update($id, $data);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Patch(
         *     path="/api/users/{id}/password",
         *     tags={"Users"},
         *     summary="Update user password",
         *     description="Change user password",
         *     security={{"ApiKey":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="User ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"password"},
         *             @OA\Property(property="password", type="string", format="password", example="newpassword123", minLength=6)
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Password updated successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Success")
         *     ),
         *     @OA\Response(response=400, description="Validation error", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=404, description="User not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('PATCH /api/users/@id/password', function($id) {
            // Admin can change any password; regular users only their own
            AuthMiddleware::authorizeCurrentUserOrAdmin($id);

            $service = Flight::userService();
            $data = Flight::request()->data->getData();
            $password = $data['password'] ?? null;
            $result = $service->updatePassword($id, $password);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Delete(
         *     path="/api/users/{id}",
         *     tags={"Users"},
         *     summary="Delete user",
         *     description="Delete a user by ID",
         *     security={{"ApiKey":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         description="User ID",
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="User deleted successfully",
         *         @OA\JsonContent(ref="#/components/schemas/Success")
         *     ),
         *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error")),
         *     @OA\Response(response=404, description="User not found", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('DELETE /api/users/@id', function($id) {
            // Only admins can delete users
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::userService();
            $result = $service->delete($id);
            Flight::json($result, $result['success'] ? 200 : 400);
        });
    }
}

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="is_admin", type="integer", example=0, enum={0, 1}),
 *     @OA\Property(property="phone", type="string", example="+1 555-123-4567", maxLength=50, nullable=true),
 *     @OA\Property(property="address", type="string", example="123 Main St", maxLength=255, nullable=true),
 *     @OA\Property(property="city", type="string", example="New York", maxLength=100, nullable=true),
 *     @OA\Property(property="zip_code", type="string", example="10001", maxLength=20, nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01 12:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01 12:00:00")
 * )
 */
?>