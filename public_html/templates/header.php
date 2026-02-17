<?php
$email = $_SESSION['email'] ?? null;
$role  = $_SESSION['role'] ?? null;
$isAdmin = ($role === 'admin');
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Афиша мероприятий</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    body{min-height:100vh;display:flex;flex-direction:column;}
    main{flex:1;}
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php?page=home">
      <i class="bi bi-calendar2-week"></i> Афиша мероприятий
    </a>

    <div class="d-flex align-items-center gap-2">
      <?php if ($email): ?>
        <span class="text-white small me-2"><?= htmlspecialchars($email) ?></span>

        <?php if ($isAdmin): ?>
          <a class="btn btn-success btn-sm" href="index.php?page=add_event">
            <i class="bi bi-plus-circle"></i> Добавить
          </a>
          <a class="btn btn-outline-light btn-sm" href="index.php?page=admin_orders">
            <i class="bi bi-list-check"></i> Заказы
          </a>
        <?php endif; ?>

        <a class="btn btn-outline-light btn-sm" href="index.php?page=profile">
          Мои билеты
        </a>

        <a class="btn btn-outline-light btn-sm" href="index.php?page=logout">
          Выйти
        </a>
      <?php else: ?>
        <a class="btn btn-outline-light btn-sm" href="index.php?page=login">Войти</a>
        <a class="btn btn-primary btn-sm" href="index.php?page=register">Регистрация</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<main class="py-4">