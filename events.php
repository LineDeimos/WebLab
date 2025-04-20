<?php
require_once 'header.php';
require_once 'db_connect.php';

$location_id = $_GET['location'] ?? null;
$search = $_GET['search'] ?? '';

try {
    $query = "
        SELECT e.*, l.Наименовние as Локация
        FROM события e
        JOIN локации l ON e.Код_Локации = l.Код_Локации
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($location_id) {
        $query .= " AND e.Код_Локации = ?";
        $params[] = $location_id;
    }
    
    if ($search) {
        $query .= " AND (e.Тип LIKE ? OR e.Описание LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $query .= " ORDER BY e.Время DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching events: " . $e->getMessage());
    $events = [];
}

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
                    <h1 class="card-title text-center mb-4">События мира</h1>
                    
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
                                    <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Поиск по типу или описанию">
                                    <button class="btn btn-primary" type="submit">Поиск</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <?php if (empty($events)): ?>
                        <div class="alert alert-info">
                            События не найдены. Попробуйте изменить параметры поиска.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($events as $event): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card event-card fade-in">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($event['Тип']); ?></h5>
                                            <p class="card-text">
                                                <strong>Локация:</strong> <?php echo htmlspecialchars($event['Локация']); ?><br>
                                                <strong>Время:</strong> <?php echo htmlspecialchars($event['Время']); ?>
                                            </p>
                                            <p class="card-text">
                                                <?php echo nl2br(htmlspecialchars($event['Описание'])); ?>
                                            </p>
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