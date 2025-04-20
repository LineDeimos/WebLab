<?php
require_once 'header.php';
require_once 'db_connect.php';

try {
    $stmt = $pdo->query("
        SELECT q.*, l.Наименовние as Локация 
        FROM квесты q
        JOIN локации l ON q.Код_Локации = l.Код_Локации
        ORDER BY q.Код_Квеста DESC
        LIMIT 3
    ");
    $latest_quests = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching latest quests: " . $e->getMessage());
    $latest_quests = [];
}

try {
    $stmt = $pdo->query("
        SELECT p.*, r.Название as Раса, k.Название as Класс 
        FROM персонаж p
        JOIN расы r ON p.Код_Расы = r.Код_Расы
        JOIN классы k ON p.Код_Класса = k.Код_Класса
        ORDER BY p.Уровень DESC, p.Опыт DESC
        LIMIT 3
    ");
    $top_characters = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching top characters: " . $e->getMessage());
    $top_characters = [];
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card fade-in">
                <div class="card-body text-center">
                    <h1 class="display-4 mb-4">Добро пожаловать в RPG Game World!</h1>
                    <p class="lead">Отправляйтесь в увлекательное путешествие по нашему миру, полному тайн и приключений.</p>
                    <?php if (!isLoggedIn()): ?>
                        <a href="register.php" class="btn btn-primary btn-lg">Начать игру</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="text-center mb-4">Последние квесты</h2>
            
            <div class="row">
                <?php foreach ($latest_quests as $quest): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card quest-card fade-in">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($quest['Наименование']); ?></h5>
                                <p class="card-text">
                                    <strong>Локация:</strong> <?php echo htmlspecialchars($quest['Локация']); ?><br>
                                    <strong>Условие:</strong> <?php echo htmlspecialchars($quest['Условие']); ?>
                                </p>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($quest['Описание'])); ?></p>
                                <?php if (isLoggedIn()): ?>
                                    <a href="quests.php" class="btn btn-primary">Подробнее</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="text-center mb-4">Топ персонажей</h2>
            
            <div class="row">
                <?php foreach ($top_characters as $character): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card character-stats fade-in">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($character['Имя']); ?></h5>
                                <p class="card-text">
                                    <strong>Раса:</strong> <?php echo htmlspecialchars($character['Раса']); ?><br>
                                    <strong>Класс:</strong> <?php echo htmlspecialchars($character['Класс']); ?><br>
                                    <strong>Уровень:</strong> <?php echo htmlspecialchars($character['Уровень']); ?><br>
                                    <strong>Опыт:</strong> <?php echo htmlspecialchars($character['Опыт']); ?>
                                </p>
                                <?php if (isLoggedIn()): ?>
                                    <a href="gallery.php" class="btn btn-primary">Смотреть всех</a>
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
            <div class="card fade-in">
                <div class="card-body">
                    <h2 class="text-center mb-4">Особенности игры</h2>
                    
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                                <h4>Разнообразные расы</h4>
                                <p>Выберите свою расу и начните уникальное путешествие</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <i class="fas fa-shield-alt fa-3x mb-3 text-primary"></i>
                                <h4>Множество классов</h4>
                                <p>Станьте воином, волшебником или паладином</p>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="text-center">
                                <i class="fas fa-scroll fa-3x mb-3 text-primary"></i>
                                <h4>Увлекательные квесты</h4>
                                <p>Отправляйтесь в приключения и получайте награды</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 