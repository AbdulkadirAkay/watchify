<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../data/Roles.php';

/**
 * @OA\Tag(
 *     name="Upload",
 *     description="File upload endpoints"
 * )
 */
class UploadRoutes {
    /**
     * Register all upload routes
     */
    public function register() {
        /**
         * @OA\Post(
         *     path="/api/upload/image",
         *     tags={"Upload"},
         *     summary="Upload an image file",
         *     description="Upload an image file and get the file path",
         *     security={{"ApiKey":{}}},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\MediaType(
         *             mediaType="multipart/form-data",
         *             @OA\Schema(
         *                 @OA\Property(
         *                     property="image",
         *                     description="Image file to upload",
         *                     type="string",
         *                     format="binary"
         *                 )
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="File uploaded successfully",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="message", type="string", example="File uploaded successfully"),
         *             @OA\Property(
         *                 property="data",
         *                 type="object",
         *                 @OA\Property(property="filename", type="string", example="product_abc123.jpg"),
         *                 @OA\Property(property="filepath", type="string", example="uploads/products/product_abc123.jpg"),
         *                 @OA\Property(property="url", type="string", example="uploads/products/product_abc123.jpg")
         *             )
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request - Invalid file"),
         *     @OA\Response(response=401, description="Unauthorized"),
         *     @OA\Response(response=403, description="Forbidden - Admin access required")
         * )
         */
        Flight::route('POST /api/upload/image', function() {
            // Only admins can upload images
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::fileUploadService();
            
            // Check if file was uploaded
            if (!isset($_FILES['image'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'No image file provided'
                ], 400);
                return;
            }

            $result = $service->uploadImage($_FILES['image']);
            Flight::json($result, $result['success'] ? 200 : 400);
        });

        /**
         * @OA\Delete(
         *     path="/api/upload/image",
         *     tags={"Upload"},
         *     summary="Delete an uploaded image",
         *     description="Delete an uploaded image file by filepath",
         *     security={{"ApiKey":{}}},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             @OA\Property(property="filepath", type="string", example="uploads/products/product_abc123.jpg")
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="File deleted successfully",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="message", type="string", example="File deleted successfully")
         *         )
         *     ),
         *     @OA\Response(response=400, description="Bad request - Invalid filepath"),
         *     @OA\Response(response=401, description="Unauthorized"),
         *     @OA\Response(response=403, description="Forbidden - Admin access required")
         * )
         */
        Flight::route('DELETE /api/upload/image', function() {
            // Only admins can delete images
            AuthMiddleware::authorizeRole(Roles::ADMIN);

            $service = Flight::fileUploadService();
            $data = Flight::request()->data->getData();
            
            if (!isset($data['filepath'])) {
                Flight::json([
                    'success' => false,
                    'message' => 'Filepath is required'
                ], 400);
                return;
            }

            $result = $service->deleteImage($data['filepath']);
            Flight::json($result, $result['success'] ? 200 : 400);
        });
    }
}
?>

