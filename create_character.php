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
$races = [];
$classes = [];
$locations = [];

try {
    $stmt = $pdo->query("SELECT * FROM расы ORDER BY Название");
    $races = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM классы ORDER BY Название");
    $classes = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM локации ORDER BY Наименовние");
    $locations = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $race_id = (int)($_POST['race_id'] ?? 0);
    $class_id = (int)($_POST['class_id'] ?? 0);
    $location_id = (int)($_POST['location_id'] ?? 0);
    
    // Валидация данных
    if (empty($name)) {
        $error = 'Имя персонажа обязательно';
    } elseif (strlen($name) > 18) {
        $error = 'Имя персонажа должно быть не длиннее 18 символов';
    } elseif ($race_id <= 0) {
        $error = 'Выберите расу';
    } elseif ($class_id <= 0) {
        $error = 'Выберите класс';
    } elseif ($location_id <= 0) {
        $error = 'Выберите начальную локацию';
    } else {
        try {
            $pdo->beginTransaction();
            
            // 1. Создаем персонажа
            $stmt = $pdo->prepare("
                INSERT INTO персонаж 
                (Имя, Уровень, Опыт, Золото, Код_Локации, Код_Расы, Код_Класса) 
                VALUES (?, 1, 0, 100, ?, ?, ?)
            ");
            $stmt->execute([$name, $location_id, $race_id, $class_id]);
            $character_id = $pdo->lastInsertId();
            
            // 2. Привязываем персонажа к игроку
            $stmt = $pdo->prepare("
                INSERT INTO игроки_персонажи 
                (Код_Персонажа, Код_Игрока) 
                VALUES (?, ?)
            ");
            $stmt->execute([$character_id, $user['Код_Игрока']]);
            
            $pdo->commit();
            
            $success = 'Персонаж успешно создан!';
            header("refresh:2;url=character.php?id=" . $character_id);
        } catch(PDOException $e) {
            $pdo->rollBack();
            error_log("Error creating character: " . $e->getMessage());
            $error = 'Ошибка при создании персонажа: ' . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Боковая панель профиля -->
            <div class="card profile-sidebar mb-4 fantasy-card">
                <div class="card-body text-center">
                    <img src="<?php echo htmlspecialchars($user['аватар']); ?>" alt="Character Avatar" class="img-fluid rounded-circle mb-2">
                    <h4 class="fantasy-title"><?php echo htmlspecialchars($user['Имя']); ?></h4>
                    <p class="text-muted fantasy-text"><?php echo htmlspecialchars($user['Роль']); ?></p>
                    
                    <div class="list-group fantasy-list">
                        <a href="profile.php" class="list-group-item list-group-item-action fantasy-list-item">
                            <i class="fas fa-user me-2 fantasy-icon"></i>Мой профиль
                        </a>
                        <a href="edit_profile.php" class="list-group-item list-group-item-action fantasy-list-item">
                            <i class="fas fa-edit me-2 fantasy-icon"></i>Редактировать профиль
                        </a>
                        <a href="create_character.php" class="list-group-item list-group-item-action active fantasy-list-item active-item">
                            <i class="fas fa-plus-circle me-2 fantasy-icon"></i>Создать персонажа
                        </a>
                        <a href="change_password.php" class="list-group-item list-group-item-action fantasy-list-item">
                            <i class="fas fa-lock me-2 fantasy-icon"></i>Сменить пароль
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger fantasy-list-item">
                            <i class="fas fa-sign-out-alt me-2 fantasy-icon"></i>Выйти
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Основное содержимое -->
            <div class="card fantasy-card">
                <div class="card-header fantasy-card-header">
                    <h4 class="mb-0 fantasy-title">Создание нового персонажа</h4>
                </div>
                <div class="card-body fantasy-card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger fantasy-alert"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success fantasy-alert"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label fantasy-label">Имя персонажа</label>
                                    <input type="text" class="form-control fantasy-input" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                           required maxlength="18">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="race_id" class="form-label fantasy-label">Раса</label>
                                    <select class="form-select fantasy-select" id="race_id" name="race_id" required>
                                        <option value="">-- Выберите расу --</option>
                                        <?php foreach ($races as $race): ?>
                                            <option value="<?php echo $race['Код_Расы']; ?>"
                                                <?php if (($_POST['race_id'] ?? 0) == $race['Код_Расы']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($race['Название']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="class_id" class="form-label fantasy-label">Класс</label>
                                    <select class="form-select fantasy-select" id="class_id" name="class_id" required>
                                        <option value="">-- Выберите класс --</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['Код_Класса']; ?>"
                                                <?php if (($_POST['class_id'] ?? 0) == $class['Код_Класса']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($class['Название']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="location_id" class="form-label fantasy-label">Начальная локация</label>
                                    <select class="form-select fantasy-select" id="location_id" name="location_id" required>
                                        <option value="">-- Выберите локацию --</option>
                                        <?php foreach ($locations as $location): ?>
                                            <option value="<?php echo $location['Код_Локации']; ?>"
                                                <?php if (($_POST['location_id'] ?? 0) == $location['Код_Локации']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($location['Наименовние']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="profile.php" class="btn btn-secondary me-md-2 fantasy-btn fantasy-btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary fantasy-btn fantasy-btn-primary">Создать персонажа</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Информация о расах и классах -->
            <div class="row mt-4 g-3">
                <div class="col-md-6">
                    <div class="fantasy-section">
                        <h3 class="fantasy-section-title">
                            <i class="fas fa-users me-2"></i>Описание рас
                        </h3>

                        <div class="fantasy-items-container">
                            <?php foreach ($races as $race): ?>
                                <div class="fantasy-item race-item">
                                    <h4 class="fantasy-item-title">
                                        <?php echo htmlspecialchars($race['Название']); ?>
                                    </h4>
                                    <div class="fantasy-item-content">
                                        <p><?php echo !empty($race['Описание']) ? htmlspecialchars($race['Описание']) : 'Описание отсутствует'; ?></p>
                                        <?php if (!empty($race['Бонусы'])): ?>
                                            <div class="fantasy-bonuses">
                                                <span class="bonus-label">Бонусы:</span>
                                                <?php echo htmlspecialchars($race['Бонусы']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                                        
                <div class="col-md-6">
                    <div class="fantasy-section">
                        <h3 class="fantasy-section-title">
                            <i class="fas fa-scroll me-2"></i>Описание классов
                        </h3>
                                        
                        <div class="fantasy-items-container">
                            <?php foreach ($classes as $class): ?>
                                <div class="fantasy-item class-item">
                                    <h4 class="fantasy-item-title">
                                        <?php echo htmlspecialchars($class['Название']); ?>
                                    </h4>
                                    <div class="fantasy-item-content">
                                        <p><?php echo !empty($class['Описание']) ? htmlspecialchars($class['Описание']) : 'Описание отсутствует'; ?></p>
                                        <?php if (!empty($class['Особенности'])): ?>
                                            <div class="fantasy-bonuses">
                                                <span class="bonus-label">Особенности:</span>
                                                <?php echo htmlspecialchars($class['Особенности']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>