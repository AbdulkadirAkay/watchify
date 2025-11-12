<?php
require_once __DIR__ . '/BaseDao.php';

class OrderProductDao extends BaseDao {
    public function __construct() {
        parent::__construct("order_product");
    }

    public function getOrderProductsWithDetails($order_id) {
        $stmt = $this->connection->prepare("
            SELECT op.*, p.name as product_name, p.brand, p.image_url 
            FROM order_product op 
            INNER JOIN products p ON op.product_id = p.id 
            WHERE op.order_id = :order_id
        ");
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function getOrderTotal($order_id) {
        $stmt = $this->connection->prepare("
            SELECT SUM(quantity * unit_price) as order_total 
            FROM order_product 
            WHERE order_id = :order_id
        ");
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['order_total'] ?? 0;
    }

    public function addProductToOrder($order_id, $product_id, $quantity, $unit_price) {
        $data = [
            'order_id' => $order_id,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'unit_price' => $unit_price,
            'created_at' => date('Y-m-d H:i:s')
        ];
        return $this->insert($data);
    }


}
?>
