<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/OrderDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/OrderProductDao.php';
require_once __DIR__ . '/../dao/BaseDao.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/ProductService.php';

class OrderService extends BaseService {
    private $userDao;
    private $orderProductDao;
    private $productService;

    public function __construct() {
        $dao = new OrderDao();
        parent::__construct($dao);
        $this->userDao = new UserDao();
        $this->orderProductDao = new OrderProductDao();
        $this->productService = new ProductService();
    }

    /**
     * Create a new order with validation
     */
    public function create($data) {
        $validator = $this->getValidator();
        $validator->clear();

        // Validation rules
        $validator->required('user_id', $data['user_id'] ?? null);
        $validator->required('total_price', $data['total_price'] ?? null);
        $validator->required('shipping_cost', $data['shipping_cost'] ?? null);
        $validator->required('payment_method', $data['payment_method'] ?? null);
        $validator->required('address', $data['address'] ?? null);
        $validator->required('status', $data['status'] ?? null);
        $validator->required('products', $data['products'] ?? null, 'Order must contain at least one product');

        if (isset($data['user_id'])) {
            $validator->integer('user_id', $data['user_id']);
            // Verify user exists
            $user = $this->userDao->getById($data['user_id']);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                    'errors' => ['user_id' => 'Invalid user ID']
                ];
            }
        }

        if (isset($data['total_price'])) {
            $validator->positive('total_price', $data['total_price'], 'Total price must be positive');
        }

        if (isset($data['shipping_cost'])) {
            $validator->numeric('shipping_cost', $data['shipping_cost']);
            $validator->min('shipping_cost', $data['shipping_cost'], 0, 'Shipping cost cannot be negative');
        }

        if (isset($data['payment_method'])) {
            $validator->in('payment_method', $data['payment_method'], 
                ['credit_card', 'debit_card', 'paypal', 'cash_on_delivery', 'bank_transfer'],
                'Invalid payment method'
            );
            $validator->maxLength('payment_method', $data['payment_method'], 45);
        }

        if (isset($data['address'])) {
            $validator->minLength('address', $data['address'], 10);
        }

        if (isset($data['status'])) {
            $validator->in('status', $data['status'], 
                ['pending', 'processing', 'shipped', 'delivered', 'cancelled'],
                'Invalid order status'
            );
            $validator->maxLength('status', $data['status'], 45);
        }

        // Validate products array
        if (isset($data['products']) && is_array($data['products'])) {
            if (empty($data['products'])) {
                return [
                    'success' => false,
                    'message' => 'Order must contain at least one product',
                    'errors' => ['products' => 'Products array cannot be empty']
                ];
            }

            foreach ($data['products'] as $index => $product) {
                if (!isset($product['product_id']) || !isset($product['quantity']) || !isset($product['unit_price'])) {
                    return [
                        'success' => false,
                        'message' => 'Invalid product data',
                        'errors' => ['products' => "Product at index {$index} is missing required fields (product_id, quantity, unit_price)"]
                    ];
                }

                // Verify product exists and has enough quantity
                $productResult = $this->productService->getById($product['product_id']);
                if (!$productResult['success']) {
                    return [
                        'success' => false,
                        'message' => "Product ID {$product['product_id']} not found",
                        'errors' => ['products' => "Invalid product at index {$index}"]
                    ];
                }

                $productData = $productResult['data'];
                if ($productData['quantity'] < $product['quantity']) {
                    return [
                        'success' => false,
                        'message' => "Insufficient quantity for product: {$productData['name']}",
                        'errors' => ['products' => "Only {$productData['quantity']} items available for product at index {$index}"]
                    ];
                }
            }
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

        // Extract products before inserting order
        $products = $data['products'];
        unset($data['products']);

        // Create order
        $orderResult = parent::create($data);
        
        if (!$orderResult['success']) {
            return $orderResult;
        }

        $orderId = $orderResult['data']['id'];

        // Add products to order and decrease product quantities
        try {
            foreach ($products as $product) {
                // Add product to order
                $this->orderProductDao->addProductToOrder(
                    $orderId,
                    $product['product_id'],
                    $product['quantity'],
                    $product['unit_price']
                );

                // Decrease product quantity
                $decreaseResult = $this->productService->decreaseQuantity(
                    $product['product_id'],
                    $product['quantity']
                );

                if (!$decreaseResult['success']) {
                    // Rollback: delete order and restore quantities
                    $this->dao->delete($orderId);
                    return [
                        'success' => false,
                        'message' => 'Failed to process order: ' . $decreaseResult['message']
                    ];
                }
            }

            return [
                'success' => true,
                'data' => ['id' => $orderId],
                'message' => 'Order created successfully'
            ];
        } catch (Exception $e) {
            // Rollback: delete order
            $this->dao->delete($orderId);
            return [
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update order with validation
     */
    public function update($id, $data) {
        $validator = $this->getValidator();
        $validator->clear();

        // Validation rules for update
        if (isset($data['payment_method'])) {
            $validator->in('payment_method', $data['payment_method'], 
                ['credit_card', 'debit_card', 'paypal', 'cash_on_delivery', 'bank_transfer'],
                'Invalid payment method'
            );
            $validator->maxLength('payment_method', $data['payment_method'], 45);
        }

        if (isset($data['address'])) {
            $validator->minLength('address', $data['address'], 10);
        }

        if (isset($data['status'])) {
            $validator->in('status', $data['status'], 
                ['pending', 'processing', 'shipped', 'delivered', 'cancelled'],
                'Invalid order status'
            );
            $validator->maxLength('status', $data['status'], 45);
        }

        if (isset($data['total_price'])) {
            $validator->positive('total_price', $data['total_price'], 'Total price must be positive');
        }

        if (isset($data['shipping_cost'])) {
            $validator->numeric('shipping_cost', $data['shipping_cost']);
            $validator->min('shipping_cost', $data['shipping_cost'], 0, 'Shipping cost cannot be negative');
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
     * Get orders by user ID
     */
    public function getByUserId($user_id) {
        try {
            if (empty($user_id) || !is_numeric($user_id)) {
                return [
                    'success' => false,
                    'message' => 'Invalid user ID'
                ];
            }

            $orders = $this->dao->getByUserId($user_id);
            
            return [
                'success' => true,
                'data' => $orders
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve orders: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get orders by status
     */
    public function getByStatus($status) {
        try {
            if (empty($status)) {
                return [
                    'success' => false,
                    'message' => 'Status is required'
                ];
            }

            $validator = $this->getValidator();
            $validator->in('status', $status, 
                ['pending', 'processing', 'shipped', 'delivered', 'cancelled'],
                'Invalid order status'
            );

            if (!$validator->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->getErrors()
                ];
            }

            $orders = $this->dao->getByStatus($status);
            
            return [
                'success' => true,
                'data' => $orders
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve orders: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get orders with user information
     */
    public function getOrdersWithUserInfo() {
        try {
            $orders = $this->dao->getOrdersWithUserInfo();
            
            return [
                'success' => true,
                'data' => $orders
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve orders: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get orders by date range
     */
    public function getOrdersByDateRange($start_date, $end_date) {
        try {
            if (empty($start_date) || empty($end_date)) {
                return [
                    'success' => false,
                    'message' => 'Start date and end date are required'
                ];
            }

            $orders = $this->dao->getOrdersByDateRange($start_date, $end_date);
            
            return [
                'success' => true,
                'data' => $orders
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve orders: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update order status
     */
    public function updateStatus($id, $status) {
        $validator = $this->getValidator();
        $validator->clear();

        $validator->required('status', $status);
        $validator->in('status', $status, 
            ['pending', 'processing', 'shipped', 'delivered', 'cancelled'],
            'Invalid order status'
        );

        if (!$validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ];
        }

        try {
            // Check if order exists
            $order = $this->dao->getById($id);
            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found'
                ];
            }

            $result = $this->dao->updateStatus($id, $status);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Order status updated successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update order status'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ];
        }
    }
}
?>