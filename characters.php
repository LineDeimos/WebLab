<?php 
$pageTitle = "Персонажи";
require_once "auth.php";
require_once "db_connect.php";
require_once "header.php";

$race = $_GET['race'] ?? '';
$class = $_GET['class'] ?? '';
$level = $_GET['level'] ?? '';

$query = "
    SELECT p.*, r.Название as Раса, k.Название as Класс 
    FROM персонаж p
    JOIN расы r ON p.Код_Расы = r.Код_Расы
    JOIN классы k ON p.Код_Класса = k.Код_Класса
    WHERE 1=1
";
$params = [];

if ($race) {
    $query .= " AND r.Название = ?";
    $params[] = $race;
}

if ($class) {
    $query .= " AND k.Название = ?";
    $params[] = $class;
}

if ($level) {
    $query .= " AND p.Уровень = ?";
    $params[] = $level;
}

$races = $pdo->query("SELECT Название FROM расы")->fetchAll(PDO::FETCH_COLUMN);
$classes = $pdo->query("SELECT Название FROM классы")->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$characters = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Фильтры</h5>
                <form method="GET" action="">
                    <div class="mb-3">
                        <label for="race" class="form-label">Раса</label>
                        <select class="form-select" id="race" name="race">
                            <option value="">Все расы</option>
                            <?php foreach ($races as $r): ?>
                                <option value="<?php echo htmlspecialchars($r); ?>" <?php echo $race === $r ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($r); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="class" class="form-label">Класс</label>
                        <select class="form-select" id="class" name="class">
                            <option value="">Все классы</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $class === $c ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="level" class="form-label">Уровень</label>
                        <input type="number" class="form-control" id="level" name="level" value="<?php echo htmlspecialchars($level); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Применить фильтры</button>
                    <a href="/lab4/pages/characters.php" class="btn btn-secondary">Сбросить</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <h2 class="mb-4">Персонажи</h2>
        
        <?php if (empty($characters)): ?>
            <div class="alert alert-info">
                Персонажи не найдены. Попробуйте изменить параметры фильтрации.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($characters as $character): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card character-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($character['Имя']); ?></h5>
                                <p><strong>Уровень:</strong> <?php echo htmlspecialchars($character['Уровень']); ?></p>
                                <p><strong>Опыт:</strong> <?php echo htmlspecialchars($character['Опыт']); ?></p>
                                <p><strong>Золото:</strong> <?php echo htmlspecialchars($character['Золото']); ?></p>
                                <p><strong>Раса:</strong> <?php echo htmlspecialchars($character['Раса']); ?></p>
                                <p><strong>Класс:</strong> <?php echo htmlspecialchars($character['Класс']); ?></p>
                                <a href="/lab4/pages/character.php?id=<?php echo $character['Код_Персонажа']; ?>" class="btn btn-primary">Подробнее</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function addToParty(characterId) {
    fetch('/process/add_to_party.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ characterId: characterId, userId: <?php echo $_SESSION['user_id'] ?? 'null'; ?> })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Персонаж добавлен в вашу группу!');
        } else {
            alert('Ошибка: ' + data.message);
        }
    });
}
</script>

<?php require_once "footer.php"; ?>