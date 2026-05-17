<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

session_start();

// Information Disclosure: отключаем вывод ошибок в production
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Подключение к БД
try {
    $db = new PDO("mysql:host=localhost;dbname=u82361;charset=utf8", 'u82361', '9967838');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Ошибка подключения к базе данных');
}

// Разрешенные языки
$allowedLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];

// Функция для генерации уникального логина
function generateUniqueLogin($db) {
    $prefixes = ['user', 'dev', 'coder', 'web', 'php'];
    $prefix = $prefixes[array_rand($prefixes)];
    
    do {
        $login = $prefix . rand(100, 9999);
        $stmt = $db->prepare("SELECT COUNT(*) FROM application WHERE login = ?");
        $stmt->execute([$login]);
        $exists = $stmt->fetchColumn();
    } while ($exists > 0);
    
    return $login;
}

// Функция для генерации пароля
function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

// GET запрос - показываем форму
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();
    $errors = array();
    $values = array(
        'fio' => '',
        'phone' => '',
        'email' => '',
        'birthdate' => '',
        'gender' => '',
        'bio' => '',
        'contract' => false,
        'languages' => array()
    );

    // Проверяем cookie успешного сохранения
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = '<div class="success">✅ Спасибо, результаты сохранены.</div>';
        
        if (!empty($_COOKIE['new_login']) && !empty($_COOKIE['new_pass'])) {
            $messages[] = sprintf(
                '<div class="success">🔐 Сгенерированы учетные данные:<br>
                 Логин: <strong>%s</strong><br>
                 Пароль: <strong>%s</strong><br>
                 <a href="login.php">Войти</a> для редактирования данных.</div>',
                htmlspecialchars($_COOKIE['new_login']),
                htmlspecialchars($_COOKIE['new_pass'])
            );
            setcookie('new_login', '', 100000);
            setcookie('new_pass', '', 100000);
        }
    }

    // Проверяем авторизацию через сессию
    if (!empty($_SESSION['login']) && !empty($_SESSION['uid'])) {
        $messages[] = sprintf('<div class="success">👋 Вы вошли как %s. Можете редактировать данные.</div>', 
            htmlspecialchars($_SESSION['login']));
        
        try {
            $stmt = $db->prepare("SELECT full_name, phone, email, birth_date, gender, biography, agreed FROM application WHERE id = ?");
            $stmt->execute([$_SESSION['uid']]);
            $userData = $stmt->fetch();
            
            if ($userData) {
                $values['fio'] = htmlspecialchars($userData['full_name']);
                $values['phone'] = htmlspecialchars($userData['phone']);
                $values['email'] = htmlspecialchars($userData['email']);
                $values['birthdate'] = $userData['birth_date'];
                $values['gender'] = $userData['gender'];
                $values['bio'] = htmlspecialchars($userData['biography']);
                $values['contract'] = (bool)$userData['agreed'];
                
                $stmtLang = $db->prepare("SELECT pl.name FROM application_language al 
                                         JOIN programming_language pl ON al.language_id = pl.id 
                                         WHERE al.application_id = ?");
                $stmtLang->execute([$_SESSION['uid']]);
                $values['languages'] = $stmtLang->fetchAll(PDO::FETCH_COLUMN);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $messages[] = '<div class="error">❌ Ошибка загрузки данных</div>';
        }
    } else {
        $errorFields = ['fio', 'phone', 'email', 'birthdate', 'gender', 'languages', 'bio', 'contract'];
        foreach ($errorFields as $field) {
            $errors[$field] = !empty($_COOKIE[$field . '_error']);
            if ($errors[$field]) {
                setcookie($field . '_error', '', 100000);
            }
        }

        if ($errors['fio']) $messages[] = '<div class="error">❌ Ошибка в поле "ФИО"</div>';
        if ($errors['phone']) $messages[] = '<div class="error">❌ Ошибка в поле "Телефон"</div>';
        if ($errors['email']) $messages[] = '<div class="error">❌ Ошибка в поле "Email"</div>';
        if ($errors['birthdate']) $messages[] = '<div class="error">❌ Ошибка в поле "Дата рождения" (должно быть 18+)</div>';
        if ($errors['gender']) $messages[] = '<div class="error">❌ Ошибка в поле "Пол"</div>';
        if ($errors['languages']) $messages[] = '<div class="error">❌ Выберите хотя бы один язык</div>';
        if ($errors['bio']) $messages[] = '<div class="error">❌ Ошибка в поле "Биография"</div>';
        if ($errors['contract']) $messages[] = '<div class="error">❌ Подтвердите ознакомление с контрактом</div>';

        $values['fio'] = empty($_COOKIE['fio_value']) ? '' : htmlspecialchars($_COOKIE['fio_value']);
        $values['phone'] = empty($_COOKIE['phone_value']) ? '' : htmlspecialchars($_COOKIE['phone_value']);
        $values['email'] = empty($_COOKIE['email_value']) ? '' : htmlspecialchars($_COOKIE['email_value']);
        $values['birthdate'] = empty($_COOKIE['birthdate_value']) ? '' : $_COOKIE['birthdate_value'];
        $values['gender'] = empty($_COOKIE['gender_value']) ? '' : $_COOKIE['gender_value'];
        $values['bio'] = empty($_COOKIE['bio_value']) ? '' : htmlspecialchars($_COOKIE['bio_value']);
        $values['contract'] = !empty($_COOKIE['contract_value']);
        $values['languages'] = empty($_COOKIE['languages_value']) ? array() : json_decode($_COOKIE['languages_value'], true);
        if (!is_array($values['languages'])) $values['languages'] = array();
    }

    include('form.php');
    exit();
}

// POST запрос - валидация и сохранение
else {
    $errors = false;

    // 1. ФИО
    if (empty($_POST['fio'])) {
        setcookie('fio_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]+$/u', $_POST['fio']) || strlen($_POST['fio']) > 150) {
        setcookie('fio_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    }
    setcookie('fio_value', $_POST['fio'], time() + 365 * 24 * 60 * 60);

    // 2. Телефон
    if (empty($_POST['phone'])) {
        setcookie('phone_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } elseif (!preg_match('/^[\d\s\+\(\)\-]{10,20}$/', $_POST['phone'])) {
        setcookie('phone_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    }
    setcookie('phone_value', $_POST['phone'], time() + 365 * 24 * 60 * 60);

    // 3. Email
    if (empty($_POST['email'])) {
        setcookie('email_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    }
    setcookie('email_value', $_POST['email'], time() + 365 * 24 * 60 * 60);

    // 4. Дата рождения (должно быть 18+)
    if (empty($_POST['birthdate'])) {
        setcookie('birthdate_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } else {
        $birthdate = DateTime::createFromFormat('Y-m-d', $_POST['birthdate']);
        if (!$birthdate || $birthdate->diff(new DateTime())->y < 18) {
            setcookie('birthdate_error', '1', time() + 24 * 60 * 60);
            $errors = true;
        }
    }
    setcookie('birthdate_value', $_POST['birthdate'], time() + 365 * 24 * 60 * 60);

    // 5. Пол
    if (empty($_POST['gender']) || !in_array($_POST['gender'], ['male', 'female'])) {
        setcookie('gender_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    }
    setcookie('gender_value', $_POST['gender'], time() + 365 * 24 * 60 * 60);

    // 6. Языки
    if (empty($_POST['languages'])) {
        setcookie('languages_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    } else {
        foreach ($_POST['languages'] as $lang) {
            if (!in_array($lang, $allowedLanguages)) {
                setcookie('languages_error', '1', time() + 24 * 60 * 60);
                $errors = true;
                break;
            }
        }
    }
    setcookie('languages_value', json_encode($_POST['languages']), time() + 365 * 24 * 60 * 60);

    // 7. Биография
    if (empty($_POST['bio']) || strlen($_POST['bio']) > 5000) {
        setcookie('bio_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    }
    setcookie('bio_value', $_POST['bio'], time() + 365 * 24 * 60 * 60);

    // 8. Контракт
    if (empty($_POST['contract'])) {
        setcookie('contract_error', '1', time() + 24 * 60 * 60);
        $errors = true;
    }
    setcookie('contract_value', $_POST['contract'], time() + 365 * 24 * 60 * 60);

    if ($errors) {
        header('Location: index.php');
        exit();
    }

    $errorFields = ['fio', 'phone', 'email', 'birthdate', 'gender', 'languages', 'bio', 'contract'];
    foreach ($errorFields as $field) {
        setcookie($field . '_error', '', 100000);
    }

    try {
        if (!empty($_SESSION['login']) && !empty($_SESSION['uid'])) {
            $stmt = $db->prepare("UPDATE application SET full_name = ?, phone = ?, email = ?, 
                                  birth_date = ?, gender = ?, biography = ?, agreed = ? WHERE id = ?");
            $stmt->execute([
                $_POST['fio'], $_POST['phone'], $_POST['email'],
                $_POST['birthdate'], $_POST['gender'], $_POST['bio'], 1, $_SESSION['uid']
            ]);
            
            $stmt = $db->prepare("DELETE FROM application_language WHERE application_id = ?");
            $stmt->execute([$_SESSION['uid']]);
            
            $stmt = $db->prepare("INSERT INTO application_language (application_id, language_id) 
                                  VALUES (?, (SELECT id FROM programming_language WHERE name = ?))");
            foreach ($_POST['languages'] as $lang) {
                $stmt->execute([$_SESSION['uid'], $lang]);
            }
        } else {
            $login = generateUniqueLogin($db);
            $password = generatePassword();
            $password_hash = md5($password);
            
            $stmt = $db->prepare("INSERT INTO application (full_name, phone, email, birth_date, 
                                  gender, biography, agreed, login, password_hash) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['fio'], $_POST['phone'], $_POST['email'],
                $_POST['birthdate'], $_POST['gender'], $_POST['bio'], 1, $login, $password_hash
            ]);
            
            $applicationId = $db->lastInsertId();
            
            $stmt = $db->prepare("INSERT INTO application_language (application_id, language_id) 
                                  VALUES (?, (SELECT id FROM programming_language WHERE name = ?))");
            foreach ($_POST['languages'] as $lang) {
                $stmt->execute([$applicationId, $lang]);
            }
            
            setcookie('new_login', $login, time() + 60);
            setcookie('new_pass', $password, time() + 60);
        }
        
        setcookie('save', '1', time() + 24 * 60 * 60);
        header('Location: index.php');
        exit();
        
    } catch (PDOException $e) {
        error_log($e->getMessage());
        setcookie('db_error', '1', time() + 24 * 60 * 60);
        header('Location: index.php');
        exit();
    }
}
?>