<?php
require_once 'header.php';
require_once 'auth.php';

requireLogin();

$user = getCurrentUser();
if (!$user) {
    header("Location: login.php");
    exit();
}

global $pdo;
try {
    $stmt = $pdo->prepare("
        SELECT p.*, r.Название as Раса, k.Название as Класс, l.Наименовние as Локация 
        FROM персонаж p
        JOIN расы r ON p.Код_Расы = r.Код_Расы
        JOIN классы k ON p.Код_Класса = k.Код_Класса
        JOIN локации l ON p.Код_Локации = l.Код_Локации
        JOIN игроки_персонажи ip ON p.Код_Персонажа = ip.Код_Персонажа
        WHERE ip.Код_Игрока = ?
        ORDER BY p.Уровень DESC
    ");
    $stmt->execute([$user['Код_Игрока']]);
    $characters = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching characters: " . $e->getMessage());
    $characters = [];
}

$avatarPath = !empty($user['аватар']) ? $user['аватар'] : '/avatars/default.jpg';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Боковая панель профиля -->
            <div class="card profile-sidebar mb-4">
                <div class="card-body text-center">
                    <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="Character Avatar" class="img-fluid rounded-circle mb-2">
                    <h4><?php echo htmlspecialchars($user['Имя']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($user['Роль']); ?></p>
                    
                    <div class="list-group">
                        <a href="profile.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-user me-2"></i>Мой профиль
                        </a>
                        <a href="edit_profile.php" class="list-group-item list-group-item-action">
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
            
            <!-- Статистика профиля -->
            <div class="card profile-stats">
                <div class="card-body">
                    <h5 class="card-title">Статистика</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-users me-2"></i>
                            Персонажи: <strong><?php echo count($characters); ?></strong>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Дата регистрации: <strong><?php echo date('d.m.Y', strtotime($user['Дата_регистрации'])); ?></strong>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            Email: <strong><?php echo htmlspecialchars($user['Почта']); ?></strong>
                        </li>
                        <li>
                            <i class="fas fa-shield-alt me-2"></i>
                            Статус: <span class="badge bg-<?php echo $user['Статус'] === 'Активен' ? 'success' : 'danger'; ?>">
                                <?php echo htmlspecialchars($user['Статус']); ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Основное содержимое -->
            <div class="card profile-content">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Мои персонажи</h4>
                        <a href="create_character.php" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i> Новый персонаж
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (empty($characters)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users-slash fa-4x text-muted mb-4"></i>
                            <h5>У вас пока нет персонажей</h5>
                            <p>Создайте своего первого персонажа и начните игру!</p>
                            <a href="create_character.php" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Создать персонажа
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($characters as $character): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card character-card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0"><?php echo htmlspecialchars($character['Имя']); ?></h5>
                                            <span class="badge bg-secondary">Ур. <?php echo htmlspecialchars($character['Уровень']); ?></span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4 text-center">
                                                    <!-- Используем изображение персонажа из таблицы персонаж -->
                                                    <img src="<?php echo htmlspecialchars(!empty($character['Изображение']) ? $character['Изображение'] : '/char_avatar/default_avatar.png'); ?>" alt="Character Avatar" class="img-fluid rounded-circle mb-2">
                                                    <div class="d-flex justify-content-center">
                                                        <span class="badge bg-info me-1"><?php echo htmlspecialchars($character['Раса']); ?></span>
                                                        <span class="badge bg-warning"><?php echo htmlspecialchars($character['Класс']); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="character-stats">
                                                        <div class="stat-item">
                                                            <span class="stat-label">Опыт:</span>
                                                            <span class="stat-value"><?php echo htmlspecialchars($character['Опыт']); ?></span>
                                                        </div>
                                                        <div class="stat-item">
                                                            <span class="stat-label">Золото:</span>
                                                            <span class="stat-value"><?php echo htmlspecialchars($character['Золото']); ?></span>
                                                        </div>
                                                        <div class="stat-item">
                                                            <span class="stat-label">Локация:</span>
                                                            <span class="stat-value"><?php echo htmlspecialchars($character['Локация']); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="d-flex justify-content-between">
                                                <a href="character.php?id=<?php echo $character['Код_Персонажа']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-info-circle me-1"></i> Подробнее
                                                </a>
                                                <a href="edit_character.php?id=<?php echo $character['Код_Персонажа']; ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-edit me-1"></i> Редактировать
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>