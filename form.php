<?php
// form.php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета разработчика</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>📝 Анкета разработчика</h1>
    
    <div class="admin-link">
        <a href="admin.php" class="admin-btn">👑 Админ-панель</a>
    </div>
    
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $message): ?>
            <?php echo $message; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (!empty($_SESSION['login'])): ?>
        <div class="user-info">
            <span>👤 Вы вошли как <strong><?php echo htmlspecialchars($_SESSION['login']); ?></strong></span>
            <a href="logout.php" class="logout-link">🚪 Выйти</a>
        </div>
    <?php else: ?>
        <div class="user-info">
            <a href="login.php" class="login-link">🔐 Уже есть учетная запись? Войти</a>
        </div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <div class="form-group">
            <label for="fio">ФИО:</label>
            <input type="text" id="fio" name="fio" 
                   value="<?php echo htmlspecialchars($values['fio']); ?>"
                   class="<?php echo !empty($errors['fio']) ? 'error' : ''; ?>"
                   placeholder="Иванов Иван Иванович">
        </div>

        <div class="form-group">
            <label for="phone">Телефон:</label>
            <input type="tel" id="phone" name="phone" 
                   value="<?php echo htmlspecialchars($values['phone']); ?>"
                   class="<?php echo !empty($errors['phone']) ? 'error' : ''; ?>"
                   placeholder="+7 (123) 456-78-90">
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" 
                   value="<?php echo htmlspecialchars($values['email']); ?>"
                   class="<?php echo !empty($errors['email']) ? 'error' : ''; ?>"
                   placeholder="example@mail.ru">
        </div>

        <div class="form-group">
            <label for="birthdate">Дата рождения:</label>
            <input type="date" id="birthdate" name="birthdate" 
                   value="<?php echo htmlspecialchars($values['birthdate']); ?>"
                   class="<?php echo !empty($errors['birthdate']) ? 'error' : ''; ?>">
            <small>Должно быть 18 лет и старше</small>
        </div>

        <div class="form-group">
            <label>Пол:</label>
            <div class="radio-group">
                <input type="radio" id="male" name="gender" value="male" 
                       <?php echo ($values['gender'] == 'male') ? 'checked' : ''; ?>>
                <label for="male">👨 Мужской</label>
            </div>
            <div class="radio-group">
                <input type="radio" id="female" name="gender" value="female" 
                       <?php echo ($values['gender'] == 'female') ? 'checked' : ''; ?>>
                <label for="female">👩 Женский</label>
            </div>
        </div>

        <div class="form-group">
            <label for="languages">Любимый язык программирования:</label>
            <select id="languages" name="languages[]" multiple size="6">
                <option value="Pascal" <?php echo in_array('Pascal', $values['languages']) ? 'selected' : ''; ?>>Pascal</option>
                <option value="C" <?php echo in_array('C', $values['languages']) ? 'selected' : ''; ?>>C</option>
                <option value="C++" <?php echo in_array('C++', $values['languages']) ? 'selected' : ''; ?>>C++</option>
                <option value="JavaScript" <?php echo in_array('JavaScript', $values['languages']) ? 'selected' : ''; ?>>JavaScript</option>
                <option value="PHP" <?php echo in_array('PHP', $values['languages']) ? 'selected' : ''; ?>>PHP</option>
                <option value="Python" <?php echo in_array('Python', $values['languages']) ? 'selected' : ''; ?>>Python</option>
                <option value="Java" <?php echo in_array('Java', $values['languages']) ? 'selected' : ''; ?>>Java</option>
                <option value="Haskell" <?php echo in_array('Haskell', $values['languages']) ? 'selected' : ''; ?>>Haskell</option>
                <option value="Clojure" <?php echo in_array('Clojure', $values['languages']) ? 'selected' : ''; ?>>Clojure</option>
                <option value="Prolog" <?php echo in_array('Prolog', $values['languages']) ? 'selected' : ''; ?>>Prolog</option>
                <option value="Scala" <?php echo in_array('Scala', $values['languages']) ? 'selected' : ''; ?>>Scala</option>
                <option value="Go" <?php echo in_array('Go', $values['languages']) ? 'selected' : ''; ?>>Go</option>
            </select>
            <small>💡 Для выбора нескольких языков зажмите Ctrl (Cmd) и кликайте</small>
        </div>

        <div class="form-group">
            <label for="bio">Биография:</label>
            <textarea id="bio" name="bio" rows="5" 
                      placeholder="Расскажите о себе..."><?php echo htmlspecialchars($values['bio']); ?></textarea>
        </div>

        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" id="contract" name="contract" value="1" 
                       <?php echo $values['contract'] ? 'checked' : ''; ?>>
                <label for="contract">📄 С контрактом ознакомлен и согласен</label>
            </div>
        </div>

        <button type="submit">💾 Сохранить</button>
    </form>
</div>
</body>
</html>