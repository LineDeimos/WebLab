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
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Новый пароль и подтверждение не совпадают';
    } elseif (strlen($new_password) < 8) {
        $error = 'Пароль должен содержать минимум 8 символов';
    } else {
        try {
            global $pdo;
            
            $stmt = $pdo->prepare("SELECT Пароль FROM игроки WHERE Код_Игрока = ?");
            $stmt->execute([$user['Код_Игрока']]);
            $db_password = $stmt->fetchColumn();
            
            if (!$db_password || !($current_password == $db_password)) {
                $error = 'Текущий пароль введен неверно';
            } else {
                
                $stmt = $pdo->prepare("UPDATE игроки SET Пароль = ? WHERE Код_Игрока = ?");
                $result = $stmt->execute([$new_password, $user['Код_Игрока']]);
                
                if ($result) {
                    $success = 'Пароль успешно изменен';
                } else {
                    $error = 'Ошибка при обновлении пароля';
                }
            }
        } catch(PDOException $e) {
            error_log("Password change error: " . $e->getMessage());
            $error = 'Системная ошибка при изменении пароля';
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
                        <a href="edit_profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-edit me-2"></i>Редактировать профиль
                        </a>
                        <a href="create_character.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus-circle me-2"></i>Создать персонажа
                        </a>
                        <a href="change_password.php" class="list-group-item list-group-item-action active">
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
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Смена пароля</h4>
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
                            <label for="current_password" class="form-label">Текущий пароль</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Новый пароль</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="form-text">Минимум 8 символов</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Подтвердите новый пароль</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="profile.php" class="btn btn-secondary me-md-2">Отмена</a>
                            <button type="submit" class="btn btn-primary">Изменить пароль</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Дополнительные рекомендации по паролю -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5>Рекомендации по созданию надежного пароля:</h5>
                    <ul class="mb-0">
                        <li>Используйте не менее 8 символов</li>
                        <li>Добавьте заглавные и строчные буквы</li>
                        <li>Включите цифры и специальные символы (@, #, $ и т.д.)</li>
                        <li>Не используйте личную информацию</li>
                        <li>Избегайте простых последовательностей (123456, qwerty)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const passwordError = document.createElement('div');
    passwordError.className = 'invalid-feedback';
    confirmPassword.parentNode.appendChild(passwordError);
    
    confirmPassword.addEventListener('input', function() {
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.classList.add('is-invalid');
            passwordError.textContent = 'Пароли не совпадают';
        } else {
            confirmPassword.classList.remove('is-invalid');
            passwordError.textContent = '';
        }
    });
    
    form.addEventListener('submit', function(e) {
        if (newPassword.value !== confirmPassword.value) {
            e.preventDefault();
            confirmPassword.classList.add('is-invalid');
            passwordError.textContent = 'Пароли не совпадают';
            confirmPassword.focus();
        }
        
        if (newPassword.value.length < 8) {
            e.preventDefault();
            newPassword.classList.add('is-invalid');
            newPassword.focus();
        }
    });
    
    // Индикатор сложности пароля
    newPassword.addEventListener('input', function() {
        const strength = checkPasswordStrength(newPassword.value);
        updateStrengthIndicator(strength);
    });
});

function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return Math.min(strength, 5);
}

function updateStrengthIndicator(strength) {
    const indicator = document.getElementById('password-strength');
    if (!indicator) return;
    
    const texts = ['Очень слабый', 'Слабый', 'Средний', 'Хороший', 'Отличный', 'Идеальный'];
    const colors = ['danger', 'danger', 'warning', 'info', 'success', 'success'];
    
    indicator.textContent = texts[strength];
    indicator.className = 'text-' + colors[strength];
}
</script>

<?php require_once 'footer.php'; ?>