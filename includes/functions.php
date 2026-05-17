<?php
// includes/functions.php
require_once 'db.php';

// Функция для защиты от XSS
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Получение всех языков (исправлено: SQL Injection)
function getAllLanguages() {
    global $db;
    $stmt = $db->prepare("SELECT * FROM programming_language ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Получение языков пользователя
function getUserLanguages($userId) {
    global $db;
    $stmt = $db->prepare("SELECT pl.name FROM application_language al JOIN programming_language pl ON al.language_id = pl.id WHERE al.application_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Получение пользователя по ID (добавлено)
function getUserById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM application WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Получение статистики по языкам (исправлено: SQL Injection)
function getLanguageStats() {
    global $db;
    $stmt = $db->prepare("
        SELECT pl.name, COUNT(al.language_id) as count 
        FROM programming_language pl
        LEFT JOIN application_language al ON pl.id = al.language_id
        GROUP BY pl.id, pl.name
        ORDER BY count DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Проверка авторизации администратора (исправлено: SQL Injection)
function checkAdminAuth() {
    if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
        return false;
    }
    
    global $db;
    $stmt = $db->prepare("SELECT password_hash FROM admins WHERE login = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch();
    
    return $admin && md5($_SERVER['PHP_AUTH_PW']) == $admin['password_hash'];
}
?>