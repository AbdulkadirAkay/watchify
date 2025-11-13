<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../dao/CategoryDao.php';
require_once __DIR__ . '/../dao/BaseDao.php';
require_once __DIR__ . '/../config.php';

class ProductService extends BaseService {
    private $categoryDao;

    public function __construct() {
        $dao = new ProductDao();
        parent::__construct($dao);
        $this->categoryDao = new CategoryDao();
    }

    /**
     * Create a new product with validation
     */
    public function create($data) {
        $validator = $this->getValidator();
        $validator->clear();

        // Validation rules
        $validator->required('name', $data['name'] ?? null);
        $validator->required('brand', $data['brand'] ?? null);
        $validator->required('price', $data['price'] ?? null);
        $validator->required('quantity', $data['quantity'] ?? null);
        $validator->required('category_id', $data['category_id'] ?? null);
        $validator->required('image_url', $data['image_url'] ?? null);
        $validator->required('description', $data['description'] ?? null);

        if (isset($data['name'])) {
            $validator->maxLength('name', $data['name'], 100);
        }

        if (isset($data['brand'])) {
            $validator->maxLength('brand', $data['brand'], 100);
        }

        if (isset($data['price'])) {
            $validator->positive('price', $data['price'], 'Price must be a positive number');
        }

        if (isset($data['quantity'])) {
            $validator->integer('quantity', $data['quantity']);
            $validator->min('quantity', $data['quantity'], 0, 'Quantity cannot be negative');
        }

        if (isset($data['category_id'])) {
            $validator->integer('category_id', $data['category_id']);
            // Verify category exists
            $category = $this->categoryDao->getById($data['category_id']);
            if (!$category) {
                return [
                    'success' => false,
                    'message' => 'Category not found',
                    'errors' => ['category_id' => 'Invalid category ID']
                ];
            }
        }

        if (isset($data['image_url'])) {
            $validator->maxLength('image_url', $data['image_url'], 255);
            $validator->url('image_url', $data['image_url'], 'Invalid image URL format');
        }

        if (isset($data['description'])) {
            $validator->maxLength('description', $data['description'], 500);
        }

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ];
        }

        // Set timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::create($data);
    }

    /**
     * Update product with validation
     */
    public function update($id, $data) {
        $validator = $this->getValidator();
        $validator->clear();

        // Validation rules for update
        if (isset($data['name'])) {
            $validator->maxLength('name', $data['name'], 100);
        }

        if (isset($data['brand'])) {
            $validator->maxLength('brand', $data['brand'], 100);
        }

        if (isset($data['price'])) {
            $validator->positive('price', $data['price'], 'Price must be a positive number');
        }

        if (isset($data['quantity'])) {
            $validator->integer('quantity', $data['quantity']);
            $validator->min('quantity', $data['quantity'], 0, 'Quantity cannot be negative');
        }

        if (isset($data['category_id'])) {
            $validator->integer('category_id', $data['category_id']);
            // Verify category exists
            $category = $this->categoryDao->getById($data['category_id']);
            if (!$category) {
                return [
                    'success' => false,
                    'message' => 'Category not found',
                    'errors' => ['category_id' => 'Invalid category ID']
                ];
            }
        }

        if (isset($data['image_url'])) {
            $validator->maxLength('image_url', $data['image_url'], 255);
            $validator->url('image_url', $data['image_url'], 'Invalid image URL format');
        }

        if (isset($data['description'])) {
            $validator->maxLength('description', $data['description'], 500);
        }

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ];
        }

        // Update timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::update($id, $data);
    }

    /**
     * Get products by category
     */
    public function getByCategory($category_id) {
        try {
            if (empty($category_id) || !is_numeric($category_id)) {
                return [
                    'success' => false,
                    'message' => 'Invalid category ID'
                ];
            }

            $products = $this->dao->getByCategory($category_id);
            
            return [
                'success' => true,
                'data' => $products
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get products by brand
     */
    public function getByBrand($brand) {
        try {
            if (empty($brand)) {
                return [
                    'success' => false,
                    'message' => 'Brand is required'
                ];
            }

            $products = $this->dao->getByBrand($brand);
            
            return [
                'success' => true,
                'data' => $products
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get available products (quantity > 0)
     */
    public function getAvailableProducts() {
        try {
            $products = $this->dao->getAvailableProducts();
            
            return [
                'success' => true,
                'data' => $products
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get popular products
     */
    public function getPopularProducts($limit = 10) {
        try {
            if (!is_numeric($limit) || $limit < 1) {
                $limit = 10;
            }

            $products = $this->dao->getPopularProducts($limit);
            
            return [
                'success' => true,
                'data' => $products
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get new arrivals
     */
    public function getNewArrivals($limit = 10) {
        try {
            if (!is_numeric($limit) || $limit < 1) {
                $limit = 10;
            }

            $products = $this->dao->getNewArrivals($limit);
            
            return [
                'success' => true,
                'data' => $products
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update product quantity
     */
    public function updateQuantity($id, $quantity) {
        $validator = $this->getValidator();
        $validator->clear();

        $validator->required('quantity', $quantity);
        $validator->integer('quantity', $quantity);
        $validator->min('quantity', $quantity, 0, 'Quantity cannot be negative');

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ];
        }

        try {
            // Check if product exists
            $product = $this->dao->getById($id);
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }

            $result = $this->dao->updateQuantity($id, $quantity);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Quantity updated successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update quantity'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update quantity: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Decrease product quantity (for order processing)
     */
    public function decreaseQuantity($id, $amount) {
        $validator = $this->getValidator();
        $validator->clear();

        $validator->required('amount', $amount);
        $validator->integer('amount', $amount);
        $validator->positive('amount', $amount, 'Amount must be positive');

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ];
        }

        try {
            // Check if product exists and has enough quantity
            $product = $this->dao->getById($id);
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }

            if ($product['quantity'] < $amount) {
                return [
                    'success' => false,
                    'message' => 'Insufficient quantity available',
                    'errors' => ['quantity' => 'Only ' . $product['quantity'] . ' items available']
                ];
            }

            $result = $this->dao->decreaseQuantity($id, $amount);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Quantity decreased successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to decrease quantity'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to decrease quantity: ' . $e->getMessage()
            ];
        }
    }
}
?>
