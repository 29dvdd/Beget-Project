<?php
require __DIR__ . '/../includes/check_admin.php';
require __DIR__ . '/../config/db.php';

function starts_with($haystack, $needle)
{
    return $needle === '' || strpos($haystack, $needle) === 0;
}

function redirect_to_edit($eventId, $status)
{
    header('Location: index.php?page=edit_event&id=' . (int)$eventId . '&' . $status . '=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Неверный метод запроса');
}

if (empty($_SESSION['csrf_token']) || empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    exit('Ошибка CSRF');
}

$id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$desc = isset($_POST['description']) ? trim($_POST['description']) : '';
$event_date = isset($_POST['event_date']) ? $_POST['event_date'] : '';
$venue = isset($_POST['venue']) ? trim($_POST['venue']) : '';
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
$tickets = isset($_POST['available_tickets']) ? (int)$_POST['available_tickets'] : 0;
$image = isset($_POST['current_image']) ? $_POST['current_image'] : null;

if ($image !== null && !starts_with($image, 'uploads/')) {
    $image = null;
}

if ($id <= 0 || $title === '' || $event_date === '') {
    http_response_code(400);
    exit('Ошибка данных');
}
if ($price < 0 || $tickets < 0) {
    http_response_code(400);
    exit('Цена и количество билетов не могут быть отрицательными');
}

if (!empty($_FILES['image_file']['name'])) {
    $allowedMimes = array('image/jpeg', 'image/png', 'image/gif');
    $maxSize = 5 * 1024 * 1024;

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $_FILES['image_file']['tmp_name']) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    if (!in_array($mime, $allowedMimes, true)) {
        redirect_to_edit($id, 'bad_file');
    }
    if ($_FILES['image_file']['size'] > $maxSize) {
        redirect_to_edit($id, 'file_too_large');
    }

    $uploadDir = realpath(__DIR__ . '/../uploads');
    if ($uploadDir === false) {
        $tmpDir = __DIR__ . '/../uploads';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }
        $uploadDir = realpath($tmpDir);
    }

    $mimeToExt = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif');
    if (!isset($mimeToExt[$mime])) {
        redirect_to_edit($id, 'bad_file');
    }
    $ext = $mimeToExt[$mime];
    $filename = 'poster_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    if (move_uploaded_file($_FILES['image_file']['tmp_name'], $fullPath)) {
        if (!empty($image)) {
            $basename = basename($image);
            $oldPath = $uploadDir . DIRECTORY_SEPARATOR . $basename;
            $realOld = realpath($oldPath);
            if ($realOld && is_file($realOld) && starts_with($realOld, $uploadDir)) {
                @unlink($realOld);
            }
        }
        $image = 'uploads/' . $filename;
    } else {
        redirect_to_edit($id, 'upload_error');
    }
}

$stmt = $pdo->prepare('UPDATE events SET title = ?, description = ?, event_date = ?, venue = ?, price = ?, available_tickets = ?, poster_url = ? WHERE id = ? LIMIT 1');
$stmt->execute(array($title, $desc, $event_date, $venue ? $venue : null, $price, $tickets, $image ? $image : null, $id));
header('Location: index.php?page=edit_event&id=' . $id . '&updated=1');
exit;
