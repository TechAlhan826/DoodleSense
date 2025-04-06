<?php
session_start();
include 'includes/config.php';
include 'includes/db.php';
include 'includes/functions.php';
include 'includes/auth.php';

// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: drawing_board.php");
    exit();
}

$error = '';
$success = '';
$token = '';
$email = '';

// Check if token is valid
if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = sanitize_input($_GET['token']);
    $email = sanitize_input($_GET['email']);
    
    if (!verify_reset_token($email, $token)) {
        $error = "Invalid or expired reset token. Please request a new password reset link.";
    }
} else {
    header("Location: forgot_password.php");
    exit();
}

// Process reset password form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        $result = reset_password($email, $token, $password);
        
        if ($result['success']) {
            $success = "Password has been reset successfully. You can now login with your new password.";
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
    <title>Reset Password - DoodleSense AI</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>DoodleSense AI</h1>
                <p>Set a new password</p>
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
                    <div class="auth-redirect">
                        <a href="login.php" class="btn-secondary">Go to Login</a>
                    </div>
                </div>
            <?php else: ?>
                <form class="auth-form" method="POST" action="reset_password.php?token=<?php echo urlencode($token); ?>&email=<?php echo urlencode($email); ?>">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Create a new password" required>
                        </div>
                        <div class="password-strength" id="password-strength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary btn-full">Reset Password</button>
                </form>
            <?php endif; ?>
            
            <div class="auth-footer">
                <a href="login.php" class="back-to-home">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>
    
    <script src="js/auth.js"></script>
</body>
</html>
