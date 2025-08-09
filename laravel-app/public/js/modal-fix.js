// Modal Fix Script
document.addEventListener('DOMContentLoaded', function() {
    // Fix modal z-index issues
    function fixModalZIndex() {
        const modals = document.querySelectorAll('.modal');
        const modalBackdrops = document.querySelectorAll('.modal-backdrop');
        const modalContents = document.querySelectorAll('.modal-content');
        const modalDialogs = document.querySelectorAll('.modal-dialog');
        
        modals.forEach(modal => {
            modal.style.zIndex = '99999';
        });
        
        modalBackdrops.forEach(backdrop => {
            backdrop.style.zIndex = '99997';
            backdrop.style.opacity = '0.5';
        });
        
        modalContents.forEach(content => {
            content.style.zIndex = '100000';
        });
        
        modalDialogs.forEach(dialog => {
            dialog.style.zIndex = '100001';
        });
    }

    // Fix close button styling
    function fixCloseButton() {
        const closeButtons = document.querySelectorAll('.btn-close');
        
        closeButtons.forEach(button => {
            // Apply styles directly
            button.style.borderRadius = '50%';
            button.style.padding = '0.5rem';
            button.style.transition = 'all 0.3s ease';
            button.style.zIndex = '100002';
            button.style.position = 'relative';
            button.style.transform = 'none';
            button.style.width = '32px';
            button.style.height = '32px';
            button.style.display = 'flex';
            button.style.alignItems = 'center';
            button.style.justifyContent = 'center';
            button.style.background = 'rgba(255, 255, 255, 0.9)';
            button.style.border = '1px solid rgba(102, 126, 234, 0.2)';
            button.style.margin = '0';
            button.style.opacity = '1';
            button.style.boxShadow = 'none';
            
            // Remove Bootstrap's default background image
            button.style.backgroundImage = 'none';
            button.style.backgroundSize = 'auto';
            button.style.backgroundRepeat = 'no-repeat';
            button.style.backgroundPosition = 'center';
            
            // Remove Bootstrap's default content and set our custom X
            button.innerHTML = '×';
            button.style.fontSize = '24px';
            button.style.fontWeight = 'bold';
            button.style.color = '#667eea';
            button.style.lineHeight = '1';
            button.style.textAlign = 'center';
            
            // Remove any pseudo-elements
            button.style.setProperty('--bs-btn-close-bg', 'none');
            button.style.setProperty('--bs-btn-close-focus-shadow', 'none');
            
            // Add hover event listener
            button.addEventListener('mouseenter', function() {
                this.style.background = 'rgba(102, 126, 234, 0.1)';
                this.style.borderColor = 'rgba(102, 126, 234, 0.5)';
                this.style.transform = 'none';
                this.style.boxShadow = '0 4px 12px rgba(102, 126, 234, 0.2)';
                this.style.opacity = '1';
                this.style.backgroundImage = 'none';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.background = 'rgba(255, 255, 255, 0.9)';
                this.style.borderColor = 'rgba(102, 126, 234, 0.2)';
                this.style.transform = 'none';
                this.style.boxShadow = 'none';
                this.style.opacity = '1';
                this.style.backgroundImage = 'none';
            });
            
            button.addEventListener('focus', function() {
                this.style.transform = 'none';
                this.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.3)';
                this.style.outline = 'none';
                this.style.opacity = '1';
                this.style.backgroundImage = 'none';
            });
            
            button.addEventListener('blur', function() {
                this.style.transform = 'none';
                this.style.boxShadow = 'none';
                this.style.opacity = '1';
                this.style.backgroundImage = 'none';
            });
        });
    }

    // Apply fixes immediately
    fixModalZIndex();
    fixCloseButton();

    // Apply fixes when modals are shown
    document.addEventListener('shown.bs.modal', function() {
        setTimeout(() => {
            fixModalZIndex();
            fixCloseButton();
        }, 100);
    });

    document.addEventListener('show.bs.modal', function() {
        setTimeout(() => {
            fixModalZIndex();
            fixCloseButton();
        }, 50);
    });

    // Use MutationObserver to detect dynamically added modals
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        if (node.classList && node.classList.contains('modal')) {
                            setTimeout(() => {
                                fixModalZIndex();
                                fixCloseButton();
                            }, 100);
                        }
                        if (node.querySelectorAll) {
                            const modals = node.querySelectorAll('.modal');
                            const closeButtons = node.querySelectorAll('.btn-close');
                            if (modals.length > 0 || closeButtons.length > 0) {
                                setTimeout(() => {
                                    fixModalZIndex();
                                    fixCloseButton();
                                }, 100);
                            }
                        }
                    }
                });
            }
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Special handling for achievement modal
    const achievementModal = document.getElementById('allAchievementsModal');
    if (achievementModal) {
        achievementModal.style.zIndex = '999999';
        const achievementContent = achievementModal.querySelector('.modal-content');
        const achievementDialog = achievementModal.querySelector('.modal-dialog');
        const achievementClose = achievementModal.querySelector('.btn-close');
        
        if (achievementContent) achievementContent.style.zIndex = '1000000';
        if (achievementDialog) achievementDialog.style.zIndex = '1000001';
        if (achievementClose) achievementClose.style.zIndex = '1000002';
    }

    // Hide backdrop for achievement modal
    const achievementBackdrops = document.querySelectorAll('.modal-backdrop');
    achievementBackdrops.forEach(backdrop => {
        if (backdrop.getAttribute('data-bs-target') === '#allAchievementsModal') {
            backdrop.style.display = 'none';
            backdrop.style.opacity = '0';
            backdrop.style.visibility = 'hidden';
            backdrop.style.pointerEvents = 'none';
        }
    });
}); 