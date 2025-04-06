<?php
session_start();
include 'includes/config.php';
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/auth.php';

// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Process forgot password form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    
    if (empty($email)) {
        $error = "Email is required";
    } else {
        $result = send_password_reset($email);
        
        if ($result['success']) {
            $success = "Password reset link has been sent to your email.";
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - DoodleSense AI</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>DoodleSense AI</h1>
                <p>Reset your password</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div class="auth-info">
                <p>Enter your email address and we'll send you a link to reset your password.</p>
            </div>
            
            <form class="auth-form" method="POST" action="forgot_password.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary btn-full">Send Reset Link</button>
            </form>
            
            <div class="auth-footer">
                <p>Remember your password? <a href="login.php">Sign in</a></p>
                <a href="index.php" class="back-to-home">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <script src="js/auth.js"></script>
</body>
</html>
