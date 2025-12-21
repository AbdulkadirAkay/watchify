<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ProductDao.php';
require_once __DIR__ . '/../dao/OrderDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class StatisticsService {
    private $productDao;
    private $orderDao;
    private $userDao;

    public function __construct() {
        $this->productDao = new ProductDao();
        $this->orderDao = new OrderDao();
        $this->userDao = new UserDao();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        try {
            $stats = [
                'total_products' => $this->getTotalProducts(),
                'total_orders' => $this->getTotalOrders(),
                'total_revenue' => $this->getTotalRevenue(),
                'total_customers' => $this->getTotalCustomers(),
                'recent_orders' => $this->getRecentOrders(5)
            ];

            return [
                'success' => true,
                'data' => $stats
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get total number of products
     */
    private function getTotalProducts() {
        $connection = Database::connect();
        $stmt = $connection->prepare("SELECT COUNT(*) as total FROM products");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['total'];
    }

    /**
     * Get total number of orders
     */
    private function getTotalOrders() {
        $connection = Database::connect();
        $stmt = $connection->prepare("SELECT COUNT(*) as total FROM orders");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['total'];
    }

    /**
     * Get total revenue from all orders
     */
    private function getTotalRevenue() {
        $connection = Database::connect();
        $stmt = $connection->prepare("SELECT SUM(total_price) as revenue FROM orders");
        $stmt->execute();
        $result = $stmt->fetch();
        return (float) ($result['revenue'] ?? 0);
    }

    /**
     * Get total number of customers (users)
     */
    private function getTotalCustomers() {
        $connection = Database::connect();
        $stmt = $connection->prepare("SELECT COUNT(*) as total FROM users WHERE is_admin = 0");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['total'];
    }

    /**
     * Get recent orders with user info
     */
    private function getRecentOrders($limit = 5) {
        $connection = Database::connect();
        $stmt = $connection->prepare("
            SELECT o.id, o.total_price, o.status, o.created_at,
                   u.name as user_name, u.email as user_email
            FROM orders o
            INNER JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get order statistics by status
     */
    public function getOrderStatsByStatus() {
        try {
            $connection = Database::connect();
            $stmt = $connection->prepare("
                SELECT status, COUNT(*) as count, SUM(total_price) as total_amount
                FROM orders
                GROUP BY status
            ");
            $stmt->execute();
            $stats = $stmt->fetchAll();

            return [
                'success' => true,
                'data' => $stats
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve order statistics: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get product statistics by category
     */
    public function getProductStatsByCategory() {
        try {
            $connection = Database::connect();
            $stmt = $connection->prepare("
                SELECT c.name as category, COUNT(p.id) as product_count, SUM(p.quantity) as total_stock
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id
                GROUP BY c.id, c.name
            ");
            $stmt->execute();
            $stats = $stmt->fetchAll();

            return [
                'success' => true,
                'data' => $stats
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve product statistics: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get sales statistics for a date range
     */
    public function getSalesStats($start_date = null, $end_date = null) {
        try {
            $connection = Database::connect();
            
            if (!$start_date) {
                $start_date = date('Y-m-01'); // First day of current month
            }
            if (!$end_date) {
                $end_date = date('Y-m-d'); // Today
            }

            $stmt = $connection->prepare("
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_price) as total_revenue,
                    AVG(total_price) as average_order_value,
                    SUM(shipping_cost) as total_shipping
                FROM orders
                WHERE created_at BETWEEN :start_date AND :end_date
            ");
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            $stats = $stmt->fetch();

            return [
                'success' => true,
                'data' => $stats
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve sales statistics: ' . $e->getMessage()
            ];
        }
    }
}
?>


