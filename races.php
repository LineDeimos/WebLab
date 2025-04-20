<?php
require_once 'header.php';
require_once 'db_connect.php';

try {
    $stmt = $pdo->query("SELECT * FROM расы ORDER BY Название");
    $races = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching races: " . $e->getMessage());
    $races = [];
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card fade-in">
                <div class="card-body">
                    <h1 class="card-title text-center mb-4">Расы мира</h1>
                    
                    <?php if (empty($races)): ?>
                        <div class="alert alert-info">
                            Расы не найдены.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($races as $race): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card race-card fade-in">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($race['Название']); ?></h5>
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