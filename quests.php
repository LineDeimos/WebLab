<?php
require_once 'header.php';
require_once 'db_connect.php';

$location_id = $_GET['location'] ?? null;
$search = $_GET['search'] ?? '';

try {
    $query = "
        SELECT q.*, l.Наименовние as Локация, n.Золото, n.Опыт
        FROM квесты q
        JOIN локации l ON q.Код_Локации = l.Код_Локации
        JOIN награды n ON q.Код_Награды = n.Код_Награды
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($location_id) {
        $query .= " AND q.Код_Локации = ?";
        $params[] = $location_id;
    }
    
    if ($search) {
        $query .= " AND (q.Наименование LIKE ? OR q.Описание LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $query .= " ORDER BY q.Код_Квеста DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $quests = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching quests: " . $e->getMessage());
    $quests = [];
}

// Get all locations for filter
try {
    $stmt = $pdo->query("SELECT * FROM локации");
    $locations = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching locations: " . $e->getMessage());
    $locations = [];
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card fade-in">
                <div class="card-body">
                    <h1 class="card-title text-center mb-4">Квесты</h1>
                    
                    <form method="GET" action="" class="mb-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Фильтр по локации</label>
                                <select class="form-select" id="location" name="location">
                                    <option value="">Все локации</option>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?php echo $location['Код_Локации']; ?>" <?php echo $location_id == $location['Код_Локации'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($location['Наименовние']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="search" class="form-label">Поиск</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Поиск по названию или описанию">
                                    <button class="btn btn-primary" type="submit">Поиск</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <?php if (empty($quests)): ?>
                        <div class="alert alert-info">
                            Квесты не найдены. Попробуйте изменить параметры поиска.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($quests as $quest): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card quest-card fade-in">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($quest['Наименование']); ?></h5>
                                            <p class="card-text">
                                                <strong>Локация:</strong> <?php echo htmlspecialchars($quest['Локация']); ?><br>
                                                <strong>Условие:</strong> <?php echo htmlspecialchars($quest['Условие']); ?><br>
                                                <strong>Награда:</strong> <?php echo htmlspecialchars($quest['Золото']); ?> золота, <?php echo htmlspecialchars($quest['Опыт']); ?> опыта
                                            </p>
                                            <p class="card-text">
                                                <?php echo nl2br(htmlspecialchars($quest['Описание'])); ?>
                                            </p>
                                            <?php if (isLoggedIn()): ?>
                                                <a href="character.php?quest=<?php echo $quest['Код_Квеста']; ?>" class="btn btn-primary">Принять квест</a>
                                            <?php endif; ?>
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