<?php

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentication endpoints"
 * )
 */
class AuthRoutes {
    /**
     * Register all authentication routes
     */
    public function register() {
        /**
         * @OA\Post(
         *     path="/auth/register",
         *     tags={"Auth"},
         *     summary="Register new user",
         *     description="Add a new user to the database",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"email", "password", "name"},
         *             @OA\Property(property="name", type="string", example="John Doe", description="User name"),
         *             @OA\Property(property="email", type="string", example="demo@gmail.com", description="User email"),
         *             @OA\Property(property="password", type="string", example="some_password", description="User password"),
         *             @OA\Property(property="phone", type="string", example="+1 555-123-4567", description="User phone number", nullable=true),
         *             @OA\Property(property="address", type="string", example="123 Main St", description="User address", nullable=true),
         *             @OA\Property(property="city", type="string", example="New York", description="User city", nullable=true),
         *             @OA\Property(property="zip_code", type="string", example="10001", description="User ZIP/postal code", nullable=true)
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="User has been registered successfully",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="message", type="string", example="User registered successfully"),
         *             @OA\Property(property="data", ref="#/components/schemas/User")
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Internal server error",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="error", type="string")
         *         )
         *     )
         * )
         */
        Flight::route('POST /auth/register', function() {
            $data = Flight::request()->data->getData();
            $response = Flight::authService()->register($data);

            if ($response['success']) {
                Flight::json([
                    'message' => 'User registered successfully',
                    'data' => $response['data']
                ], 200);
            } else {
                Flight::json($response, 400);
            }
        });

        /**
         * @OA\Post(
         *     path="/auth/login",
         *     tags={"Auth"},
         *     summary="Login to system using email and password",
         *     description="Authenticate user and receive JWT token",
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"email", "password"},
         *             @OA\Property(property="email", type="string", example="demo@gmail.com", description="User email address"),
         *             @OA\Property(property="password", type="string", example="some_password", description="User password")
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Login successful",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="message", type="string", example="User logged in successfully"),
         *             @OA\Property(
         *                 property="data",
         *                 type="object",
         *                 @OA\Property(property="id", type="integer", example=1),
         *                 @OA\Property(property="name", type="string", example="John Doe"),
         *                 @OA\Property(property="email", type="string", example="demo@gmail.com"),
         *                 @OA\Property(property="phone", type="string", example="+1 555-123-4567", nullable=true),
         *                 @OA\Property(property="address", type="string", example="123 Main St", nullable=true),
         *                 @OA\Property(property="city", type="string", example="New York", nullable=true),
         *                 @OA\Property(property="zip_code", type="string", example="10001", nullable=true),
         *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc...")
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=500,
         *         description="Invalid credentials",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="error", type="string")
         *         )
         *     )
         * )
         */
        Flight::route('POST /auth/login', function() {
            $data = Flight::request()->data->getData();
            $response = Flight::authService()->login($data);

            if ($response['success']) {
                Flight::json([
                    'message' => 'User logged in successfully',
                    'data' => $response['data']
                ], 200);
            } else {
                // Invalid credentials or validation errors -> 401 with JSON
                Flight::json($response, 401);
            }
        });
    }
}
