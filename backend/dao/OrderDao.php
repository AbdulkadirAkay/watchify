<?php
require_once __DIR__ . '/BaseDao.php';

class OrderDao extends BaseDao {
    public function __construct() {
        parent::__construct("orders");
    }

    public function getByUserId($user_id) {
        $stmt = $this->connection->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByStatus($status) {
        $stmt = $this->connection->prepare("SELECT * FROM orders WHERE status = :status ORDER BY created_at DESC");
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function getOrdersWithUserInfo() {
        $stmt = $this->connection->prepare("
            SELECT o.*, u.name as user_name, u.email as user_email 
            FROM orders o 
            INNER JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getOrdersByDateRange($start_date, $end_date) {
        $stmt = $this->connection->prepare("SELECT * FROM orders WHERE created_at BETWEEN :start_date AND :end_date ORDER BY created_at DESC");
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status) {
        $stmt = $this->connection->prepare("UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }


}
?>
