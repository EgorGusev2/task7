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

$availableStudios = ['Rock Studio', 'Jazz Hall', 'Electronic Lab', 'Acoustic Room', 'Recording Suite'];

function generateUniqueLogin($db) {
    $prefixes = ['musician', 'rocker', 'drummer', 'singer', 'guitarist'];
    $prefix = $prefixes[array_rand($prefixes)];
    do {
        $login = $prefix . rand(100, 9999);
        $stmt = $db->prepare("SELECT COUNT(*) FROM rehearsal_booking WHERE login = ?");
        $stmt->execute([$login]);
    } while ($stmt->fetchColumn() > 0);
    return $login;
}

function generatePassword() {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, 8);
}

// GET — показываем форму
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $values = [
        'full_name' => $_COOKIE['full_name'] ?? '',
        'phone' => $_COOKIE['phone'] ?? '',
        'booking_date' => $_COOKIE['booking_date'] ?? '',
        'booking_time' => $_COOKIE['booking_time'] ?? '',
        'studio_name' => $_COOKIE['studio_name'] ?? 'Rock Studio',
        'special_requests' => $_COOKIE['special_requests'] ?? '',
        'agreed' => false
    ];
    
    if (!empty($_SESSION['booking_id'])) {
        $stmt = $db->prepare("SELECT * FROM rehearsal_booking WHERE id = ?");
        $stmt->execute([$_SESSION['booking_id']]);
        $userData = $stmt->fetch();
        if ($userData) {
            $values['full_name'] = $userData['full_name'];
            $values['phone'] = $userData['phone'];
            $values['booking_date'] = $userData['booking_date'];
            $values['booking_time'] = $userData['booking_time'];
            $values['studio_name'] = $userData['studio_name'];
            $values['special_requests'] = $userData['special_requests'];
            $values['agreed'] = true;
        }
    }
    
    $showCredentials = !empty($_COOKIE['show_login']) && !empty($_COOKIE['show_pass']);
    $newLogin = $_COOKIE['show_login'] ?? '';
    $newPass = $_COOKIE['show_pass'] ?? '';
    
    if ($showCredentials) {
        setcookie('show_login', '', time() - 3600);
        setcookie('show_pass', '', time() - 3600);
    }
    
    include 'form.php';
    exit();
}

// POST — сохраняем с проверкой CSRF
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF защита
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Ошибка CSRF: неверный токен');
    }
    
    $errors = [];
    
    if (empty($_POST['full_name'])) $errors[] = 'full_name';
    if (empty($_POST['phone'])) $errors[] = 'phone';
    if (empty($_POST['booking_date'])) $errors[] = 'booking_date';
    if (empty($_POST['booking_time'])) $errors[] = 'booking_time';
    if (empty($_POST['studio_name'])) $errors[] = 'studio_name';
    if (empty($_POST['agreed'])) $errors[] = 'agreed';
    
    setcookie('full_name', $_POST['full_name'], time() + 86400*365);
    setcookie('phone', $_POST['phone'], time() + 86400*365);
    setcookie('booking_date', $_POST['booking_date'], time() + 86400*365);
    setcookie('booking_time', $_POST['booking_time'], time() + 86400*365);
    setcookie('studio_name', $_POST['studio_name'], time() + 86400*365);
    setcookie('special_requests', $_POST['special_requests'], time() + 86400*365);
    
    if (!empty($errors)) {
        foreach ($errors as $err) {
            setcookie($err . '_error', '1', time() + 86400);
        }
        header('Location: index.php');
        exit();
    }
    
    foreach(['full_name','phone','booking_date','booking_time','studio_name','agreed'] as $f) {
        setcookie($f . '_error', '', time() - 3600);
    }
    
    try {
        if (!empty($_SESSION['booking_id'])) {
            $stmt = $db->prepare("UPDATE rehearsal_booking SET full_name = ?, phone = ?, booking_date = ?, booking_time = ?, studio_name = ?, special_requests = ? WHERE id = ?");
            $stmt->execute([
                $_POST['full_name'],
                $_POST['phone'],
                $_POST['booking_date'],
                $_POST['booking_time'],
                $_POST['studio_name'],
                $_POST['special_requests'],
                $_SESSION['booking_id']
            ]);
            
            setcookie('save_success', '1', time() + 60);
            header('Location: index.php');
            exit();
        } else {
            $login = generateUniqueLogin($db);
            $password = generatePassword();
            $password_hash = md5($password);
            
            $stmt = $db->prepare("INSERT INTO rehearsal_booking (full_name, phone, login, password_hash, booking_date, booking_time, studio_name, special_requests, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([
                $_POST['full_name'],
                $_POST['phone'],
                $login,
                $password_hash,
                $_POST['booking_date'],
                $_POST['booking_time'],
                $_POST['studio_name'],
                $_POST['special_requests']
            ]);
            
            setcookie('show_login', $login, time() + 60);
            setcookie('show_pass', $password, time() + 60);
            setcookie('save_success', '1', time() + 60);
            
            foreach(['full_name','phone','booking_date','booking_time','studio_name','special_requests'] as $f) {
                setcookie($f, '', time() - 3600);
            }
            
            header('Location: index.php');
            exit();
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        die('Ошибка при сохранении данных');
    }
}
?>
