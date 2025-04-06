/**
 * Dashboard JavaScript
 * Handles dashboard interactions, drawing management, and user activity
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeSearchBar();
    initializeDrawingCards();
    initializeDeleteModal();
    initializeSidebar();
    initializeViewToggle();
});

/**
 * Initialize search functionality
 */
function initializeSearchBar() {
    const searchInput = document.querySelector('.search-bar input');
    
    if (!searchInput) return;
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const drawingCards = document.querySelectorAll('.drawing-card');
        
        drawingCards.forEach(card => {
            const drawingTitle = card.querySelector('h4').textContent.toLowerCase();
            
            if (drawingTitle.includes(searchTerm) || searchTerm === '') {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Show/hide empty state if no matching drawings
        const visibleCards = [...drawingCards].filter(card => card.style.display !== 'none');
        const emptyState = document.querySelector('.empty-state');
        
        if (visibleCards.length === 0 && emptyState) {
            if (searchTerm !== '') {
                // Customize empty state for search
                const emptyTitle = emptyState.querySelector('h3');
                const emptyText = emptyState.querySelector('p');
                if (emptyTitle) emptyTitle.textContent = 'No matching drawings';
                if (emptyText) emptyText.textContent = 'Try a different search term';
            }
            emptyState.style.display = 'block';
        } else if (emptyState) {
            emptyState.style.display = 'none';
        }
    });
}

/**
 * Initialize drawing card interactions
 */
function initializeDrawingCards() {
    // Download button functionality
    const downloadButtons = document.querySelectorAll('.download-btn');
    
    downloadButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const drawingId = this.dataset.id;
            
            // Track download
            fetch('api/track_download.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `drawing_id=${drawingId}`
            });
            
            // Get the drawing data and download it
            fetch(`api/get_drawings.php?id=${drawingId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.drawings.length > 0) {
                        const drawing = data.drawings[0];
                        const a = document.createElement('a');
                        a.href = drawing.drawing_data;
                        a.download = `${drawing.title.replace(/\s+/g, '_')}.png`;
                        a.click();
                    } else {
                        alert('Failed to download: Drawing not found');
                    }
                })
                .catch(error => {
                    console.error('Error downloading drawing:', error);
                    alert('Failed to download drawing. Please try again.');
                });
        });
    });
    
    // Delete button functionality
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const drawingId = this.dataset.id;
            
            // Save the drawing ID to the delete modal
            document.querySelector('.confirm-delete').dataset.id = drawingId;
            
            // Show delete modal
            document.getElementById('delete-modal').style.display = 'flex';
        });
    });
    
    // Click on card to view/edit
    const drawingCards = document.querySelectorAll('.drawing-card');
    
    drawingCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't navigate if clicking on action buttons
            if (e.target.closest('.drawing-actions')) {
                return;
            }
            
            const drawingId = this.dataset.id;
            window.location.href = `drawing_board.php?edit=${drawingId}`;
        });
    });
}

/**
 * Initialize delete confirmation modal
 */
function initializeDeleteModal() {
    const deleteModal = document.getElementById('delete-modal');
    if (!deleteModal) return;
    
    const closeButtons = deleteModal.querySelectorAll('.close-modal, .cancel-delete');
    const confirmButton = deleteModal.querySelector('.confirm-delete');
    
    // Close modal
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            deleteModal.style.display = 'none';
        });
    });
    
    // Close when clicking outside modal content
    deleteModal.addEventListener('click', function(e) {
        if (e.target === this) {
            deleteModal.style.display = 'none';
        }
    });
    
    // Delete drawing on confirmation
    if (confirmButton) {
        confirmButton.addEventListener('click', function() {
            const drawingId = this.dataset.id;
            
            // Show loading state
            const originalText = this.textContent;
            this.textContent = 'Deleting...';
            this.disabled = true;
            
            // Send delete request
            fetch('api/delete_drawing.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `drawing_id=${drawingId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the drawing card
                    const card = document.querySelector(`.drawing-card[data-id="${drawingId}"]`);
                    if (card) {
                        card.remove();
                    }
                    
                    // Update drawing count
                    const totalDrawingsElement = document.querySelector('.stat-card:first-child .stat-info h3');
                    if (totalDrawingsElement) {
                        const currentCount = parseInt(totalDrawingsElement.textContent);
                        totalDrawingsElement.textContent = Math.max(currentCount - 1, 0);
                    }
                    
                    // Show empty state if no drawings left
                    const remainingCards = document.querySelectorAll('.drawing-card');
                    if (remainingCards.length === 0) {
                        const emptyState = document.querySelector('.empty-state');
                        if (emptyState) {
                            const emptyTitle = emptyState.querySelector('h3');
                            const emptyText = emptyState.querySelector('p');
                            if (emptyTitle) emptyTitle.textContent = 'No drawings yet';
                            if (emptyText) emptyText.textContent = 'Start creating your first masterpiece!';
                            emptyState.style.display = 'block';
                        }
                    }
                    
                    // Close modal
                    deleteModal.style.display = 'none';
                } else {
                    alert('Failed to delete drawing: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error deleting drawing:', error);
                alert('Failed to delete drawing. Please try again.');
            })
            .finally(() => {
                // Restore button state
                this.textContent = originalText;
                this.disabled = false;
            });
        });
    }
}

/**
 * Initialize responsive sidebar for mobile
 */
function initializeSidebar() {
    // Check if we need to add mobile toggle button
    if (window.innerWidth <= 576 && !document.querySelector('.mobile-toggle')) {
        const toggle = document.createElement('div');
        toggle.className = 'mobile-toggle';
        toggle.innerHTML = '<i class="fas fa-bars"></i>';
        document.body.appendChild(toggle);
        
        toggle.addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
            
            // Change icon based on state
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('active')) {
                icon.className = 'fas fa-times';
            } else {
                icon.className = 'fas fa-bars';
            }
        });
    }
    
    // Close sidebar when clicking on main content (mobile only)
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.addEventListener('click', function() {
            if (window.innerWidth <= 576) {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.remove('active');
                
                const toggleIcon = document.querySelector('.mobile-toggle i');
                if (toggleIcon) {
                    toggleIcon.className = 'fas fa-bars';
                }
            }
        });
    }
}

/**
 * Initialize view toggle between gallery and list views
 */
function initializeViewToggle() {
    // Check if we're on gallery view
    const urlParams = new URLSearchParams(window.location.search);
    const view = urlParams.get('view');
    
    if (view === 'gallery') {
        // Handle gallery view specific functionality
        const drawingsGrid = document.querySelector('.drawings-grid');
        if (drawingsGrid) {
            drawingsGrid.classList.add('gallery-view');
        }
        
        // Update the active navigation
        const navItems = document.querySelectorAll('.sidebar-nav li');
        navItems.forEach(item => {
            item.classList.remove('active');
        });
        
        const galleryNav = document.querySelector('.sidebar-nav a[href="dashboard.php?view=gallery"]');
        if (galleryNav) {
            galleryNav.parentElement.classList.add('active');
        }
    }
}

/**
 * Refresh dashboard data
 */
function refreshDashboard() {
    // Reload drawing statistics
    fetch('api/get_dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update statistics
                document.querySelector('.stat-card:nth-child(1) .stat-info h3').textContent = data.total_drawings;
                document.querySelector('.stat-card:nth-child(2) .stat-info h3').textContent = data.recent_drawings;
                document.querySelector('.stat-card:nth-child(3) .stat-info h3').textContent = data.downloads;
                
                // Refresh drawings grid if needed
                if (data.should_refresh_grid) {
                    location.reload();
                }
            }
        })
        .catch(error => {
            console.error('Error refreshing dashboard:', error);
        });
}

// Setup auto-refresh if needed
let refreshInterval = null;

// Start auto-refresh when tab is visible
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        if (!refreshInterval) {
            refreshInterval = setInterval(refreshDashboard, 60000); // Refresh every minute
            refreshDashboard(); // Refresh immediately
        }
    } else {
        // Clear interval when tab is not visible
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }
});

// Start refresh when page loads if visible
if (document.visibilityState === 'visible') {
    refreshInterval = setInterval(refreshDashboard, 60000); // Refresh every minute
}
