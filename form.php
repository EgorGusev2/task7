<?php
// form.php - шаблон формы с CSRF-защитой
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎸 Запись на репетицию</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>🎸 Запись на репетицию</h1>
    
    <div class="admin-link">
        <a href="admin.php" class="admin-btn">👑 Админ-панель</a>
    </div>
    
    <?php if (!empty($_COOKIE['save_success'])): ?>
        <?php setcookie('save_success', '', time() - 3600); ?>
        <div class="success">✅ Заявка успешно отправлена!</div>
    <?php endif; ?>
    
    <?php if ($showCredentials): ?>
        <div class="credentials-box">
            <h3>🔐 Ваши учетные данные для входа</h3>
            <p><strong>Логин:</strong> <span class="cred-value"><?= h($newLogin) ?></span></p>
            <p><strong>Пароль:</strong> <span class="cred-value"><?= h($newPass) ?></span></p>
            <p class="cred-note">✏️ Сохраните эти данные! Они понадобятся для редактирования записи.</p>
            <a href="login.php" class="cred-login-btn">Войти →</a>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($_SESSION['booking_id'])): ?>
        <div class="user-info">
            <span>👤 Вы вошли как <strong><?= h($_SESSION['login']) ?></strong></span>
            <a href="logout.php" class="logout-link">🚪 Выйти</a>
        </div>
        <div class="edit-info">
            💡 <strong>Режим редактирования</strong> — измените данные ниже, и ваша запись обновится.
        </div>
    <?php else: ?>
        <div class="user-info">
            <a href="login.php" class="login-link">🔐 Уже есть аккаунт? Войти</a>
            <span style="margin-left: 15px; color: #666;">|</span>
            <span style="color: #666;">📝 Нет аккаунта? Заполните форму — логин и пароль появятся после отправки</span>
        </div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <?= csrfField() ?>
        
        <div class="form-group">
            <label>📝 ФИО:</label>
            <input type="text" name="full_name" value="<?= h($values['full_name']) ?>" required
                   class="<?= !empty($_COOKIE['full_name_error']) ? 'error' : '' ?>">
        </div>
        
        <div class="form-group">
            <label>📞 Телефон:</label>
            <input type="text" name="phone" value="<?= h($values['phone']) ?>" required
                   class="<?= !empty($_COOKIE['phone_error']) ? 'error' : '' ?>">
        </div>
        
        <div class="form-group">
            <label>📅 Дата репетиции:</label>
            <input type="date" name="booking_date" value="<?= h($values['booking_date']) ?>" required
                   class="<?= !empty($_COOKIE['booking_date_error']) ? 'error' : '' ?>">
        </div>
        
        <div class="form-group">
            <label>⏰ Время репетиции:</label>
            <input type="time" name="booking_time" value="<?= h($values['booking_time']) ?>" required
                   class="<?= !empty($_COOKIE['booking_time_error']) ? 'error' : '' ?>">
            <small>Работаем с 10:00 до 22:00</small>
        </div>
        
        <div class="form-group">
            <label>🏢 Студия:</label>
            <select name="studio_name" required>
                <?php foreach ($availableStudios as $studio): ?>
                    <option value="<?= h($studio) ?>" <?= $values['studio_name'] == $studio ? 'selected' : '' ?>>
                        <?= h($studio) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>💭 Пожелания к репетиции:</label>
            <textarea name="special_requests" rows="3" placeholder="Нужное оборудование, особые условия..."><?= h($values['special_requests']) ?></textarea>
            <small>Необязательное поле</small>
        </div>
        
        <div class="form-group">
            <label class="checkbox">
                <input type="checkbox" name="agreed" value="1" <?= $values['agreed'] ? 'checked' : '' ?>
                       class="<?= !empty($_COOKIE['agreed_error']) ? 'error' : '' ?>">
                📄 Ознакомлен с правилами записи и согласен
            </label>
        </div>
        
        <button type="submit">
            <?= !empty($_SESSION['booking_id']) ? '✏️ Обновить запись' : '🎸 Записаться на репетицию' ?>
        </button>
    </form>
    
    <?php if (!empty($_SESSION['booking_id'])): ?>
        <div style="margin-top: 20px; text-align: center;">
            <a href="my_bookings.php" class="my-bookings-link">📋 Посмотреть мою запись</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>