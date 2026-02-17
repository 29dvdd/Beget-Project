-- SQL для проекта "Афиша" (Барабашов)
-- Выполнить в phpMyAdmin (Beget) в вашей базе данных.

-- 1) Таблица пользователей (если ещё нет)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','client') NOT NULL DEFAULT 'client',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Таблица мероприятий
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `event_date` DATE NOT NULL,
  `venue` VARCHAR(255) DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `available_tickets` INT NOT NULL DEFAULT 0,
  `poster_url` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Если таблица events уже была, но без колонок price/available_tickets:
ALTER TABLE `events`
  ADD COLUMN IF NOT EXISTS `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN IF NOT EXISTS `available_tickets` INT NOT NULL DEFAULT 0;

-- 3) Таблица заказов (покупки билетов)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `event_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `status` ENUM('new','processing','done','cancelled') NOT NULL DEFAULT 'new',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_orders_user` (`user_id`),
  KEY `idx_orders_event` (`event_id`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_orders_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) Создание админа (пример)
-- INSERT INTO users (email, password_hash, role) VALUES ('admin@example.com', '<hash>', 'admin');
