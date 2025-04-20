<?php
require_once 'header.php';
require_once 'auth.php';

requireLogin();

$character_id = $_GET['id'] ?? 0;

$user = getCurrentUser();
if (!$user) {
    header("Location: login.php");
    exit();
}

$character = [];
$races = [];
$classes = [];
$locations = [];

try {
    global $pdo;

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
        SELECT * FROM персонаж 
        WHERE Код_Персонажа = ?
    ");
    $stmt->execute([$character_id]);
    $character = $stmt->fetch();
    
    if (!$character) {
        header("Location: profile.php");
        exit();
    }

    $stmt = $pdo->query("SELECT * FROM расы ORDER BY Название");
    $races = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM классы ORDER BY Название");
    $classes = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM локации ORDER BY Наименовние");
    $locations = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log("Edit character error: " . $e->getMessage());
    header("Location: profile.php");
    exit();
}

$error = '';
$success = '';

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $race_id = (int)($_POST['race_id'] ?? 0);
    $class_id = (int)($_POST['class_id'] ?? 0);
    $location_id = (int)($_POST['location_id'] ?? 0);
    $level = (int)($_POST['level'] ?? 1);
    $experience = (int)($_POST['experience'] ?? 0);
    $gold = (int)($_POST['gold'] ?? 0);
    
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
        $error = 'Выберите локацию';
    } elseif ($level < 1 || $level > 100) {
        $error = 'Уровень должен быть от 1 до 100';
    } elseif ($experience < 0) {
        $error = 'Опыт не может быть отрицательным';
    } elseif ($gold < 0) {
        $error = 'Золото не может быть отрицательным';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE персонаж 
                SET Имя = ?, Код_Расы = ?, Код_Класса = ?, 
                    Код_Локации = ?, Уровень = ?, Опыт = ?, Золото = ?
                WHERE Код_Персонажа = ?
            ");
            $result = $stmt->execute([
                $name, $race_id, $class_id, $location_id, 
                $level, $experience, $gold, $character_id
            ]);
            
            if ($result) {
                $success = 'Изменения сохранены успешно!';
                // Обновляем данные персонажа
                $stmt = $pdo->prepare("SELECT * FROM персонаж WHERE Код_Персонажа = ?");
                $stmt->execute([$character_id]);
                $character = $stmt->fetch();
            } else {
                $error = 'Ошибка при сохранении изменений';
            }
            
        } catch(PDOException $e) {
            error_log("Update character error: " . $e->getMessage());
            $error = 'Ошибка при сохранении изменений: ' . $e->getMessage();
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
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Редактирование персонажа</h4>
                        <a href="character.php?id=<?php echo $character_id; ?>" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left"></i> Назад
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Имя персонажа</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($character['Имя']); ?>" 
                                           required maxlength="18">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="race_id" class="form-label">Раса</label>
                                    <select class="form-select" id="race_id" name="race_id" required>
                                        <option value="">-- Выберите расу --</option>
                                        <?php foreach ($races as $race): ?>
                                            <option value="<?php echo $race['Код_Расы']; ?>"
                                                <?php if ($character['Код_Расы'] == $race['Код_Расы']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($race['Название']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Класс</label>
                                    <select class="form-select" id="class_id" name="class_id" required>
                                        <option value="">-- Выберите класс --</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['Код_Класса']; ?>"
                                                <?php if ($character['Код_Класса'] == $class['Код_Класса']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($class['Название']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location_id" class="form-label">Локация</label>
                                    <select class="form-select" id="location_id" name="location_id" required>
                                        <option value="">-- Выберите локацию --</option>
                                        <?php foreach ($locations as $location): ?>
                                            <option value="<?php echo $location['Код_Локации']; ?>"
                                                <?php if ($character['Код_Локации'] == $location['Код_Локации']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($location['Наименовние']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="level" class="form-label">Уровень</label>
                                    <input type="number" class="form-control" id="level" name="level" 
                                           min="1" max="100" 
                                           value="<?php echo $character['Уровень']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="experience" class="form-label">Опыт</label>
                                    <input type="number" class="form-control" id="experience" name="experience" 
                                           min="0" 
                                           value="<?php echo $character['Опыт']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="gold" class="form-label">Золото</label>
                                    <input type="number" class="form-control" id="gold" name="gold" 
                                           min="0" 
                                           value="<?php echo $character['Золото']; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="character.php?id=<?php echo $character_id; ?>" class="btn btn-secondary me-md-2">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Дополнительные действия -->
            <div class="card mt-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Опасная зона</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteCharacterModal">
                            <i class="fas fa-trash me-2"></i> Удалить персонажа
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteCharacterModal" tabindex="-1" aria-labelledby="deleteCharacterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteCharacterModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить персонажа <strong><?php echo htmlspecialchars($character['Имя']); ?></strong>?</p>
                <p class="text-danger">Это действие невозможно отменить!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form action="delete_character.php" method="POST" style="display: inline;">
                    <input type="hidden" name="character_id" value="<?php echo $character_id; ?>">
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>