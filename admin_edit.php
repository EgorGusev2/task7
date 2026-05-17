<?php
// admin_edit.php
require_once 'includes/functions.php';

// Information Disclosure: отключаем вывод ошибок
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Проверка авторизации
if (!checkAdminAuth()) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    echo '<h1>401 Требуется авторизация</h1>';
    exit();
}

$id = $_GET['id'] ?? 0;
$user = getUserById($id);
if (!$user) {
    header('Location: admin.php');
    exit();
}

$userLanguages = getUserLanguages($id);
$allLanguages = getAllLanguages();

// Обработка сохранения (исправлено: SQL Injection)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fio = $_POST['fio'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $bio = $_POST['bio'];
    $languages = $_POST['languages'] ?? [];
    
    if (!empty($fio) && !empty($email) && !empty($languages)) {
        $stmt = $db->prepare("UPDATE application SET full_name=?, phone=?, email=?, birth_date=?, gender=?, biography=? WHERE id=?");
        $stmt->execute([$fio, $phone, $email, $birthdate, $gender, $bio, $id]);
        
        $stmt = $db->prepare("DELETE FROM application_language WHERE application_id=?");
        $stmt->execute([$id]);
        
        $stmt = $db->prepare("INSERT INTO application_language (application_id, language_id) VALUES (?, (SELECT id FROM programming_language WHERE name=?))");
        foreach ($languages as $lang) {
            $stmt->execute([$id, $lang]);
        }
        
        header('Location: admin.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование пользователя</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>✏️ Редактирование</h1>
    
    <form method="POST">
        <div class="form-group">
            <label>ФИО:</label>
            <input type="text" name="fio" value="<?php echo h($user['full_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Телефон:</label>
            <input type="text" name="phone" value="<?php echo h($user['phone']); ?>">
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo h($user['email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Дата рождения:</label>
            <input type="date" name="birthdate" value="<?php echo h($user['birth_date']); ?>">
        </div>
        
        <div class="form-group">
            <label>Пол:</label>
            <select name="gender">
                <option value="male" <?php echo $user['gender'] == 'male' ? 'selected' : ''; ?>>Мужской</option>
                <option value="female" <?php echo $user['gender'] == 'female' ? 'selected' : ''; ?>>Женский</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Языки программирования (Ctrl+клик для выбора нескольких):</label>
            <select name="languages[]" multiple size="6">
                <?php foreach ($allLanguages as $lang): ?>
                    <option value="<?php echo h($lang['name']); ?>" 
                        <?php echo in_array($lang['name'], $userLanguages) ? 'selected' : ''; ?>>
                        <?php echo h($lang['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Биография:</label>
            <textarea name="bio" rows="4"><?php echo h($user['biography']); ?></textarea>
        </div>
        
        <button type="submit">💾 Сохранить</button>
        <a href="admin.php" style="margin-left: 10px;">Отмена</a>
    </form>
</div>
</body>
</html>