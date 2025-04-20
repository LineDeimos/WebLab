<?php
require_once 'header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Неверный формат email';
    } else {
        $to = 'info@rpggameworld.com';
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        $email_message = "Имя: $name\n";
        $email_message .= "Email: $email\n";
        $email_message .= "Тема: $subject\n\n";
        $email_message .= "Сообщение:\n$message";
        
        if (mail($to, $subject, $email_message, $headers)) {
            $success = 'Ваше сообщение успешно отправлено. Мы свяжемся с вами в ближайшее время.';
        } else {
            $error = 'Произошла ошибка при отправке сообщения. Пожалуйста, попробуйте позже.';
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card fade-in">
                <div class="card-body">
                    <h1 class="card-title text-center mb-4">Свяжитесь с нами</h1>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Ваше имя</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Тема</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Сообщение</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Отправить сообщение</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4 fade-in">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Контактная информация</h2>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Адрес</h5>
                            <p>
                                <i class="fas fa-map-marker-alt"></i> г. Москва, ул. Примерная, 123
                            </p>
                            
                            <h5>Телефон</h5>
                            <p>
                                <i class="fas fa-phone"></i> +7 (123) 456-7890
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Email</h5>
                            <p>
                                <i class="fas fa-envelope"></i> info@rpggameworld.com
                            </p>
                            
                            <h5>Режим работы</h5>
                            <p>
                                <i class="fas fa-clock"></i> Пн-Пт: 9:00 - 18:00<br>
                                <i class="fas fa-clock"></i> Сб-Вс: 10:00 - 16:00
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 