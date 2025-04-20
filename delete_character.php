<?php
require_once 'auth.php';

requireLogin();

$user = getCurrentUser();
if (!$user) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: profile.php");
    exit();
}

$character_id = $_POST['character_id'] ?? 0;
if (!$character_id) {
    header("Location: profile.php");
    exit();
}

try {
    global $pdo;
    
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM игроки_персонажи 
        WHERE Код_Игрока = ? AND Код_Персонажа = ?
    ");
    $stmt->execute([$user['Код_Игрока'], $character_id]);
    
    if ($stmt->fetchColumn() == 0) {
        header("Location: profile.php");
        exit();
    }
    
    $stmt = $pdo->prepare("
        DELETE FROM игроки_персонажи 
        WHERE Код_Персонажа = ? AND Код_Игрока = ?
    ");
    $stmt->execute([$character_id, $user['Код_Игрока']]);
    
    $stmt = $pdo->prepare("
        DELETE FROM инвентари 
        WHERE Код_Персонажа = ?
    ");
    $stmt->execute([$character_id]);
    
    $stmt = $pdo->prepare("
        DELETE FROM инвентари_квесты 
        WHERE Код_Инвенторя IN (
            SELECT Код_Инвенторя FROM инвентари WHERE Код_Персонажа = ?
        )
    ");
    $stmt->execute([$character_id]);
    
    $stmt = $pdo->prepare("
        DELETE FROM персонаж 
        WHERE Код_Персонажа = ?
    ");
    $stmt->execute([$character_id]);
    
    $pdo->commit();
    
    $_SESSION['success_message'] = 'Персонаж успешно удален';
    header("Location: profile.php");
    exit();
    
} catch(PDOException $e) {
    $pdo->rollBack();
    error_log("Delete character error: " . $e->getMessage());
    
    $_SESSION['error_message'] = 'Ошибка при удалении персонажа';
    header("Location: character.php?id=" . $character_id);
    exit();
}