<?php
require_once 'header.php';
require_once 'auth.php';

requireLogin();

$user = getCurrentUser();
if (!$user) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Валидация
    if (empty($username)) {
        $error = 'Имя пользователя обязательно';
    } elseif (empty($email)) {
        $error = 'Email обязателен';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Неверный формат email';
    } else {
        try {
            global $pdo;
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM игроки WHERE (Имя = ? OR Почта = ?) AND Код_Игрока != ?");
            $stmt->execute([$username, $email, $user['Код_Игрока']]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Имя пользователя или email уже заняты';
            } else {
                $stmt = $pdo->prepare("UPDATE игроки SET Имя = ?, Почта = ? WHERE Код_Игрока = ?");
                $stmt->execute([$username, $email, $user['Код_Игрока']]);

                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                
                $success = 'Профиль успешно обновлен';
                $user = getCurrentUser(); 
            }
        } catch(PDOException $e) {
            error_log("Error updating profile: " . $e->getMessage());
            $error = 'Ошибка при обновлении профиля';
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Боковая панель профиля -->
            <div class="card profile-sidebar mb-4">
                <div class="card-body text-center">
                    <img src="<?php echo htmlspecialchars($user['аватар']); ?>" alt="Character Avatar" class="img-fluid rounded-circle mb-2">
                    <h4><?php echo htmlspecialchars($user['Имя']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($user['Роль']); ?></p>
                    
                    <div class="list-group">
                        <a href="profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i>Мой профиль
                        </a>
                        <a href="edit_profile.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-edit me-2"></i>Редактировать профиль
                        </a>
                        <a href="create_character.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus-circle me-2"></i>Создать персонажа
                        </a>
                        <a href="change_password.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-lock me-2"></i>Сменить пароль
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Выйти
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Основное содержимое -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Редактирование профиля</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Имя пользователя</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['Имя']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['Почта']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Статус</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['Статус']); ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Роль</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['Роль']); ?>" readonly>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="profile.php" class="btn btn-secondary me-md-2">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>