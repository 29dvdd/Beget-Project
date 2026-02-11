<?php
require_once __DIR__ . '/../Models/Order.php';
require_once __DIR__ . '/../Models/Product.php';

class OrderController
{
    public function create()
    {
        $message = '';
        $productModel = new Product();
        $orderModel = new Order();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);

            if ($quantity < 1) {
                $message = "Некорректное количество билетов";
            } else {
                if ($orderModel->create($_SESSION['user_id'], $productId, $quantity)) {
                    header('Location: ?page=orders&ok=1');
                    exit;
                } else {
                    $message = "Ошибка при покупке билетов или недостаточно билетов";
                }
            }
        }

        $product = $productModel->getById((int)($_GET['id'] ?? 0));
        require __DIR__ . '/../../templates/buy_ticket.php';
    }

    public function index()
    {
        $orderModel = new Order();
        $orders = $orderModel->getByUser($_SESSION['user_id']);
        require __DIR__ . '/../../templates/orders.php';
    }
}
