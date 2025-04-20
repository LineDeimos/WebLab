<?php
require_once 'header.php';
require_once 'db_connect.php';

try {
    $stmt = $pdo->query("SELECT * FROM классы ORDER BY Название");
    $classes = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching classes: " . $e->getMessage());
    $classes = [];
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card fade-in">
                <div class="card-body">
                    <h1 class="card-title text-center mb-4">Классы персонажей</h1>
                    
                    <?php if (empty($classes)): ?>
                        <div class="alert alert-info">
                            Классы не найдены.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($classes as $class): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card class-card fade-in">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($class['Название']); ?></h5>
                                            <p class="card-text">
                                            
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