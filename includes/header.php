<?php
/**
 * Common header for DoodleSense AI
 */

// Determine current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : APP_NAME . ' - Draw, Create, Recognize'; ?>">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/style.css">
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1><a href="index.php"><?php echo APP_NAME; ?></a></h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>>Home</a></li>
                    <li><a href="index.php#features" <?php echo $current_page == 'features.php' ? 'class="active"' : ''; ?>>Features</a></li>
                    <li><a href="index.php#gallery" <?php echo $current_page == 'gallery.php' ? 'class="active"' : ''; ?>>Gallery</a></li>
                    <li><a href="index.php#about" <?php echo $current_page == 'about.php' ? 'class="active"' : ''; ?>>About</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php" class="btn-secondary" <?php echo $current_page == 'dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a></li>
                        <li><a href="logout.php" class="btn-outline">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="btn-secondary" <?php echo $current_page == 'login.php' ? 'class="active"' : ''; ?>>Login</a></li>
                        <li><a href="signup.php" class="btn-primary" <?php echo $current_page == 'signup.php' ? 'class="active"' : ''; ?>>Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
