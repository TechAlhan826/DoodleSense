<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$drawing = null;
$drawing_id = null;

// Check if editing an existing drawing
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $drawing_id = (int)$_GET['edit'];
    $drawing = get_drawing($drawing_id, $user_id);
    
    if (!$drawing) {
        header("Location: dashboard.php");
        exit();
    }
}

// Handle saving drawing data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['drawing_data'])) {
    $title = $_POST['title'] ?? 'Untitled Drawing';
    $description = $_POST['description'] ?? '';
    $drawing_data = $_POST['drawing_data'];
    
    $db = Database::getInstance();
    
    $data = [
        'user_id' => $user_id,
        'title' => $title,
        'description' => $description,
        'drawing_data' => $drawing_data
    ];
    
    if ($drawing_id) {
        // Update existing drawing
        $db->update('drawings', $data, 'id = :id', [':id' => $drawing_id]);
    } else {
        // Create new drawing
        $db->insert('drawings', $data);
    }
    
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($drawing['title'] ?? 'New Drawing') ?> - DoodleSense AI</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            overflow: hidden;
        }
        
        /* Drawing Container */
        .drawing-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100%;
        }
        
        /* Header Styles */
        .drawing-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .header-left, .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .back-btn {
            color: #6c757d;
            text-decoration: none;
            font-size: 18px;
        }
        
        .drawing-title input {
            border: none;
            padding: 5px;
            font-size: 18px;
            width: 250px;
            outline: none;
        }
        
        .drawing-title input:focus {
            border-bottom: 2px solid #007bff;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6c757d;
        }
        
        /* Drawing Board */
        .drawing-board {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        /* Toolbar */
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 10px 20px;
            background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .tool-group {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 0 10px;
            border-right: 1px solid #e0e0e0;
        }
        
        .tool-group:last-child {
            border-right: none;
        }
        
        .tool {
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            border-radius: 4px;
            color: #6c757d;
            transition: all 0.2s;
            background: none;
            border: none;
        }
        
        .tool:hover {
            background-color: #f1f3f4;
            color: #007bff;
        }
        
        .tool.active {
            background-color: #e8f0fe;
            color: #007bff;
        }
        
        /* Color Picker */
        .color-picker {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .current-color {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid #e0e0e0;
            background-color: #000000;
            cursor: pointer;
        }
        
        #color-picker {
            opacity: 0;
            position: absolute;
            width: 1px;
            height: 1px;
        }
        
        .color-presets {
            display: flex;
            gap: 5px;
        }
        
        .color-preset {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            border: 1px solid #e0e0e0;
        }
        
        /* Brush Size */
        .brush-size {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Shape Options */
        .shape-options {
            display: none;
            position: absolute;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 10px;
            z-index: 100;
            top: 160px;
            left: 150px;
        }
        
        .shape-type {
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            border-radius: 4px;
            color: #6c757d;
        }
        
        .shape-type.active {
            background-color: #e8f0fe;
            color: #007bff;
        }
        
        /* Canvas Container */
        .canvas-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
            overflow: hidden;
            position: relative;
        }
        
        #drawing-canvas {
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        /* Status Bar */
        .status-bar {
            display: flex;
            justify-content: space-between;
            padding: 5px 20px;
            background-color: #ffffff;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #6c757d;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .close-modal {
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 15px 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        
        .drawing-preview {
            margin-top: 15px;
            text-align: center;
        }
        
        .drawing-preview img {
            max-width: 100%;
            max-height: 200px;
            border: 1px solid #e0e0e0;
        }
        
        /* AI Modal Specific */
        .ai-loading {
            text-align: center;
            padding: 20px 0;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .ai-drawing-preview {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .ai-drawing-preview img {
            max-width: 100%;
            max-height: 200px;
            border: 1px solid #e0e0e0;
        }
        
        .confidence-score {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .progress-bar {
            flex: 1;
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress {
            height: 100%;
            background-color: #28a745;
            width: 0%;
        }
        
        /* Text Input for Canvas */
        .canvas-text-input {
            position: absolute;
            border: 1px dashed #000;
            background: rgba(255, 255, 255, 0.9);
            padding: 5px;
            min-width: 100px;
            font-family: Arial, sans-serif;
            z-index: 1000;
            resize: both;
        }


        /* Add these styles to your existing CSS */

/* Modal animations */
.modal-content {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Loading spinner enhancement */
.spinner {
    border-width: 4px;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Loading message animations */
.loading-message {
    transition: opacity 0.2s ease;
}

/* Results container animations */
.ai-result .result-container {
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Progress bar pulse animation for low values */
.progress-bar .progress[style*="width: 0"] {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 0.6; }
    50% { opacity: 1; }
    100% { opacity: 0.6; }
}

/* Make the modal more responsive on mobile */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        max-width: 500px;
    }
    
    .ai-result .result-container {
        flex-direction: column;
    }
}
    </style>
</head>
<body>

    <div class="drawing-container">
        <div class="drawing-header">
            <div class="header-left">
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="drawing-title">
                    <input type="text" id="drawing-title" placeholder="Untitled Drawing" value="<?php echo ($drawing) ? htmlspecialchars($drawing['title']) : ''; ?>">
                </div>
            </div>
            <div class="header-right">
                <button id="save-btn" class="btn-primary">
                    <i class="fas fa-save"></i>
                    <span>Save</span>
                </button>
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span><?php echo htmlspecialchars($username); ?></span>
                </div>
            </div>
        </div>

        <div class="drawing-board">
            <div class="toolbar">
                <div class="tool-group">
                    <button class="tool" data-tool="select" title="Select">
                        <i class="fas fa-mouse-pointer"></i>
                    </button>
                    <button class="tool active" data-tool="pencil" title="Pencil">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="tool" data-tool="shape" title="Shape">
                        <i class="fas fa-shapes"></i>
                    </button>
                    <button class="tool" data-tool="text" title="Text">
                        <i class="fas fa-font"></i>
                    </button>
                    <button class="tool" data-tool="image" title="Image">
                        <i class="fas fa-image"></i>
                    </button>
                </div>

                <div class="tool-group">
                    <button class="tool tool-undo" data-action="undo" title="Undo">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button class="tool tool-redo" data-action="redo" title="Redo">
                        <i class="fas fa-redo"></i>
                    </button>
                    <button class="tool tool-eraser" data-tool="eraser" title="Eraser">
                        <i class="fas fa-eraser"></i>
                    </button>
                    <button class="tool tool-clear" data-action="clear" title="Clear All">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>

                <div class="tool-group">
                    <div class="color-picker">
                        <div class="current-color" id="current-color"></div>
                        <input type="color" id="color-picker" value="#000000">
                        <div class="color-presets">
                            <div class="color-preset" style="background-color: #000000" data-color="#000000"></div>
                            <div class="color-preset" style="background-color: #ff0000" data-color="#ff0000"></div>
                            <div class="color-preset" style="background-color: #00ff00" data-color="#00ff00"></div>
                            <div class="color-preset" style="background-color: #0000ff" data-color="#0000ff"></div>
                            <div class="color-preset" style="background-color: #ffff00" data-color="#ffff00"></div>
                            <div class="color-preset" style="background-color: #ff00ff" data-color="#ff00ff"></div>
                        </div>
                    </div>
                    <div class="brush-size">
                        <label for="brush-size">Size</label>
                        <input type="range" id="brush-size" min="1" max="50" value="5">
                        <span id="brush-size-value">5px</span>
                    </div>
                </div>

                <div class="tool-group shape-options">
                    <div class="shape-type active" data-shape="rectangle" title="Rectangle">
                        <i class="fas fa-square"></i>
                    </div>
                    <div class="shape-type" data-shape="circle" title="Circle">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="shape-type" data-shape="line" title="Line">
                        <i class="fas fa-slash"></i>
                    </div>
                    <div class="shape-type" data-shape="triangle" title="Triangle">
                        <i class="fas fa-play"></i>
                    </div>
                </div>

                <div class="tool-group">
                    <button class="tool tool-download" data-action="download" title="Download">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="tool tool-ai" data-action="recognize" title="AI Recognition">
                        <i class="fas fa-robot"></i>
                    </button>
                </div>
            </div>

            <div class="canvas-container">
                <canvas id="drawing-canvas"></canvas>
            </div>
        </div>

        <div class="status-bar">
            <div class="canvas-info">
                <span id="canvas-size">800 x 600</span>
                <span id="zoom-level">100%</span>
            </div>
            <div class="ai-status">
                <i class="fas fa-robot"></i>
                <span id="ai-message">AI Ready</span>
            </div>
        </div>
    </div>

  



  <!-- AI Recognition Modal -->
<!-- AI Recognition Modal -->
<div class="modal" id="ai-modal">
    <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(90deg, #4776E6, #8E54E9); color: white;">
            <h3><i class="fas fa-robot"></i> AI Recognition</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <!-- Loading state -->
            <div class="ai-loading">
                <div class="spinner" style="border-top-color: #4776E6;"></div>
                <p style="color: #4776E6; font-weight: bold; margin-top: 15px;">Analyzing your drawing...</p>
                <div class="loader-messages">
                    <p class="loading-message">Examining lines and shapes...</p>
                </div>
            </div>
            
            <!-- Results state -->
            <div class="ai-result" style="display: none;">
                <div class="result-container" style="display: flex; flex-direction: column; gap: 20px;">
                    <!-- Preview with border -->
                    <div class="ai-drawing-preview" style="border: 3px solid #8E54E9; border-radius: 10px; padding: 10px; text-align: center;">
                        <h4 style="margin-top: 0; color: #8E54E9;"><i class="fas fa-image"></i> Your Drawing</h4>
                        <img id="ai-preview-image" src="" alt="Drawing Preview" style="max-width: 100%; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    </div>
                    
                    <!-- Analysis results with animated entry -->
                    <div class="ai-analysis" style="background-color: #f9f9f9; border-radius: 10px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                        <h4 style="color: #4776E6; margin-top: 0; display: flex; align-items: center;">
                            <i class="fas fa-brain" style="margin-right: 10px;"></i> Analysis Results
                        </h4>
                        
                        <div class="recognition-result">
                            <div class="result-icon" style="background-color: #8E54E9; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 15px auto;">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            
                            <div id="ai-recognition-text" style="background-color: white; border-left: 4px solid #4776E6; padding: 15px; border-radius: 0 8px 8px 0; margin-bottom: 20px; font-size: 16px; line-height: 1.6; min-height: 50px;"></div>
                            
                            <div class="confidence-score" style="background-color: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                    <span style="font-weight: bold; color: #333;"><i class="fas fa-chart-line" style="color: #4776E6;"></i> Confidence Level</span>
                                    <span id="confidence-value" style="font-weight: bold; color: #4776E6;">0%</span>
                                </div>
                                <div class="progress-bar" style="height: 20px; background-color: #f0f0f0; border-radius: 10px; overflow: hidden;">
                                    <div id="confidence-progress" class="progress" style="height: 100%; background: linear-gradient(90deg, #4776E6, #8E54E9); width: 0%; transition: width 1s ease-in-out;"></div>
                                </div>
                                <div id="confidence-message" style="margin-top: 10px; font-size: 14px; text-align: right; font-style: italic; color: #666;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #eee; padding-top: 15px;">
            <p id="ai-message" style="margin-right: auto; color: #4776E6; font-weight: bold;">AI Ready</p>
            <button class="btn-secondary close-ai-modal" style="background: linear-gradient(90deg, #4776E6, #8E54E9); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: all 0.3s ease;">Close</button>
        </div>
    </div>
</div>


            <script src="js/drawing.js"></script>

</body>
</html>