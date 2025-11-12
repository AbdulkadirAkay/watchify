<?php

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
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"name", "email", "password"},
         *             @OA\Property(property="name", type="string", example="John Doe", minLength=2, maxLength=100),
         *             @OA\Property(property="email", type="string", format="email", example="john@example.com", maxLength=100),
         *             @OA\Property(property="password", type="string", format="password", example="password123", minLength=6),
         *             @OA\Property(property="is_admin", type="integer", example=0, enum={0, 1})
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
         *             @OA\Property(property="is_admin", type="integer", example=0, enum={0, 1})
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
            $service = Flight::userService();
            $data = Flight::request()->data->getData();
            $password = $data['password'] ?? null;
            $result = $service->updatePassword($id, $password);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Post(
         *     path="/api/users/login",
         *     tags={"Users"},
         *     summary="User login",
         *     description="Authenticate user with email and password",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"email", "password"},
         *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
         *             @OA\Property(property="password", type="string", format="password", example="password123")
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Login successful",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", ref="#/components/schemas/User")
         *         )
         *     ),
         *     @OA\Response(response=401, description="Invalid credentials", @OA\JsonContent(ref="#/components/schemas/Error"))
         * )
         */
        Flight::route('POST /api/users/login', function() {
            $service = Flight::userService();
            $data = Flight::request()->data->getData();
            $email = $data['email'] ?? null;
            $password = $data['password'] ?? null;
            $result = $service->verifyPassword($email, $password);
            Flight::json($result, $result['success'] ? 200 : 401);
        });

        /**
         * @OA\Delete(
         *     path="/api/users/{id}",
         *     tags={"Users"},
         *     summary="Delete user",
         *     description="Delete a user by ID",
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
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01 12:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01 12:00:00")
 * )
 */
?>