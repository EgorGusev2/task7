<?php
session_start();
require_once 'includes/security.php';
secureErrorHandling();

if (empty($_SESSION['booking_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $db = new PDO("mysql:host=localhost;dbname=u82361;charset=utf8", 'u82361', '9967838');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Ошибка подключения к базе данных');
}

$stmt = $db->prepare("SELECT * FROM rehearsal_booking WHERE id = ?");
$stmt->execute([$_SESSION['booking_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Моя запись</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .booking-card { background: #f8f9fa; padding: 20px; border-radius: 16px; margin-top: 20px; }
        .status-pending { color: #e67e22; font-weight: bold; }
        .status-confirmed { color: #27ae60; font-weight: bold; }
        .status-cancelled { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>📋 Моя запись на репетицию</h1>
    
    <div class="user-info">
        <span>👤 Вы вошли как <strong><?= h($_SESSION['login']) ?></strong></span>
        <a href="logout.php" class="logout-link">🚪 Выйти</a>
    </div>
    
    <div class="booking-card">
        <p><strong>ФИО:</strong> <?= h($booking['full_name']) ?></p>
        <p><strong>Телефон:</strong> <?= h($booking['phone']) ?></p>
        <p><strong>Дата:</strong> <?= h($booking['booking_date']) ?></p>
        <p><strong>Время:</strong> <?= h($booking['booking_time']) ?></p>
        <p><strong>Студия:</strong> <?= h($booking['studio_name']) ?></p>
        <p><strong>Пожелания:</strong> <?= h($booking['special_requests'] ?: '-') ?></p>
        <p><strong>Статус:</strong> 
            <span class="status-<?= $booking['status'] ?>">
                <?= $booking['status'] == 'pending' ? '⏳ В ожидании' : ($booking['status'] == 'confirmed' ? '✅ Подтверждено' : '❌ Отменено') ?>
            </span>
        </p>
    </div>
    
    <p style="margin-top: 20px; text-align: center;">
        <a href="index.php">← Редактировать запись</a>
    </p>
</div>
</body>
</html>