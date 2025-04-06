<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Get user's drawings
$drawings = get_user_drawings($user_id);

// Rest of your dashboard HTML remains the same
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DoodleSense AI</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h1>DoodleSense AI</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <p>Artist</p>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="dashboard.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="drawing_board.php">
                            <i class="fas fa-paint-brush"></i>
                            <span>New Drawing</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?view=gallery">
                            <i class="fas fa-images"></i>
                            <span>My Gallery</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php">
                            <i class="fas fa-info-circle"></i>
                            <span>About</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search drawings...">
                </div>
                <div class="user-actions">
                    <a href="drawing_board.php" class="btn-primary">
                        <i class="fas fa-plus"></i>
                        New Drawing
                    </a>
                </div>
            </div>

            <div class="dashboard-header">
                <h2>Welcome back, <?php echo htmlspecialchars($username); ?>!</h2>
                <p>Here's your drawing activity</p>
            </div>

            <div class="drawing-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($drawings); ?></h3>
                        <p>Total Drawings</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count_recent_drawings($user_id, 7); ?></h3>
                        <p>Last 7 Days</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-download"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count_downloaded_drawings($user_id); ?></h3>
                        <p>Downloads</p>
                    </div>
                </div>
            </div>

            <div class="recent-drawings">
                <div class="section-header">
                    <h3>Recent Drawings</h3>
                    <a href="dashboard.php?view=gallery" class="view-all">View All</a>
                </div>

                <?php if(empty($drawings)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-paint-brush"></i>
                        </div>
                        <h3>No drawings yet</h3>
                        <p>Start creating your first masterpiece!</p>
                        <a href="drawing_board.php" class="btn-primary">Create New Drawing</a>
                    </div>
                <?php else: ?>
                    <div class="drawings-grid">
                        <?php 
                        // Display only last 6 drawings
                        $recent = array_slice($drawings, 0, 6);
                        foreach($recent as $drawing): 
                        ?>
                            <div class="drawing-card" data-id="<?php echo $drawing['id']; ?>">
                                <div class="drawing-preview">
                                    <img src="<?php echo $drawing['image_url']; ?>" alt="<?php echo htmlspecialchars($drawing['title']); ?>">
                                </div>
                                <div class="drawing-info">
                                    <h4><?php echo htmlspecialchars($drawing['title']); ?></h4>
                                    <p><?php echo format_date($drawing['created_at']); ?></p>
                                </div>
                                <div class="drawing-actions">
                                    <a href="drawing_board.php?edit=<?php echo $drawing['id']; ?>" class="edit-btn" title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <a href="#" class="download-btn" data-id="<?php echo $drawing['id']; ?>" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="#" class="delete-btn" data-id="<?php echo $drawing['id']; ?>" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="activity-timeline">
                <div class="section-header">
                    <h3>Recent Activity</h3>
                </div>
                
                <?php if(empty($drawings)): ?>
                    <div class="empty-state">
                        <p>No activity yet. Start drawing to see your activity here.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php 
                        $activities = get_user_activities($user_id);
                        foreach($activities as $activity): 
                        ?>
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <?php if($activity['type'] == 'create'): ?>
                                        <i class="fas fa-plus-circle"></i>
                                    <?php elseif($activity['type'] == 'edit'): ?>
                                        <i class="fas fa-pencil-alt"></i>
                                    <?php elseif($activity['type'] == 'download'): ?>
                                        <i class="fas fa-download"></i>
                                    <?php else: ?>
                                        <i class="fas fa-check-circle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-content">
                                    <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($activity['description']); ?></p>
                                    <span class="timeline-date"><?php echo format_date($activity['created_at']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Drawing Confirmation Modal -->
    <div class="modal" id="delete-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Delete Drawing</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this drawing? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary cancel-delete">Cancel</button>
                <button class="btn-danger confirm-delete">Delete</button>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>
