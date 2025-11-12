<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/OrderProductDao.php';
require_once __DIR__ . '/../dao/OrderDao.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../dao/BaseDao.php';
require_once __DIR__ . '/../config.php';

class OrderProductService extends BaseService {
    private $orderDao;
    private $productDao;

    public function __construct() {
        $dao = new OrderProductDao();
        parent::__construct($dao);
        $this->orderDao = new OrderDao();
        $this->productDao = new ProductDao();
    }

    /**
     * Create order product relationship with validation
     */
    public function create($data) {
        $validator = $this->getValidator();
        $validator->clear();

        // Validation rules
        $validator->required('order_id', $data['order_id'] ?? null);
        $validator->required('product_id', $data['product_id'] ?? null);
        $validator->required('quantity', $data['quantity'] ?? null);
        $validator->required('unit_price', $data['unit_price'] ?? null);

        if (isset($data['order_id'])) {
            $validator->integer('order_id', $data['order_id']);
            // Verify order exists
            $order = $this->orderDao->getById($data['order_id']);
            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found',
                    'errors' => ['order_id' => 'Invalid order ID']
                ];
            }
        }

        if (isset($data['product_id'])) {
            $validator->integer('product_id', $data['product_id']);
            // Verify product exists
            $product = $this->productDao->getById($data['product_id']);
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found',
                    'errors' => ['product_id' => 'Invalid product ID']
                ];
            }
        }

        if (isset($data['quantity'])) {
            $validator->integer('quantity', $data['quantity']);
            $validator->positive('quantity', $data['quantity'], 'Quantity must be positive');
        }

        if (isset($data['unit_price'])) {
            $validator->positive('unit_price', $data['unit_price'], 'Unit price must be positive');
        }

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ];
        }

        // Set timestamp
        $data['created_at'] = date('Y-m-d H:i:s');

        return parent::create($data);
    }

    /**
     * Get order products with details
     */
    public function getOrderProductsWithDetails($order_id) {
        try {
            if (empty($order_id) || !is_numeric($order_id)) {
                return [
                    'success' => false,
                    'message' => 'Invalid order ID'
                ];
            }

            // Verify order exists
            $order = $this->orderDao->getById($order_id);
            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found'
                ];
            }

            $orderProducts = $this->dao->getOrderProductsWithDetails($order_id);
            
            return [
                'success' => true,
                'data' => $orderProducts
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve order products: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get order total
     */
    public function getOrderTotal($order_id) {
        try {
            if (empty($order_id) || !is_numeric($order_id)) {
                return [
                    'success' => false,
                    'message' => 'Invalid order ID'
                ];
            }

            // Verify order exists
            $order = $this->orderDao->getById($order_id);
            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found'
                ];
            }

            $total = $this->dao->getOrderTotal($order_id);
            
            return [
                'success' => true,
                'data' => ['order_id' => $order_id, 'total' => $total]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to calculate order total: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add product to order
     */
    public function addProductToOrder($order_id, $product_id, $quantity, $unit_price) {
        $validator = $this->getValidator();
        $validator->clear();

        $validator->required('order_id', $order_id);
        $validator->required('product_id', $product_id);
        $validator->required('quantity', $quantity);
        $validator->required('unit_price', $unit_price);

        $validator->integer('order_id', $order_id);
        $validator->integer('product_id', $product_id);
        $validator->integer('quantity', $quantity);
        $validator->positive('quantity', $quantity, 'Quantity must be positive');
        $validator->positive('unit_price', $unit_price, 'Unit price must be positive');

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ];
        }

        try {
            // Verify order exists
            $order = $this->orderDao->getById($order_id);
            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found'
                ];
            }

            // Verify product exists
            $product = $this->productDao->getById($product_id);
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }

            $result = $this->dao->addProductToOrder($order_id, $product_id, $quantity, $unit_price);

            if ($result) {
                return [
                    'success' => true,
                    'data' => ['id' => $result],
                    'message' => 'Product added to order successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to add product to order'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to add product to order: ' . $e->getMessage()
            ];
        }
    }
}
?>
