-- Run once in phpMyAdmin if your `orders` table already exists (adds kitchen flow statuses).
USE cafe_pos;

ALTER TABLE orders
MODIFY COLUMN status ENUM('pending','preparing','ready','completed','cancelled') DEFAULT 'pending';
