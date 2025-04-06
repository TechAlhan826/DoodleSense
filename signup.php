<?php
session_start();
require_once 'includes/config.php';

class Database {
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=".DB_HOST.";dbname=doodlesense", "root", "Abcd@123");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function getRow($query, $params = []) {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($data);
        
        return $this->conn->lastInsertId();
    }
}

$db = new Database();
$conn = $db->getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All required fields must be filled';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } elseif (strlen($username) > 50) {
        $error = 'Username must be 50 characters or less';
    } else {
        // Check if user exists
        $existing = $db->getRow(
            "SELECT id FROM users WHERE username = ? OR email = ?", 
            [$username, $email]
        );
        
        if ($existing) {
            $error = 'Username or email already exists';
        } else {
            // Generate verification token
            $verification_token = bin2hex(random_bytes(32));
            
            // Insert user
            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'first_name' => !empty($first_name) ? $first_name : null,
                'last_name' => !empty($last_name) ? $last_name : null,
                'verification_token' => $verification_token,
                'is_verified' => 0
            ];
            
            $userId = $db->insert('users', $userData);
            
            if ($userId) {
                // Send verification email (implementation depends on your email setup)
                $verifyLink = "https://yourdomain.com/verify.php?token=$verification_token";
                $subject = "Verify Your Email Address";
                $message = "Hello $username,\n\nPlease verify your email by clicking this link:\n$verifyLink";
                
                // In production, use a proper email library like PHPMailer
                // mail($email, $subject, $message);
                
                $success = 'Registration successful! Please check your email to verify your account.';
                
                // Clear form
                $username = $email = $first_name = $last_name = '';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | DoodleSense AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>DoodleSense AI</h1>
                <p>Create a new account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST">
                <div class="form-group">
                    <label for="username">Username*</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Choose a username" 
                               value="<?php echo htmlspecialchars($username ?? ''); ?>" maxlength="50" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email*</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>" maxlength="100" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user-tag"></i>
                        <input type="text" id="first_name" name="first_name" placeholder="Your first name" 
                               value="<?php echo htmlspecialchars($first_name ?? ''); ?>" maxlength="50">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user-tag"></i>
                        <input type="text" id="last_name" name="last_name" placeholder="Your last name" 
                               value="<?php echo htmlspecialchars($last_name ?? ''); ?>" maxlength="50">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password*</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="password-strength-bar"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password*</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm your password" required>
                    </div>
                </div>
                
                <div class="terms">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                </div>
                
                <button type="submit" class="btn-primary">Create Account</button>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Sign in</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="js/auth.js"></script>
</body>
</html>