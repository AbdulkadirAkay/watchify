<?php
require_once 'BaseDao.php';

class ProductDao extends BaseDao {
    public function __construct() {
        parent::__construct("products");
    }

    public function getByCategory($category_id) {
        $stmt = $this->connection->prepare("SELECT * FROM products WHERE category_id = :category_id");
        $stmt->bindParam(':category_id', $category_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByBrand($brand) {
        $stmt = $this->connection->prepare("SELECT * FROM products WHERE brand = :brand");
        $stmt->bindParam(':brand', $brand);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAvailableProducts() {
        $stmt = $this->connection->prepare("SELECT * FROM products WHERE quantity > 0");
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function getPopularProducts($limit = 10) {
        $stmt = $this->connection->prepare("
            SELECT p.*, SUM(op.quantity) as total_ordered 
            FROM products p 
            LEFT JOIN order_product op ON p.id = op.product_id 
            GROUP BY p.id 
            ORDER BY total_ordered DESC 
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getNewArrivals($limit = 10) {
        $stmt = $this->connection->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateQuantity($id, $quantity) {
        $stmt = $this->connection->prepare("UPDATE products SET quantity = :quantity, updated_at = NOW() WHERE id = :id");
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function decreaseQuantity($id, $amount) {
        $stmt = $this->connection->prepare("UPDATE products SET quantity = quantity - :amount, updated_at = NOW() WHERE id = :id AND quantity >= :amount");
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
