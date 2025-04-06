<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$db = Database::getInstance();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: drawing_board.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Get user by email
        $user = $db->getRow(
            "SELECT id, username, email, password, is_verified FROM users WHERE email = :email", 
            [':email' => $email]
        );
        
        if ($user) {
            // Check if email is verified
            if (!$user['is_verified']) {
                $error = 'Please verify your email address first. <a href="resend_verification.php?email='.urlencode($email).'">Resend verification email</a>';
            } 
            // Verify password
            elseif (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                // Update last login time
                $db->update(
                    'users', 
                    ['updated_at' => date('Y-m-d H:i:s')], 
                    'id = :id', 
                    [':id' => $user['id']]
                );
                
                // Set remember me cookie if checked
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database
                    $db->update(
                        'users', 
                        [
                            'remember_token' => $token,
                            'remember_expires' => date('Y-m-d H:i:s', $expires)
                        ], 
                        'id = :id', 
                        [':id' => $user['id']]
                    );
                    
                    // Set cookie
                    setcookie('remember_token', $token, $expires, '/', '', true, true);
                }
                
                // Redirect to dashboard
                header("Location: drawing_board.php");
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}

// Check for remember me cookie
if (isset($_COOKIE['remember_token']) && empty($_SESSION['user_id'])) {
    $token = $_COOKIE['remember_token'];
    
    $user = $db->getRow(
        "SELECT id, username, email FROM users WHERE remember_token = :token AND remember_expires > NOW()", 
        [':token' => $token]
    );
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        
        // Update last login time
        $db->update(
            'users', 
            ['updated_at' => date('Y-m-d H:i:s')], 
            'id = :id', 
            [':id' => $user['id']]
        );
        
        header("Location: drawing_board.php");
        exit();
    }
}

// Rest of the login.php HTML remains the same
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | DoodleSense AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>DoodleSense AI</h1>
                <p>Sign in to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn-primary">Sign In</button>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="signup.php">Sign up</a></p>
                </div>
            </form>
        </div>
    </div>
    <script src="js/auth.js"></script>
</body>
</html>