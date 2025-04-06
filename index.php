<?php
/**
 * DoodleSense AI - Landing Page
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Initialize Auth
$auth = new Auth();
$isLoggedIn = $auth->isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - AI-Powered Drawing Recognition</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="description" content="DoodleSense AI is a powerful web-based drawing application with AI recognition capabilities. Create, save, and analyze your drawings in real-time.">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <img src="generated-icon.png" alt="DoodleSense Logo">
                <h1>DoodleSense<span>AI</span></h1>
            </div>
            <nav class="main-nav">
                <button class="mobile-menu-toggle" aria-label="Toggle menu">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
                <ul class="nav-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#about">About</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li><a href="dashboard.php" class="btn btn-primary">Dashboard</a></li>
                        <li><a href="logout.php" class="btn btn-outline">Log Out</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="btn btn-outline">Login</a></li>
                        <li><a href="signup.php" class="btn btn-primary">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Bring Your <span class="accent">Drawings</span> to Life with AI</h1>
                <p>Draw, create, and let AI recognize your sketches in real-time. The most intuitive drawing experience on the web.</p>
                <div class="hero-buttons">
                    <?php if ($isLoggedIn): ?>
                        <a href="drawing_board.php" class="btn btn-large btn-primary">Start Drawing</a>
                        <a href="dashboard.php" class="btn btn-large btn-outline">View Your Drawings</a>
                    <?php else: ?>
                        <a href="signup.php" class="btn btn-large btn-primary">Get Started</a>
                        <a href="#features" class="btn btn-large btn-outline">Learn More</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1607350215478-b770b8095a20?q=80&w=1470&auto=format&fit=crop" alt="Drawing on DoodleSense AI">
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <h2 class="section-title">Key Features</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <h3>Intuitive Drawing Tools</h3>
                    <p>A comprehensive set of drawing tools including brushes, shapes, eraser, and text insertion.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3>AI Recognition</h3>
                    <p>Powered by Google's Gemini AI to recognize and analyze your drawings in real-time.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3>Unlimited History</h3>
                    <p>Undo and redo functionality with unlimited history steps for full control.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-cloud-download-alt"></i>
                    </div>
                    <h3>Export Options</h3>
                    <p>Download your masterpieces in PNG format or save them to your account.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <h3>Easy Sharing</h3>
                    <p>Share your drawings with friends or on social media with a single click.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile-Friendly</h3>
                    <p>Responsive design that works on all devices, from desktop to mobile.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="how-it-works">
        <div class="container">
            <h2 class="section-title">How It Works</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Create Your Account</h3>
                    <p>Sign up for a free account to get started with DoodleSense AI.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Draw Anything</h3>
                    <p>Use our intuitive tools to create your masterpiece.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>AI Recognition</h3>
                    <p>Let our AI analyze and recognize what you've drawn.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Save & Share</h3>
                    <p>Save your work and share it with the world.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <h2 class="section-title">About DoodleSense AI</h2>
                <p>DoodleSense AI was created as a project for UBCA204L course to demonstrate the integration of drawing tools with artificial intelligence. Our mission is to make digital drawing accessible to everyone while providing powerful AI-driven insights.</p>
                <p>Built with HTML, CSS, JavaScript, PHP, and SQL, DoodleSense showcases what can be achieved with standard web technologies.</p>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="generated-icon.png" alt="DoodleSense Logo">
                    <h2>DoodleSense<span>AI</span></h2>
                </div>
                <div class="footer-links">
                    <div class="link-group">
                        <h3>Navigation</h3>
                        <ul>
                            <li><a href="#features">Features</a></li>
                            <li><a href="#how-it-works">How It Works</a></li>
                            <li><a href="#about">About</a></li>
                        </ul>
                    </div>
                    <div class="link-group">
                        <h3>Account</h3>
                        <ul>
                            <?php if ($isLoggedIn): ?>
                                <li><a href="dashboard.php">Dashboard</a></li>
                                <li><a href="drawing_board.php">Drawing Board</a></li>
                                <li><a href="logout.php">Log Out</a></li>
                            <?php else: ?>
                                <li><a href="login.php">Login</a></li>
                                <li><a href="signup.php">Sign Up</a></li>
                                <li><a href="forgot_password.php">Forgot Password</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> DoodleSense AI. All rights reserved. Created for UBCA204L course project.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
