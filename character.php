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
$inventory = [];
$quests = [];

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
        SELECT p.*, r.Название as Раса, r.Описание as Описание_расы, 
               k.Название as Класс, k.Описание as Описание_класса,
               l.Наименовние as Локация, l.Описание as Описание_локации
        FROM персонаж p
        JOIN расы r ON p.Код_Расы = r.Код_Расы
        JOIN классы k ON p.Код_Класса = k.Код_Класса
        JOIN локации l ON p.Код_Локации = l.Код_Локации
        WHERE p.Код_Персонажа = ?
    ");
    $stmt->execute([$character_id]);
    $character = $stmt->fetch();
    
    if (!$character) {
        header("Location: profile.php");
        exit();
    }
    
    $stmt = $pdo->prepare("
        SELECT i.*, p.Название, p.Цена, p.Вес, p.Эффект
        FROM инвентари i
        JOIN предметы p ON i.Код_Предмета = p.Код_Предмета
        WHERE i.Код_Персонажа = ?
        ORDER BY p.Название
    ");
    $stmt->execute([$character_id]);
    $inventory = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("
        SELECT q.*, l.Наименовние as Локация_квеста
        FROM квесты q
        JOIN локации l ON q.Код_Локации = l.Код_Локации
        JOIN инвентари_квесты iq ON q.Код_Квеста = iq.Код_Квеста
        JOIN инвентари i ON iq.Код_Инвенторя = i.Код_Инвенторя
        WHERE i.Код_Персонажа = ?
        ORDER BY q.Наименование
    ");
    $stmt->execute([$character_id]);
    $quests = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log("Character page error: " . $e->getMessage());
    header("Location: profile.php");
    exit();
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <!-- Боковая панель профиля -->
            <div class="card profile-sidebar mb-4">
                <div class="card-body text-center">
                    <img src="https://via.placeholder.com/150" alt="Profile Avatar" class="profile-avatar rounded-circle mb-3">
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
            <!-- Основная информация о персонаже -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?php echo htmlspecialchars($character['Имя']); ?></h4>
                        <div>
                            <a href="edit_character.php?id=<?php echo $character_id; ?>" class="btn btn-sm btn-light">
                                <i class="fas fa-edit"></i> Редактировать
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="https://via.placeholder.com/200" alt="Character Avatar" class="img-fluid rounded mb-3">
                            <div class="d-flex justify-content-center">
                                <span class="badge bg-info me-1"><?php echo htmlspecialchars($character['Раса']); ?></span>
                                <span class="badge bg-warning"><?php echo htmlspecialchars($character['Класс']); ?></span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="character-stat mb-3">
                                        <h6>Уровень</h6>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo min(100, ($character['Опыт']/($character['Уровень']*1000))*100); ?>%" 
                                                 aria-valuenow="<?php echo $character['Опыт']; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="<?php echo $character['Уровень']*1000; ?>">
                                                <?php echo $character['Уровень']; ?> (<?php echo $character['Опыт']; ?>/<?php echo $character['Уровень']*1000; ?>)
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="character-stat mb-3">
                                        <h6>Здоровье</h6>
                                        <div class="progress">
                                            <div class="progress-bar bg-danger" role="progressbar" 
                                                 style="width: 100%" 
                                                 aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                                100/100
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="character-stat mb-3">
                                        <h6>Золото</h6>
                                        <p class="fs-4 text-warning">
                                            <i class="fas fa-coins me-2"></i><?php echo $character['Золото']; ?>
                                        </p>
                                    </div>
                                    
                                    <div class="character-stat mb-3">
                                        <h6>Локация</h6>
                                        <p>
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            <?php echo htmlspecialchars($character['Локация']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">Описание расы</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><?php echo htmlspecialchars($character['Описание_расы']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">Описание класса</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><?php echo htmlspecialchars($character['Описание_класса']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Инвентарь персонажа -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Инвентарь</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($inventory)): ?>
                        <div class="alert alert-info mb-0">
                            Инвентарь пуст
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($inventory as $item): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo htmlspecialchars($item['Название']); ?></h6>
                                            <p class="card-text small">
                                                <strong>Эффект:</strong> <?php echo htmlspecialchars($item['Эффект']); ?><br>
                                                <strong>Цена:</strong> <?php echo $item['Цена']; ?> золота<br>
                                                <strong>Вес:</strong> <?php echo $item['Вес']; ?> кг
                                            </p>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <button class="btn btn-sm btn-outline-primary me-2">
                                                <i class="fas fa-arrow-up"></i> Использовать
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i> Выбросить
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Активные квесты -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Активные квесты</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($quests)): ?>
                        <div class="alert alert-info mb-0">
                            Нет активных квестов
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($quests as $quest): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($quest['Наименование']); ?></h6>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($quest['Локация_квеста']); ?></span>
                                    </div>
                                    <p class="mb-1 small"><?php echo nl2br(htmlspecialchars($quest['Описание'])); ?></p>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted">Награда: <?php echo $quest['Золото']; ?> золота</small>
                                        <button class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-check"></i> Завершить
                                        </button>
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