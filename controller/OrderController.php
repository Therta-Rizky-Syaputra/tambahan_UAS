<?php


class OrderController {
    private $conn;
    private $outlet_code;

    public function __construct($conn, $outlet_code) {
        $this->conn = $conn;
        $this->outlet_code = $outlet_code;
    }

   
    public function getPendingOrders() {
        $query = mysqli_query($this->conn, "
            SELECT o.*, GROUP_CONCAT(
                CONCAT(oi.product_name, ' (', oi.size, ') x', oi.quantity, ' = Rp.', FORMAT(oi.total_price, 0))
                SEPARATOR '; '
            ) as items
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.outlet_code = '$this->outlet_code' AND o.status = 'pending'
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");

        $orders = [];
        while ($order = mysqli_fetch_assoc($query)) {
            $orders[] = $order;
        }

        return [
            'success' => true,
            'orders' => $orders
        ];
    }

   
    public function getCompletedOrders() {
        $query = mysqli_query($this->conn, "
            SELECT o.*, GROUP_CONCAT(
                CONCAT(oi.product_name, ' (', oi.size, ') x', oi.quantity, ' = Rp.', FORMAT(oi.total_price, 0))
                SEPARATOR '; '
            ) as items
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.outlet_code = '$this->outlet_code' AND o.status = 'completed'
            GROUP BY o.id
            ORDER BY o.completed_at DESC
        ");

        $orders = [];
        while ($order = mysqli_fetch_assoc($query)) {
            $orders[] = $order;
        }

        return [
            'success' => true,
            'orders' => $orders
        ];
    }

   
    public function getOrderById($order_id) {
        // Get order info
        $order_query = mysqli_query($this->conn, "
            SELECT * FROM orders WHERE id = '$order_id' AND outlet_code = '$this->outlet_code'
        ");

        if (mysqli_num_rows($order_query) === 0) {
            return [
                'success' => false,
                'message' => 'Order not found'
            ];
        }

        $order = mysqli_fetch_assoc($order_query);

       
        $items_query = mysqli_query($this->conn, "
            SELECT * FROM order_items WHERE order_id = '$order_id' ORDER BY id
        ");

        $items = [];
        while ($item = mysqli_fetch_assoc($items_query)) {
            $items[] = $item;
        }

        $order['items'] = $items;

        return [
            'success' => true,
            'order' => $order
        ];
    }

   
    public function completeOrder($order_id) {
        // Check if order exists and belongs to outlet
        $check = $this->getOrderById($order_id);
        if (!$check['success']) {
            return [
                'success' => false,
                'message' => 'Order not found'
            ];
        }

        if ($check['order']['status'] === 'completed') {
            return [
                'success' => false,
                'message' => 'Order is already completed'
            ];
        }

       
        $query = mysqli_query($this->conn, "
            UPDATE orders
            SET status = 'completed', completed_at = NOW()
            WHERE id = '$order_id' AND outlet_code = '$this->outlet_code'
        ");

        if ($query) {
            return [
                'success' => true,
                'message' => 'Order completed successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to complete order: ' . mysqli_error($this->conn)
            ];
        }
    }

   
    public function cancelOrder($order_id) {
        // Check if order exists and belongs to outlet
        $check = $this->getOrderById($order_id);
        if (!$check['success']) {
            return [
                'success' => false,
                'message' => 'Order not found'
            ];
        }

        if ($check['order']['status'] === 'completed') {
            return [
                'success' => false,
                'message' => 'Cannot cancel completed order'
            ];
        }

       
        $query = mysqli_query($this->conn, "
            UPDATE orders
            SET status = 'cancelled'
            WHERE id = '$order_id' AND outlet_code = '$this->outlet_code'
        ");

        if ($query) {
            return [
                'success' => true,
                'message' => 'Order cancelled successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to cancel order: ' . mysqli_error($this->conn)
            ];
        }
    }

   
    public function getOrderStats() {
        $query = mysqli_query($this->conn, "
            SELECT
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount END), 0) as total_revenue,
                COUNT(*) as total_orders
            FROM orders
            WHERE outlet_code = '$this->outlet_code'
            AND DATE(created_at) = CURDATE()
        ");

        $stats = mysqli_fetch_assoc($query);

        return [
            'success' => true,
            'stats' => $stats
        ];
    }
}
?>
