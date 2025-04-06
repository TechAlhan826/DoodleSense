

// Main drawing variables
let canvas, ctx;
let isDrawing = false;
let lastX = 0, lastY = 0;
let brushSize = 5;
let currentColor = '#000000';
let currentTool = 'pencil';
let currentShape = 'rectangle';
let drawingHistory = [];
let historyIndex = -1;
let drawingData = null;

// Text tool variables
let isAddingText = false;
let textPosition = { x: 0, y: 0 };
let textInput = null;

// Shape tool variables
let shapeStartX = 0, shapeStartY = 0;
let tempCanvas, tempCtx;

document.addEventListener('DOMContentLoaded', function() {
    initializeCanvas();
    initializeTools();
    initializeColorPicker();
    initializeBrushSize();
    initializeShapeOptions();
    initializeActionButtons();
    initializeModals();
    loadExistingDrawing();
});

/**
 * Initialize the canvas and its context
 */
function initializeCanvas() {
    canvas = document.getElementById('drawing-canvas');
    ctx = canvas.getContext('2d');
    
    // Create temporary canvas for shape previews
    tempCanvas = document.createElement('canvas');
    tempCtx = tempCanvas.getContext('2d');
    
    // Set canvas size
    resizeCanvas();
    window.addEventListener('resize', debounce(resizeCanvas, 250));
    
    // Set up event listeners for drawing
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    
    // Touch support
    canvas.addEventListener('touchstart', handleTouchStart, { passive: false });
    canvas.addEventListener('touchmove', handleTouchMove, { passive: false });
    canvas.addEventListener('touchend', handleTouchEnd, { passive: false });
    
    // Initialize with a blank state in history
    saveToHistory();
}

/**
 * Recognize the drawing using Gemini API
 */
// Find all AI recognition buttons and attach the event handler document.addEventListener('DOMContentLoaded', function() {
    // Other initialization code...
    
    // AI Recognition button event handler
    const aiButtons = document.querySelectorAll('.tool-ai, [data-action="recognize"]');
    aiButtons.forEach(button => {
        button.addEventListener('click', recognizeDrawing);
    });


// Define the recognizeDrawing function
// Add this to your existing JavaScript, right before or after the recognizeDrawing function

// Loading messages to display while the AI is analyzing
const loadingMessages = [
    "Examining lines and shapes...",
    "Analyzing drawing patterns...",
    "Identifying potential objects...",
    "Cross-referencing visual elements...",
    "Almost done with analysis..."
];

// Function to cycle through loading messages
function cycleLoadingMessages() {
    let messageIndex = 0;
    const messageElement = document.querySelector('.loading-message');
    
    return setInterval(() => {
        messageElement.textContent = loadingMessages[messageIndex];
        messageElement.style.opacity = 0;
        
        setTimeout(() => {
            messageElement.style.opacity = 1;
        }, 200);
        
        messageIndex = (messageIndex + 1) % loadingMessages.length;
    }, 2000);
}

// Update the recognizeDrawing function
function recognizeDrawing() {
    // Show AI modal
    const aiModal = document.getElementById('ai-modal');
    aiModal.style.display = 'flex';
    
    // Reset previous results
    document.getElementById('ai-recognition-text').textContent = '';
    document.getElementById('confidence-progress').style.width = '0%';
    document.getElementById('confidence-value').textContent = '0%';
    
    // Show loading state, hide results
    document.querySelector('.ai-loading').style.display = 'block';
    document.querySelector('.ai-result').style.display = 'none';
    
    // Start cycling through loading messages
    const messageInterval = cycleLoadingMessages();
    
    // Set preview image
    const tempCanvas = document.createElement('canvas');
    const tempCtx = tempCanvas.getContext('2d');
    tempCanvas.width = canvas.width;
    tempCanvas.height = canvas.height;
    
    // Fill with white background
    tempCtx.fillStyle = '#FFFFFF';
    tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
    
    // Draw the original canvas content
    tempCtx.drawImage(canvas, 0, 0);
  
    const drawingData = tempCanvas.toDataURL('image/png');
    document.getElementById('ai-preview-image').src = drawingData;
    
    // Send drawing to API for recognition
    const formData = new FormData();
    formData.append('image_data', drawingData);
    
    // Make sure the path is correct - adjust if needed based on your file structure
    fetch('api/recognize_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Clear the message interval
        clearInterval(messageInterval);
        
        if (data.success) {
            // Hide loading, show results
            document.querySelector('.ai-loading').style.display = 'none';
            document.querySelector('.ai-result').style.display = 'block';
            
            // Add a delay for visual effect
            setTimeout(() => {
                // Set recognition text with typewriter effect
                typewriterEffect(document.getElementById('ai-recognition-text'), data.recognition_text);
                
                // Set confidence score with animation
                const confidence = data.confidence || 75; // Default to 75% if not provided
                animateConfidenceScore(confidence);
                
                // Update confidence message
                setConfidenceMessage(confidence);
                
                // Update status message
                document.getElementById('ai-message').textContent = 'AI Analysis Complete';
                document.getElementById('ai-message').style.color = '#4CAF50';
            }, 500);
        } else {
            throw new Error(data.message || 'Recognition failed');
        }
    })
    .catch(error => {
        console.error('Error recognizing drawing:', error);
        
        // Clear the message interval
        clearInterval(messageInterval);
        
        // Hide loading, show results with error
        document.querySelector('.ai-loading').style.display = 'none';
        document.querySelector('.ai-result').style.display = 'block';
        
        document.getElementById('ai-recognition-text').textContent = 
            'Sorry, I couldn\'t recognize your drawing. Please try again with a clearer image.';
        document.getElementById('ai-recognition-text').style.color = '#f44336';
        
        document.getElementById('confidence-progress').style.width = '0%';
        document.getElementById('confidence-value').textContent = '0%';
        document.getElementById('confidence-message').textContent = 'Recognition failed';
        
        // Update status message
        document.getElementById('ai-message').textContent = 'AI Analysis Failed';
        document.getElementById('ai-message').style.color = '#f44336';
    });
}

// Typewriter effect for displaying recognition text
function typewriterEffect(element, text) {
    let i = 0;
    element.textContent = '';
    
    const typing = setInterval(() => {
        if (i < text.length) {
            element.textContent += text.charAt(i);
            i++;
        } else {
            clearInterval(typing);
        }
    }, 20);
}

// Animate confidence score
function animateConfidenceScore(confidence) {
    const progressBar = document.getElementById('confidence-progress');
    const valueDisplay = document.getElementById('confidence-value');
    
    progressBar.style.width = '0%';
    let currentValue = 0;
    
    const increment = confidence / 40; // Will take about 1 second for the animation
    
    const animation = setInterval(() => {
        if (currentValue >= confidence) {
            clearInterval(animation);
            currentValue = confidence;
        } else {
            currentValue += increment;
        }
        
        progressBar.style.width = currentValue + '%';
        valueDisplay.textContent = Math.round(currentValue) + '%';
        
        // Change color based on confidence
        if (currentValue < 40) {
            progressBar.style.background = 'linear-gradient(90deg, #FF416C, #FF4B2B)';
            valueDisplay.style.color = '#FF416C';
        } else if (currentValue < 70) {
            progressBar.style.background = 'linear-gradient(90deg, #F2994A, #F2C94C)';
            valueDisplay.style.color = '#F2994A';
        } else {
            progressBar.style.background = 'linear-gradient(90deg, #4776E6, #8E54E9)';
            valueDisplay.style.color = '#4776E6';
        }
    }, 25);
}

// Set confidence message based on score
function setConfidenceMessage(confidence) {
    const messageElement = document.getElementById('confidence-message');
    
    if (confidence < 40) {
        messageElement.textContent = "Low confidence. Try adding more detail.";
    } else if (confidence < 70) {
        messageElement.textContent = "Moderate confidence in this guess.";
    } else if (confidence < 90) {
        messageElement.textContent = "High confidence in identification!";
    } else {
        messageElement.textContent = "Very high confidence! Clear drawing detected.";
    }
}

/**
 * Debounce function to prevent excessive resize calculations
 */
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

/**
 * Set proper canvas dimensions
 */
function resizeCanvas() {
    const container = document.querySelector('.canvas-container');
    const containerWidth = container.clientWidth;
    const containerHeight = container.clientHeight;
    
    // Store the current drawing
    const tempImg = new Image();
    tempImg.src = canvas.toDataURL();
    
    // Set display size (css pixels)
    canvas.style.width = containerWidth + 'px';
    canvas.style.height = containerHeight + 'px';
    
    // Set actual size in memory (scaled for retina displays)
    const dpr = window.devicePixelRatio || 1;
    canvas.width = containerWidth * dpr;
    canvas.height = containerHeight * dpr;
    
    // Set same size for temp canvas
    tempCanvas.width = canvas.width;
    tempCanvas.height = canvas.height;
    
    // Scale all drawing operations by dpr
    ctx.scale(dpr, dpr);
    tempCtx.scale(dpr, dpr);
    
    // Restore the drawing
    if (tempImg.complete) {
        ctx.drawImage(tempImg, 0, 0, containerWidth, containerHeight);
    } else {
        tempImg.onload = function() {
            ctx.drawImage(tempImg, 0, 0, containerWidth, containerHeight);
        };
    }
    
    // Update canvas info in status bar
    document.getElementById('canvas-size').textContent = `${Math.round(containerWidth)} x ${Math.round(containerHeight)}`;
}

/**
 * Initialize drawing tools
 */
function initializeTools() {
    const tools = document.querySelectorAll('.tool[data-tool]');
    
    tools.forEach(tool => {
        tool.addEventListener('click', function() {
            // Deactivate all tools
            document.querySelectorAll('.tool[data-tool]').forEach(t => t.classList.remove('active'));
            
            // Activate selected tool
            this.classList.add('active');
            
            // Set current tool
            currentTool = this.dataset.tool;
            
            // Show/hide shape options
            if (currentTool === 'shape') {
                document.querySelector('.shape-options').style.display = 'flex';
            } else {
                document.querySelector('.shape-options').style.display = 'none';
            }
            
            // Remove any active text input
            if (isAddingText && currentTool !== 'text') {
                commitTextToCanvas();
            }
            
            // Update cursor
            updateCursor();
        });
    });
}

/**
 * Update the cursor based on the current tool
 */
function updateCursor() {
    switch (currentTool) {
        case 'pencil':
            canvas.style.cursor = `url('data:image/svg+xml;utf8,') 7 7, auto`;
            break;
        case 'eraser':
            canvas.style.cursor = `url('data:image/svg+xml;utf8,') 10 10, auto`;
            break;
        case 'text':
            canvas.style.cursor = 'text';
            break;
        case 'select':
            canvas.style.cursor = 'default';
            break;
        case 'shape':
            canvas.style.cursor = 'crosshair';
            break;
        default:
            canvas.style.cursor = 'default';
    }
}

/**
 * Initialize color picker
 */
function initializeColorPicker() {
    const colorPicker = document.getElementById('color-picker');
    const currentColorEl = document.getElementById('current-color');
    const colorPresets = document.querySelectorAll('.color-preset');
    
    // Update color when picker changes
    colorPicker.addEventListener('input', function() {
        currentColor = this.value;
        currentColorEl.style.backgroundColor = currentColor;
    });
    
    // Set initial color
    currentColorEl.style.backgroundColor = currentColor;
    
    // Open color picker when clicking current color
    currentColorEl.addEventListener('click', function() {
        colorPicker.click();
    });
    
    // Handle color presets
    colorPresets.forEach(preset => {
        preset.addEventListener('click', function() {
            currentColor = this.dataset.color;
            colorPicker.value = currentColor;
            currentColorEl.style.backgroundColor = currentColor;
        });
    });
}

/**
 * Initialize brush size slider
 */
function initializeBrushSize() {
    const brushSizeInput = document.getElementById('brush-size');
    const brushSizeValue = document.getElementById('brush-size-value');
    
    brushSizeInput.addEventListener('input', function() {
        brushSize = parseInt(this.value);
        brushSizeValue.textContent = `${brushSize}px`;
        
        // Update cursor if we can
        updateCursor();
    });
}

/**
 * Initialize shape options
 */
function initializeShapeOptions() {
    const shapeOptions = document.querySelectorAll('.shape-type');
    
    shapeOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Deactivate all shape options
            document.querySelectorAll('.shape-type').forEach(s => s.classList.remove('active'));
            
            // Activate selected shape
            this.classList.add('active');
            
            // Set current shape
            currentShape = this.dataset.shape;
        });
    });
    
    // Set default active shape
    document.querySelector('.shape-type[data-shape="rectangle"]').classList.add('active');
}

document.querySelector('.tool-clear').addEventListener('click', function() {
    clearCanvas();
});

function clearCanvas() {
    ctx.fillStyle = '#FFFFFF';  // Set fill color to white
    ctx.fillRect(0, 0, canvas.width, canvas.height);  // Fill the entire canvas with white
    saveToHistory();  // Save the cleared state to history
}
/**
 * Initialize action buttons (undo, redo, clear, etc.)
 */
function initializeActionButtons() {
    // Undo action
    document.querySelector('.tool-undo').addEventListener('click', undo);
    
    // Redo action
    document.querySelector('.tool-redo').addEventListener('click', redo);
    
    // Clear canvas action
    document.querySelector('.tool-clear').addEventListener('click', function() {
        // Show confirmation modal
        document.getElementById('clear-modal').style.display = 'flex';
    });
    
    // Download action
    document.querySelector('.tool-download').addEventListener('click', downloadDrawing);
    
    // AI Recognition action
    document.querySelector('.tool-ai').addEventListener('click', recognizeDrawing);
    
    // Save button
    document.getElementById('save-btn').addEventListener('click', function() {
        // Set title in save modal
        const title = document.getElementById('drawing-title').value;
        document.getElementById('save-title').value = title || 'Untitled Drawing';
        
        // Create preview image
        const previewUrl = canvas.toDataURL('image/png');
        document.getElementById('save-preview').src = previewUrl;
        
        // Show save modal
        document.getElementById('save-modal').style.display = 'flex';
    });
}

/**
 * Initialize all modals and their buttons
 */
function initializeModals() {
    // Close modal buttons
    document.querySelectorAll('.close-modal, .close-ai-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            modal.style.display = 'none';
        });
    });
    
    // Close modals when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
    
    // Clear canvas confirmation
    document.querySelector('.confirm-clear').addEventListener('click', function() {
        clearCanvas();
        document.getElementById('clear-modal').style.display = 'none';
    });
    
    document.querySelector('.cancel-clear').addEventListener('click', function() {
        document.getElementById('clear-modal').style.display = 'none';
    });
    
    // Save drawing confirmation
    document.querySelector('.confirm-save').addEventListener('click', saveDrawing);
    
    document.querySelector('.cancel-save').addEventListener('click', function() {
        document.getElementById('save-modal').style.display = 'none';
    });
}

/**
 * Start drawing on canvas
 */
function startDrawing(e) {
    e.preventDefault();
    
    isDrawing = true;
    
    // Get correct position
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    
    lastX = (e.clientX - rect.left);
    lastY = (e.clientY - rect.top);
    
    // Tool-specific handling
    if (currentTool === 'text') {
        handleTextTool(lastX, lastY);
    } else if (currentTool === 'shape') {
        shapeStartX = lastX;
        shapeStartY = lastY;
        
        // Save the current canvas state for shape drawing
        tempCtx.clearRect(0, 0, tempCanvas.width, tempCanvas.height);
        tempCtx.drawImage(canvas, 0, 0);
    } else if (currentTool === 'image') {
        // Trigger file input click
        document.getElementById('image-upload').click();
    }
}

/**
 * Draw on the canvas
 */
function draw(e) {
    if (!isDrawing) return;
    e.preventDefault();
    
    // Get correct position
    const rect = canvas.getBoundingClientRect();
    const currentX = (e.clientX - rect.left);
    const currentY = (e.clientY - rect.top);
    
    // Different handling based on the current tool
    switch (currentTool) {
        case 'pencil':
            drawPencilLine(lastX, lastY, currentX, currentY);
            break;
        case 'eraser':
            erase(lastX, lastY, currentX, currentY);
            break;
        case 'shape':
            previewShape(shapeStartX, shapeStartY, currentX, currentY);
            break;
    }
    
    lastX = currentX;
    lastY = currentY;
}

/**
 * Stop drawing on canvas
 */
function stopDrawing(e) {
    if (!isDrawing) return;
    
    // Finalize shape if using shape tool
    if (currentTool === 'shape') {
        const rect = canvas.getBoundingClientRect();
        const currentX = (e.clientX - rect.left);
        const currentY = (e.clientY - rect.top);
        
        drawShape(shapeStartX, shapeStartY, currentX, currentY);
    }
    
    isDrawing = false;
    
    // Save current state to history
    saveToHistory();
}

/**
 * Draw a pencil line on canvas
 */
function drawPencilLine(x1, y1, x2, y2) {
    ctx.beginPath();
    ctx.strokeStyle = currentColor;
    ctx.lineWidth = brushSize;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.stroke();
}

/**
 * Erase content with the eraser tool
 */
function erase(x1, y1, x2, y2) {
    ctx.beginPath();
    ctx.strokeStyle = '#FFFFFF'; // White for eraser
    ctx.lineWidth = brushSize * 2; // Make eraser slightly larger
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.stroke();
}

/**
 * Handle text tool
 */
function handleTextTool(x, y) {
    // Remove any existing text input
    if (isAddingText) {
        commitTextToCanvas();
    }
    
    isAddingText = true;
    textPosition = { x, y };
    
    // Create text input element
    textInput = document.createElement('textarea');
    textInput.className = 'canvas-text-input';
    textInput.style.position = 'absolute';
    textInput.style.left = x + 'px';
    textInput.style.top = (y - 20) + 'px'; // Offset for better alignment
    textInput.style.border = '1px dashed ' + currentColor;
    textInput.style.color = currentColor;
    textInput.style.background = 'rgba(255, 255, 255, 0.7)';
    textInput.style.minWidth = '100px';
    textInput.style.minHeight = '30px';
    textInput.style.resize = 'both';
    textInput.style.overflow = 'hidden';
    textInput.style.fontFamily = 'Arial, sans-serif';
    textInput.style.fontSize = brushSize + 'px';
    textInput.style.zIndex = '100';
    
    document.querySelector('.canvas-container').appendChild(textInput);
    textInput.focus();
    
    // Handle blur event to commit text
    textInput.addEventListener('blur', function() {
        setTimeout(commitTextToCanvas, 100); // Small timeout to prevent conflicts
    });
    
    // Handle enter key press
    textInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            textInput.remove();
            isAddingText = false;
        }
    });
}

/**
 * Commit text from the input to the canvas
 */
function commitTextToCanvas() {
    if (!textInput || !isAddingText) return;
    
    const text = textInput.value.trim();
    if (text) {
        ctx.font = `${brushSize}px Arial, sans-serif`;
        ctx.fillStyle = currentColor;
        ctx.textBaseline = 'top';
        
        const lines = text.split('\n');
        const lineHeight = brushSize * 1.2;
        
        lines.forEach((line, index) => {
            ctx.fillText(line, textPosition.x, textPosition.y + (index * lineHeight));
        });
    }
    
    textInput.remove();
    isAddingText = false;
    saveToHistory();
}

/**
 * Preview shape while drawing
 */
function previewShape(startX, startY, endX, endY) {
    // Clear the canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Restore the original canvas state
    ctx.drawImage(tempCanvas, 0, 0);
    
    // Draw the shape based on type
    ctx.beginPath();
    ctx.strokeStyle = currentColor;
    ctx.lineWidth = brushSize;
    
    switch (currentShape) {
        case 'rectangle':
            ctx.rect(startX, startY, endX - startX, endY - startY);
            break;
        case 'circle':
            const radius = Math.sqrt(Math.pow(endX - startX, 2) + Math.pow(endY - startY, 2));
            ctx.arc(startX, startY, radius, 0, Math.PI * 2);
            break;
        case 'line':
            ctx.moveTo(startX, startY);
            ctx.lineTo(endX, endY);
            break;
        case 'triangle':
            ctx.moveTo(startX, startY);
            ctx.lineTo(endX, endY);
            ctx.lineTo(startX - (endX - startX), endY);
            ctx.closePath();
            break;
    }
    
    ctx.stroke();
}

/**
 * Draw finalized shape
 */
function drawShape(startX, startY, endX, endY) {
    // The shape is already drawn in the preview, no need to redraw
    // Just save the state
}



/**
 * Save current canvas state to history
 */
function saveToHistory() {
    // Remove all states after current index if we did some undos
    if (historyIndex < drawingHistory.length - 1) {
        drawingHistory = drawingHistory.slice(0, historyIndex + 1);
    }
    
    // Save current state
    drawingHistory.push(canvas.toDataURL());
    historyIndex = drawingHistory.length - 1;
    
    // Limit history size to prevent memory issues
    if (drawingHistory.length > 30) {
        drawingHistory.shift();
        historyIndex--;
    }
    
    // Enable/disable undo/redo buttons
    updateUndoRedoButtons();
}

/**
 * Update the state of undo/redo buttons
 */
function updateUndoRedoButtons() {
    const undoBtn = document.querySelector('.tool-undo');
    const redoBtn = document.querySelector('.tool-redo');
    
    // Enable/disable undo button
    if (historyIndex > 0) {
        undoBtn.classList.remove('disabled');
    } else {
        undoBtn.classList.add('disabled');
    }
    
    // Enable/disable redo button
    if (historyIndex < drawingHistory.length - 1) {
        redoBtn.classList.remove('disabled');
    } else {
        redoBtn.classList.add('disabled');
    }
}

/**
 * Undo the last action
 */
function undo() {
    if (historyIndex <= 0) return;
    
    historyIndex--;
    loadCanvasState(drawingHistory[historyIndex]);
    updateUndoRedoButtons();
}

/**
 * Redo a previously undone action
 */
function redo() {
    if (historyIndex >= drawingHistory.length - 1) return;
    
    historyIndex++;
    loadCanvasState(drawingHistory[historyIndex]);
    updateUndoRedoButtons();
}

/**
 * Load a canvas state from a data URL
 */
function loadCanvasState(dataUrl) {
    const img = new Image();
    img.onload = function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height, 0, 0, canvas.width / window.devicePixelRatio, canvas.height / window.devicePixelRatio);
    };
    img.src = dataUrl;
}

/**
 * Clear the canvas
 */
function clearCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    saveToHistory();
}

/**
 * Download the drawing as a PNG
 */
function downloadDrawing() {
    // Create a temporary canvas with white background
    const tempCanvas = document.createElement('canvas');
    const tempCtx = tempCanvas.getContext('2d');
    tempCanvas.width = canvas.width;
    tempCanvas.height = canvas.height;
    
    // Fill with white background
    tempCtx.fillStyle = '#FFFFFF';
    tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
    
    // Draw the original canvas content
    tempCtx.drawImage(canvas, 0, 0);
    
    const dataUrl = tempCanvas.toDataURL('image/png');
    const title = document.getElementById('drawing-title').value || 'drawing';
    const fileName = `${title.replace(/\s+/g, '_')}.png`;
    
    const a = document.createElement('a');
    a.href = dataUrl;
    a.download = fileName;
    a.click();
}


/**
 * Save the drawing to the server
 */
function saveDrawing() {
    const title = document.getElementById('save-title').value;
    const description = document.getElementById('save-description').value;
    const drawingData = canvas.toDataURL('image/png');
    const drawingId = document.getElementById('drawing-id').value;
    
    // Show loading state
    const saveButton = document.querySelector('.confirm-save');
    const originalText = saveButton.textContent;
    saveButton.textContent = 'Saving...';
    saveButton.disabled = true;
    
    // Prepare form data
    const formData = new FormData();
    formData.append('title', title);
    formData.append('description', description);
    formData.append('drawing_data', drawingData);
    
    if (drawingId) {
        formData.append('drawing_id', drawingId);
    }
    
    // Send to server
    fetch('api/save_drawing.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update drawing title and ID
            document.getElementById('drawing-title').value = title;
            document.getElementById('drawing-id').value = data.drawing_id;
            
            // Close modal
            document.getElementById('save-modal').style.display = 'none';
            
            // Show success message
            const aiMessage = document.getElementById('ai-message');
            aiMessage.textContent = 'Drawing saved successfully!';
            setTimeout(() => {
                aiMessage.textContent = 'AI Ready';
            }, 3000);
        } else {
            alert('Error saving drawing: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving drawing:', error);
        alert('Failed to save drawing. Please try again.');
    })
    .finally(() => {
        // Restore button state
        saveButton.textContent = originalText;
        saveButton.disabled = false;
    });
}



/**
 * Load an existing drawing if we're in edit mode
 */
function loadExistingDrawing() {
    const drawingData = document.getElementById('drawing-data').value;
    
    if (drawingData) {
        const img = new Image();
        img.onload = function() {
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height, 0, 0, canvas.width / window.devicePixelRatio, canvas.height / window.devicePixelRatio);
            saveToHistory();
        };
        img.src = drawingData;
    }
}

/**
 * Touch event handlers for mobile support
 */
function handleTouchStart(e) {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent('mousedown', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    canvas.dispatchEvent(mouseEvent);
}

function handleTouchMove(e) {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent('mousemove', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    canvas.dispatchEvent(mouseEvent);
}

function handleTouchEnd(e) {
    e.preventDefault();
    const mouseEvent = new MouseEvent('mouseup', {});
    canvas.dispatchEvent(mouseEvent);
}
