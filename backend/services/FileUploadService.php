<?php
require_once __DIR__ . '/../config.php';

class FileUploadService {
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;

    public function __construct() {
        // Set upload directory (relative to backend folder)
        $this->uploadDir = __DIR__ . '/../uploads/products/';
        
        // Create directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }

        // Allowed image types
        $this->allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        // Max file size: 5MB
        $this->maxFileSize = 5 * 1024 * 1024;
    }

    /**
     * Upload an image file
     */
    public function uploadImage($file) {
        try {
            // Validate file exists
            if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
                return [
                    'success' => false,
                    'message' => 'No file uploaded'
                ];
            }

            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return [
                    'success' => false,
                    'message' => 'File upload error: ' . $this->getUploadErrorMessage($file['error'])
                ];
            }

            // Validate file size
            if ($file['size'] > $this->maxFileSize) {
                return [
                    'success' => false,
                    'message' => 'File size exceeds maximum allowed size of 5MB'
                ];
            }

            // Validate file type
            $fileType = mime_content_type($file['tmp_name']);
            if (!in_array($fileType, $this->allowedTypes)) {
                return [
                    'success' => false,
                    'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed'
                ];
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('product_', true) . '.' . $extension;
            $filepath = $this->uploadDir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Return relative path from frontend perspective
                $relativePath = 'uploads/products/' . $filename;
                
                return [
                    'success' => true,
                    'message' => 'File uploaded successfully',
                    'data' => [
                        'filename' => $filename,
                        'filepath' => $relativePath,
                        'url' => '//'. Config::HOSTNAME() . '/' . $relativePath
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to move uploaded file'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'File upload failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete an uploaded image
     */
    public function deleteImage($filepath) {
        try {
            // Security check: ensure filepath is within uploads directory
            $realPath = realpath(__DIR__ . '/../uploads/' . $filepath);
            $uploadPath = realpath($this->uploadDir);

            if ($realPath === false || strpos($realPath, $uploadPath) !== 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid file path'
                ];
            }

            if (file_exists($realPath)) {
                if (unlink($realPath)) {
                    return [
                        'success' => true,
                        'message' => 'File deleted successfully'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'File not found or could not be deleted'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'File deletion failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE directive in HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Validate image dimensions (optional)
     */
    public function validateImageDimensions($filepath, $maxWidth = 2000, $maxHeight = 2000) {
        $imageInfo = getimagesize($filepath);
        
        if ($imageInfo === false) {
            return [
                'success' => false,
                'message' => 'Invalid image file'
            ];
        }

        list($width, $height) = $imageInfo;

        if ($width > $maxWidth || $height > $maxHeight) {
            return [
                'success' => false,
                'message' => "Image dimensions exceed maximum allowed size ({$maxWidth}x{$maxHeight})"
            ];
        }

        return [
            'success' => true,
            'width' => $width,
            'height' => $height
        ];
    }
}
?>


