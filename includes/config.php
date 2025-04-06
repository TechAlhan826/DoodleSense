<?php
/**
 * Configuration file for DoodleSense AI
 * Contains database settings, application settings, and constants
 */

// Error reporting - set to 0 in production


// Database configuration
define('DB_HOST', getenv('localhost'));
define('DB_NAME', getenv('doodlesense'));
define('DB_USER', getenv('root'));
define('DB_PASS', getenv('Pass@2025'));
define('DB_PORT', getenv('3306'));
define('DB_TYPE', 'mysql');

// Application settings
define('APP_NAME', 'DoodleSense AI');
define('APP_URL', 'http://localhost:5000'); // Change in production
define('APP_VERSION', '1.0.0');

// Security settings
define('SECURE_COOKIE', false); // Set to true in production with HTTPS
define('SESSION_LIFETIME', 3600); // 1 hour in seconds

// Email settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // tls or ssl
define('SMTP_AUTH', true);
define('SMTP_USERNAME', ''); // Add your email
define('SMTP_PASSWORD', ''); // Add your password or app password
define('SMTP_FROM_EMAIL', ''); // Add your email
define('SMTP_FROM_NAME', APP_NAME);

// File uploads
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'svg']);

// Gemini API Settings
define('GEMINI_API_KEY', getenv('YOUR-GEMINI-API-KEY') ?: ''); // Get from environment variable

// User registration settings
define('VERIFY_EMAIL', true); // Set to true to require email verification
define('PASSWORD_MIN_LENGTH', 8);

// Time settings
date_default_timezone_set('UTC');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

// Session settings
ini_set('session.cookie_httponly', 1); // Prevent JavaScript access to session cookie
ini_set('session.use_only_cookies', 1); // Force sessions to only use cookies
ini_set('session.cookie_lifetime', SESSION_LIFETIME); // Session cookie lifetime

// Set session cookie parameters
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path' => '/',
    'domain' => '',
    'secure' => SECURE_COOKIE,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
}