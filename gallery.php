<?php
require_once 'header.php';
require_once 'db_connect.php';

try {
    $stmt = $pdo->query("
        SELECT p.*, r.Название as Раса, k.Название as Класс 
        FROM персонаж p
        JOIN расы r ON p.Код_Расы = r.Код_Расы
        JOIN классы k ON p.Код_Класса = k.Код_Класса
        ORDER BY p.Уровень DESC
    ");
    $characters = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching characters: " . $e->getMessage());
    $characters = [];
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
            <h1 class="text-center mb-4">Галерея персонажей</h1>
            
            <div class="row">
                <?php foreach ($characters as $character): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card character-stats fade-in">
                            <img src="<?php echo htmlspecialchars($character['Изображение']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($character['Имя']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($character['Имя']); ?></h5>
                                <p class="card-text">
                                    <strong>Раса:</strong> <?php echo htmlspecialchars($character['Раса']); ?><br>
                                    <strong>Класс:</strong> <?php echo htmlspecialchars($character['Класс']); ?><br>
                                    <strong>Уровень:</strong> <?php echo htmlspecialchars($character['Уровень']); ?><br>
                                    <strong>Опыт:</strong> <?php echo htmlspecialchars($character['Опыт']); ?><br>
                                    <strong>Золото:</strong> <?php echo htmlspecialchars($character['Золото']); ?>
                                </p>
                                <?php if (isLoggedIn()): ?>
                                    <a href="character.php?id=<?php echo $character['Код_Персонажа']; ?>" class="btn btn-primary">Подробнее</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <h1 class="text-center mb-4">Локации мира</h1>
            
            <div class="row">
                <?php foreach ($locations as $location): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card quest-card fade-in">
                            <img src="<?php echo htmlspecialchars($location['изображение']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($location['Наименовние']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($location['Наименовние']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($location['Описание']); ?></p>
                                <?php if (isLoggedIn()): ?>
                                    <a href="quests.php?location=<?php echo $location['Код_Локации']; ?>" class="btn btn-primary">Квесты в этой локации</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 