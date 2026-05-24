<?php
session_start();
require_once 'includes/security.php';
secureErrorHandling();

try {
    $db = new PDO("mysql:host=localhost;dbname=u82361;charset=utf8", 'u82361', '9967838');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Ошибка БД');
}

// HTTP Basic Auth
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    echo '<h1>401 Требуется авторизация</h1>';
    exit();
}

$stmt = $db->prepare("SELECT password_hash FROM admins WHERE login = ?");
$stmt->execute([$_SERVER['PHP_AUTH_USER']]);
$admin = $stmt->fetch();

if (!$admin || md5($_SERVER['PHP_AUTH_PW']) != $admin['password_hash']) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    echo '<h1>401 Неверный логин или пароль</h1>';
    exit();
}

if (isset($_POST['delete']) && isset($_POST['id'])) {
    $stmt = $db->prepare("DELETE FROM rehearsal_booking WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    $message = '<div class="success">✅ Запись удалена</div>';
}

if (isset($_POST['update_status']) && isset($_POST['id']) && isset($_POST['status'])) {
    $stmt = $db->prepare("UPDATE rehearsal_booking SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['id']]);
    $message = '<div class="success">✅ Статус обновлён</div>';
}

$stmt = $db->prepare("SELECT * FROM rehearsal_booking ORDER BY booking_date DESC, booking_time DESC");
$stmt->execute();
$bookings = $stmt->fetchAll();

$stmt = $db->prepare("SELECT COUNT(*) FROM rehearsal_booking");
$stmt->execute();
$total = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM rehearsal_booking WHERE status = 'pending'");
$stmt->execute();
$pending = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats { background: #667eea; color: white; padding: 20px; border-radius: 16px; margin-bottom: 20px; display: flex; gap: 20px; flex-wrap: wrap; }
        .stat-box { background: rgba(255,255,255,0.2); padding: 15px; border-radius: 12px; text-align: center; flex: 1; min-width: 150px; }
        .stat-number { font-size: 32px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4a5568; color: white; }
        .status-pending { color: #e67e22; font-weight: bold; }
        .status-confirmed { color: #27ae60; font-weight: bold; }
        .status-cancelled { color: #e74c3c; font-weight: bold; }
        .btn-delete { background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 6px; cursor: pointer; }
        .status-select { padding: 5px; border-radius: 6px; }
    </style>
</head>
<body>
<div class="container" style="max-width: 1200px;">
    <h1>👑 Панель администратора</h1>
    
    <?php if (isset($message)) echo $message; ?>
    
    <div class="stats">
        <div class="stat-box">
            <div class="stat-number"><?= h($total) ?></div>
            <div>Всего записей</div>
        </div>
        <div class="stat-box">
            <div class="stat-number"><?= h($pending) ?></div>
            <div>Ожидают подтверждения</div>
        </div>
    </div>
    
    <h2>📋 Список записей на репетицию</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>ФИО</th><th>Телефон</th><th>Логин</th><th>Дата</th><th>Время</th><th>Студия</th><th>Статус</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
            <tr>
                <td><?= h($b['id']) ?></td>
                <td><?= h($b['full_name']) ?></td>
                <td><?= h($b['phone']) ?></td>
                <td><?= h($b['login']) ?></td>
                <td><?= h($b['booking_date']) ?></td>
                <td><?= h($b['booking_time']) ?></td>
                <td><?= h($b['studio_name']) ?></td>
                <td class="status-<?= $b['status'] ?>"><?= h($b['status']) ?></td>
                <td>
                    <form method="POST" style="display: inline-block; margin-right: 5px;">
                        <input type="hidden" name="id" value="<?= h($b['id']) ?>">
                        <select name="status" class="status-select">
                            <option value="pending" <?= $b['status'] == 'pending' ? 'selected' : '' ?>>В ожидании</option>
                            <option value="confirmed" <?= $b['status'] == 'confirmed' ? 'selected' : '' ?>>Подтверждено</option>
                            <option value="cancelled" <?= $b['status'] == 'cancelled' ? 'selected' : '' ?>>Отменено</option>
                        </select>
                        <button type="submit" name="update_status">✅</button>
                    </form>
                    <form method="POST" style="display: inline-block;" onsubmit="return confirm('Удалить запись?')">
                        <input type="hidden" name="id" value="<?= h($b['id']) ?>">
                        <button type="submit" name="delete" class="btn-delete">🗑️</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <p style="margin-top: 20px;"><a href="index.php">← На главную</a></p>
</div>
</body>
</html>