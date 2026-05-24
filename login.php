<?php
session_start();
require_once 'includes/security.php';
secureErrorHandling();

try {
    $db = new PDO("mysql:host=localhost;dbname=u82361;charset=utf8", 'u82361', '9967838');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Ошибка подключения к базе данных');
}

if (!empty($_SESSION['booking_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Вход в систему</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="container">
            <h1>🔐 Вход для редактирования записи</h1>
            <?php if (!empty($_GET['error'])): ?>
                <div class="error">❌ Неверный логин или пароль</div>
            <?php endif; ?>
            <form action="" method="post">
                <div class="form-group">
                    <label>Логин:</label>
                    <input type="text" name="login" required placeholder="Введите ваш логин">
                </div>
                <div class="form-group">
                    <label>Пароль:</label>
                    <input type="password" name="password" required placeholder="Введите пароль">
                </div>
                <button type="submit">🚪 Войти</button>
            </form>
            <p style="margin-top: 20px; text-align: center;">
                <a href="index.php">← Вернуться к записи</a>
            </p>
        </div>
    </body>
    </html>
    <?php
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $password_hash = md5($password);
    
    $stmt = $db->prepare("SELECT id, full_name, login FROM rehearsal_booking WHERE login = ? AND password_hash = ?");
    $stmt->execute([$login, $password_hash]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['booking_id'] = $user['id'];
        $_SESSION['login'] = $user['login'];
        $_SESSION['full_name'] = $user['full_name'];
        header('Location: index.php');
        exit();
    } else {
        header('Location: login.php?error=1');
        exit();
    }
}
?>