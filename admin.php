<?php
// admin.php
require_once 'includes/functions.php';

// Information Disclosure: отключаем вывод ошибок
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// HTTP-авторизация
if (!checkAdminAuth()) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    echo '<h1>401 Требуется авторизация</h1>';
    exit();
}

// Обработка удаления (исправлено: SQL Injection)
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $stmt = $db->prepare("DELETE FROM application WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    $message = '<div class="success">✅ Запись удалена</div>';
}

// Получение данных (исправлено: SQL Injection)
$stmt = $db->prepare("
    SELECT a.*, GROUP_CONCAT(pl.name ORDER BY pl.name SEPARATOR ', ') as languages_list
    FROM application a
    LEFT JOIN application_language al ON a.id = al.application_id
    LEFT JOIN programming_language pl ON al.language_id = pl.id
    GROUP BY a.id ORDER BY a.id DESC
");
$stmt->execute();
$users = $stmt->fetchAll();

$stmt = $db->prepare("SELECT COUNT(*) FROM application");
$stmt->execute();
$totalUsers = $stmt->fetchColumn();

$languageStats = getLanguageStats();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-panel { background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .stats-grid { display: flex; gap: 20px; flex-wrap: wrap; }
        .stat-box { background: white; padding: 15px; border-radius: 8px; flex: 1; min-width: 200px; }
        .lang-list { margin-top: 10px; }
        .lang-item { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #4CAF50; color: white; }
        tr:hover { background: #f5f5f5; }
        .btn-edit { background: #ffc107; color: #333; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
        .btn-delete { background: #f44336; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <h1>👑 Панель администратора</h1>
    
    <?php if (isset($message)) echo $message; ?>
    
    <div class="stats-panel">
        <h2>📊 Статистика</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <strong>Всего пользователей:</strong> <?php echo h($totalUsers); ?>
            </div>
            <div class="stat-box">
                <strong>Популярность языков:</strong>
                <div class="lang-list">
                    <?php foreach ($languageStats as $stat): ?>
                        <div class="lang-item">
                            <span><?php echo h($stat['name']); ?></span>
                            <span><?php echo h($stat['count']); ?> чел.</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <h2>📋 Список пользователей</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>ФИО</th><th>Email</th><th>Телефон</th><th>Языки</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo h($user['id']); ?></td>
                <td><?php echo h($user['full_name']); ?></td>
                <td><?php echo h($user['email']); ?></td>
                <td><?php echo h($user['phone']); ?></td>
                <td><?php echo h($user['languages_list'] ?: '-'); ?></td>
                <td>
                    <a href="admin_edit.php?id=<?php echo h($user['id']); ?>" class="btn-edit">✏️ Редактировать</a>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Удалить?')">
                        <input type="hidden" name="id" value="<?php echo h($user['id']); ?>">
                        <button type="submit" name="delete" class="btn-delete">🗑️ Удалить</button>
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