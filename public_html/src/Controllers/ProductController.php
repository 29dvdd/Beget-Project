<?php
require_once __DIR__ . '/../Models/Product.php';

class ProductController
{
    public function add()
    {
        if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo "Доступ запрещён";
            exit;
        }

        $message = '';
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = [
                'title' => trim($_POST['title'] ?? ''),
                'price' => $_POST['price'] ?? '',
                'description' => trim($_POST['description'] ?? ''),
                'available_tickets' => $_POST['available_tickets'] ?? '0',
                'event_date' => $_POST['event_date'] ?? '',
                'address' => trim($_POST['address'] ?? ''),
                'latitude' => $_POST['latitude'] ?? '',
                'longitude' => $_POST['longitude'] ?? '',
                'image_url' => trim($_POST['image_url'] ?? ''),
            ];

            if ($old['title'] === '' || $old['price'] === '' || $old['event_date'] === '') {
                $message = "Заполните обязательные поля: Название, Цена, Дата мероприятия";
            } elseif (!is_numeric($old['price']) || (float)$old['price'] <= 0) {
                $message = "Цена некорректна";
            } elseif (!is_numeric($old['available_tickets']) || (int)$old['available_tickets'] < 0) {
                $message = "Количество билетов не может быть отрицательным";
            } else {
                $productModel = new Product();
                $success = $productModel->create(
                    $old['title'],
                    $old['description'],
                    (float)$old['price'],
                    (int)$old['available_tickets'],
                    $old['image_url'] ?: null,
                    $old['event_date'],
                    $old['address'] ?: null,
                    $old['latitude'] !== '' ? (float)$old['latitude'] : null,
                    $old['longitude'] !== '' ? (float)$old['longitude'] : null
                );

                if ($success) {
                    header("Location: ?page=add_product&ok=1");
                    exit;
                } else {
                    $message = "Ошибка при добавлении мероприятия";
                }
            }
        }

        require __DIR__ . '/../../templates/add_product.php';
    }

    public function getAll(): array
    {
        $productModel = new Product();
        return $productModel->getAll();
    }

    public function getById(int $id): ?array
    {
        $productModel = new Product();
        return $productModel->getById($id);
    }
}
