<?php
session_start();
require_once 'db_connect.php';

function registerUser($username, $email, $password) {
    global $pdo;
    
    try {
        if (empty($username) || empty($email) || empty($password)) {
            error_log("Пустые данные: username=$username, email=$email");
            return ['success' => false, 'message' => 'Все поля обязательны для заполнения'];
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM игроки WHERE Имя = ? OR Почта = ?");
        if (!$stmt->execute([$username, $email])) {
            error_log("Ошибка выполнения проверки пользователя: " . implode(", ", $stmt->errorInfo()));
            return ['success' => false, 'message' => 'Ошибка при проверке данных'];
        }
        
        if ($stmt->fetchColumn() > 0) {
            error_log("Пользователь уже существует: $username или $email");
            return ['success' => false, 'message' => 'Пользователь с таким именем или email уже существует'];
        }

        $stmt = $pdo->prepare("INSERT INTO игроки (Имя, Почта, Пароль, Статус, Роль) VALUES (?, ?, ?, 'Активен', 'Игрок')");
        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        if (!$stmt->execute([$username, $email, $password])) {
            error_log("Ошибка выполнения INSERT: " . implode(", ", $stmt->errorInfo()));
            return ['success' => false, 'message' => 'Ошибка при сохранении пользователя'];
        }
        
        $lastId = $pdo->lastInsertId();
        if (!$lastId) {
            error_log("Не удалось получить ID нового пользователя");
            return ['success' => false, 'message' => 'Ошибка при регистрации'];
        }
        
        error_log("Успешная регистрация: ID=$lastId, username=$username");
        return ['success' => true, 'message' => 'Регистрация успешна', 'user_id' => $lastId];
        
    } catch(PDOException $e) {
        $errorInfo = isset($stmt) ? implode(", ", $stmt->errorInfo()) : 'Нет информации';
        error_log("Ошибка PDO: " . $e->getMessage() . " | Error Info: " . $errorInfo);
        return ['success' => false, 'message' => 'Системная ошибка при регистрации'];
    }
}

function loginUser($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM игроки WHERE Имя = ?");
        if (!$stmt->execute([$username])) {
            error_log("Ошибка выполнения запроса: " . implode(", ", $stmt->errorInfo()));
            return ['success' => false, 'message' => 'Ошибка при входе в систему'];
        }
        
        $user = $stmt->fetch();
        if (!$user) {
            error_log("Пользователь не найден: $username");
            return ['success' => false, 'message' => 'Неверное имя пользователя или пароль'];
        }
        
        // Отладочная информация
        error_log("Попытка входа: $username");
        error_log("Хеш пароля в БД: " . $user['Пароль']);
        error_log("Длина хеша: " . strlen($user['Пароль']));
        
        if (!($password == $user['Пароль'])) {
            error_log("Неверный пароль для пользователя: $username");
            return ['success' => false, 'message' => 'Неверное имя пользователя или пароль'];
        }
        
        if ($user['Статус'] === 'Заблок.') {
            error_log("Попытка входа в заблокированный аккаунт: $username");
            return ['success' => false, 'message' => 'Ваш аккаунт заблокирован'];
        }
        
        $_SESSION['user_id'] = $user['Код_Игрока'];
        $_SESSION['username'] = $user['Имя'];
        $_SESSION['role'] = $user['Роль'];
        $_SESSION['email'] = $user['Почта'];
        
        error_log("Успешный вход: $username");
        return ['success' => true, 'message' => 'Вход выполнен успешно'];
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Ошибка при входе в систему'];
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireRole($role) {
    requireLogin();
    if (getUserRole() !== $role) {
        header("Location: index.php");
        exit();
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM игроки WHERE Код_Игрока = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        error_log("Get user error: " . $e->getMessage());
        return null;
    }
}
?>