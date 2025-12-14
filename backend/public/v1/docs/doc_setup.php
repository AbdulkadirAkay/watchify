<?php

/**
 * @OA\Info(
 *     title="Watchify REST API",
 *     description="Complete REST API documentation for Watchify e-commerce platform. Manage users, products, categories, orders, and order products.",
 *     version="1.0.0",
 *     @OA\Contact(
 *         email="abdulkadir.akay@stu.ibu.edu.ba",
 *         name="Abdulkadir Akay"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 */

/**
 * @OA\Server(
 *      url="http://localhost/watchify/backend",
 *      description="Local development server"
 * )
 * @OA\Server(
 *      url="http://localhost/watchify/backend/api",
 *      description="API server"
 * )
 */

/**
 * @OA\SecurityScheme(
 *     securityScheme="ApiKey",
 *     type="apiKey",
 *     in="header",
 *     name="Authentication",
 *     description="API Key authentication"
 * )
 */

/**
 * Global security requirement so Swagger UI sends the header for all operations
 *
 * @OA\SecurityRequirement(
 *     name="ApiKey"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Error message"),
 *     @OA\Property(property="errors", type="object", description="Validation errors")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Success",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operation successful"),
 *     @OA\Property(property="data", type="object")
 * )
 */
