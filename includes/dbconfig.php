<?php
require_once('config.php');
error_reporting(E_ERROR | E_PARSE);	
date_default_timezone_set("Asia/Kolkata");   //India time (GMT+5:30)

try {
    global $conn;
    $conn = new PDO("$DB_TYPE:host=$DB_HOST;dbname=".$DB_NAME, $DB_USER, $DB_PASS);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
	$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

}
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }

*/
auth.php 

<?php
/**
 * Authentication functions
 * Handles user registration, login, password reset, etc.
 */

require_once 'db.php';
require_once 'config.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Register a new user
     * @param array $userData User data (username, email, password, etc.)
     * @return int|bool User ID on success, false on failure
     */
    public function register($userData) {
        try {
            // Check if username or email already exists
            $existingUser = $this->db->getRow(
                "SELECT id FROM users WHERE username = ? OR email = ?",
                [$userData['username'], $userData['email']]
            );
            
            if ($existingUser) {
                return false; // User already exists
            }
            
            // Hash password
            $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Generate verification token if email verification is enabled
            $verificationToken = null;
            if (VERIFY_EMAIL) {
                $verificationToken = bin2hex(random_bytes(32));
            }
            
            // Prepare user data
            $userDataToInsert = [
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => $passwordHash,
                'first_name' => $userData['first_name'] ?? null,
                'last_name' => $userData['last_name'] ?? null,
                'verification_token' => $verificationToken,
                'is_verified' => !VERIFY_EMAIL // If email verification is disabled, set as verified
            ];
            
            // Insert user
            $userId = $this->db->insert('users', $userDataToInsert);
            
            // Send verification email if enabled
            if (VERIFY_EMAIL && $verificationToken && isset($userData['email'])) {
                $this->sendVerificationEmail($userData['email'], $userData['username'], $verificationToken);
            }
            
            return $userId;
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log in a user
     * @param string $usernameOrEmail Username or email
     * @param string $password Password
     * @param bool $remember Whether to remember the user
     * @return bool Success status
     */
    public function login($usernameOrEmail, $password, $remember = false) {
        try {
            // Find user by username or email
            $user = $this->db->getRow(
                "SELECT id, username, email, password, is_verified FROM users WHERE username = ? OR email = ?",
                [$usernameOrEmail, $usernameOrEmail]
            );
            
            if (!$user) {
                return false; // User not found
            }
            
            // Check if user is verified
            if (VERIFY_EMAIL && !$user['is_verified']) {
                $_SESSION['auth_error'] = 'Please verify your email address before logging in.';
                return false;
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                return false; // Incorrect password
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_logged_in'] = true;
            
            // Update password hash if needed
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $this->db->update('users', ['password' => $newHash], 'id = ?', [$user['id']]);
            }
            
            // Set "remember me" cookie if requested
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (86400 * 30); // 30 days
                
                // Store token in database
                $this->db->update(
                    'users',
                    ['remember_token' => $token, 'remember_token_expires' => date('Y-m-d H:i:s', $expires)],
                    'id = ?',
                    [$user['id']]
                );
                
                // Set cookie
                setcookie('remember_token', $token, $expires, '/', '', SECURE_COOKIE, true);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user is logged in
     * @return bool Login status
     */
    public function isLoggedIn() {
        // Check session
        if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in']) {
            return true;
        }
        
        // Check "remember me" cookie
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            
            // Find user with matching token
            $user = $this->db->getRow(
                "SELECT id, username, email, remember_token_expires FROM users WHERE remember_token = ?",
                [$token]
            );
            
            if ($user && strtotime($user['remember_token_expires']) > time()) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['is_logged_in'] = true;
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Log out a user
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
        
        // Delete "remember me" cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', SECURE_COOKIE, true);
        }
    }
    
    /**
     * Send a verification email
     * @param string $email User email
     * @param string $username Username
     * @param string $token Verification token
     * @return bool Success status
     */
    public function sendVerificationEmail($email, $username, $token) {
        // For now, just log that an email would be sent
        error_log("Verification email would be sent to {$email} with token {$token}");
        
        // In a real application, this would send an email
        // using a library like PHPMailer or the mail() function
        return true;
    }
    
    /**
     * Verify a user's email
     * @param string $token Verification token
     * @return bool Success status
     */
    public function verifyEmail($token) {
        try {
            // Find user with matching token
            $user = $this->db->getRow(
                "SELECT id FROM users WHERE verification_token = ?",
                [$token]
            );
            
            if (!$user) {
                return false; // Invalid token
            }
            
            // Update user as verified
            $this->db->update(
                'users',
                ['is_verified' => true, 'verification_token' => null],
                'id = ?',
                [$user['id']]
            );
            
            return true;
        } catch (Exception $e) {
            error_log("Email verification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Request a password reset
     * @param string $email User email
     * @return bool Success status
     */
    public function requestPasswordReset($email) {
        try {
            // Find user by email
            $user = $this->db->getRow(
                "SELECT id, username FROM users WHERE email = ?",
                [$email]
            );
            
            if (!$user) {
                return false; // User not found
            }
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
            
            // Store token in database
            $this->db->update(
                'users',
                ['reset_token' => $token, 'reset_token_expires' => $expires],
                'id = ?',
                [$user['id']]
            );
            
            // For now, just log that an email would be sent
            error_log("Password reset email would be sent to {$email} with token {$token}");
            
            // In a real application, this would send an email
            // using a library like PHPMailer or the mail() function
            return true;
        } catch (Exception $e) {
            error_log("Password reset request error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reset a user's password
     * @param string $token Reset token
     * @param string $password New password
     * @return bool Success status
     */
    public function resetPassword($token, $password) {
        try {
            // Find user with matching token
            $user = $this->db->getRow(
                "SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()",
                [$token]
            );
            
            if (!$user) {
                return false; // Invalid or expired token
            }
            
            // Hash new password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Update user's password and clear reset token
            $this->db->update(
                'users',
                [
                    'password' => $passwordHash,
                    'reset_token' => null,
                    'reset_token_expires' => null
                ],
                'id = ?',
                [$user['id']]
            );
            
            return true;
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get current user data
     * @return array|null User data or null if not logged in
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn() || !isset($_SESSION['user_id'])) {
            return null;
        }
        
        try {
            $userData = $this->db->getRow(
                "SELECT id, username, email, first_name, last_name, profile_picture, created_at
                 FROM users WHERE id = ?",
                [$_SESSION['user_id']]
            );
            
            return $userData;
        } catch (Exception $e) {
            error_log("Get current user error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update user profile
     * @param array $userData User data to update
     * @return bool Success status
     */
    public function updateProfile($userData) {
        if (!$this->isLoggedIn() || !isset($_SESSION['user_id'])) {
            return false;
        }
        
        try {
            // Fields that can be updated
            $allowedFields = ['first_name', 'last_name', 'profile_picture'];
            
            // Filter out disallowed fields
            $filteredData = array_intersect_key($userData, array_flip($allowedFields));
            
            if (empty($filteredData)) {
                return false; // No valid fields to update
            }
            
            // Update user
            $this->db->update(
                'users',
                $filteredData,
                'id = ?',
                [$_SESSION['user_id']]
            );
            
            return true;
        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Change user password
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return bool Success status
     */
    public function changePassword($currentPassword, $newPassword) {
        if (!$this->isLoggedIn() || !isset($_SESSION['user_id'])) {
            return false;
        }
        
        try {
            // Get current user data
            $user = $this->db->getRow(
                "SELECT password FROM users WHERE id = ?",
                [$_SESSION['user_id']]
            );
            
            if (!$user) {
                return false;
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return false; // Incorrect current password
            }
            
            // Hash new password
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $this->db->update(
                'users',
                ['password' => $passwordHash],
                'id = ?',
                [$_SESSION['user_id']]
            );
            
            return true;
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return false;
        }
    }
}

signup.php
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

// Process signup form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $userData = array("username" => $username, "email"=>$email ,"password"=>$password);

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        $Auth = new Auth();
        $result = $Auth->register($userData);
        
        if ($result) {
            $success = "Registration successful! Please check your email to verify your account.";
        } //else {
        //     $error = $result['message'];
        // }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - DoodleSense AI</title>
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
            
            <form class="auth-form" method="POST" action="signup.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Choose a username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                    </div>
                    <div class="password-strength" id="password-strength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                </div>
                
                <div class="form-options">
                    <div class="terms">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary btn-full">Create Account</button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in</a></p>
                <a href="index.php" class="back-to-home">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <script src="js/auth.js"></script>
</body>
</html>



signin.php
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

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']); //filter_var($_POST['uid'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "All fields are required";
    } else {
        $Auth = new Auth();
        $result = $Auth->login($email, $password);
        
        if ($result) {
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        }  /*else {
            $error = $result['message'];
        }*/
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DoodleSense AI</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>DoodleSense AI</h1>
                <p>Sign in to your account</p>
            </div>
            
            <?php if (!empty($error)): ?>
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
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
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
                
                <button type="submit" class="btn-primary btn-full">Sign In</button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="signup.php">Sign up</a></p>
                <a href="index.php" class="back-to-home">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <script src="js/auth.js"></script>
</body>
</html>

make login,signup fully compatible
*/