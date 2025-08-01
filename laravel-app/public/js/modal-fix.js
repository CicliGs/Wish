// Modal Z-Index Fix
document.addEventListener('DOMContentLoaded', function() {
    // Fix modal z-index when modal is shown
    document.addEventListener('shown.bs.modal', function(event) {
        const modal = event.target;
        
        if (modal) {
            // Special handling for achievements modal
            if (modal.id === 'allAchievementsModal') {
                modal.style.zIndex = '999999';
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.style.zIndex = '1000000';
                }
                const modalDialog = modal.querySelector('.modal-dialog');
                if (modalDialog) {
                    modalDialog.style.zIndex = '1000001';
                }
                const btnClose = modal.querySelector('.btn-close');
                if (btnClose) {
                    btnClose.style.zIndex = '1000002';
                }
                
                // Hide backdrop for achievements modal
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(function(backdrop) {
                    backdrop.style.display = 'none';
                    backdrop.style.opacity = '0';
                    backdrop.style.visibility = 'hidden';
                    backdrop.style.pointerEvents = 'none';
                });
            } else {
                // Regular modal handling
                modal.style.zIndex = '99999';
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.style.zIndex = '100000';
                }
                const modalDialog = modal.querySelector('.modal-dialog');
                if (modalDialog) {
                    modalDialog.style.zIndex = '100001';
                }
                
                // Show backdrop for regular modals
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(function(backdrop) {
                    backdrop.style.display = 'block';
                    backdrop.style.opacity = '0.5';
                    backdrop.style.visibility = 'visible';
                    backdrop.style.pointerEvents = 'auto';
                    backdrop.style.zIndex = '99997';
                });
            }
        }
    });
    
    // Fix modal backdrop z-index when modal is about to show
    document.addEventListener('show.bs.modal', function(event) {
        const modal = event.target;
        
        setTimeout(function() {
            if (modal.id === 'allAchievementsModal') {
                // Hide backdrop for achievements modal
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(function(backdrop) {
                    backdrop.style.display = 'none';
                    backdrop.style.opacity = '0';
                    backdrop.style.visibility = 'hidden';
                    backdrop.style.pointerEvents = 'none';
                });
            } else {
                // Show backdrop for regular modals
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(function(backdrop) {
                    backdrop.style.display = 'block';
                    backdrop.style.opacity = '0.5';
                    backdrop.style.visibility = 'visible';
                    backdrop.style.pointerEvents = 'auto';
                    backdrop.style.zIndex = '99997';
                });
            }
        }, 10);
    });
    
    // Monitor for dynamically created modal backdrops
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('modal-backdrop')) {
                        // Check if achievements modal is open
                        const achievementsModal = document.getElementById('allAchievementsModal');
                        if (achievementsModal && achievementsModal.classList.contains('show')) {
                            // Hide backdrop for achievements modal
                            node.style.display = 'none';
                            node.style.opacity = '0';
                            node.style.visibility = 'hidden';
                            node.style.pointerEvents = 'none';
                        } else {
                            // Show backdrop for regular modals
                            node.style.display = 'block';
                            node.style.opacity = '0.5';
                            node.style.visibility = 'visible';
                            node.style.pointerEvents = 'auto';
                            node.style.zIndex = '99997';
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
});

// Function to force fix all modal z-indexes
window.fixModalZIndex = function() {
    const modals = document.querySelectorAll('.modal');
    const backdrops = document.querySelectorAll('.modal-backdrop');
    
    modals.forEach(function(modal) {
        // Special handling for achievements modal
        if (modal.id === 'allAchievementsModal') {
            modal.style.zIndex = '999999';
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.zIndex = '1000000';
            }
            const modalDialog = modal.querySelector('.modal-dialog');
            if (modalDialog) {
                modalDialog.style.zIndex = '1000001';
            }
            const btnClose = modal.querySelector('.btn-close');
            if (btnClose) {
                btnClose.style.zIndex = '1000002';
            }
            
            // Hide backdrop for achievements modal
            backdrops.forEach(function(backdrop) {
                backdrop.style.display = 'none';
                backdrop.style.opacity = '0';
                backdrop.style.visibility = 'hidden';
                backdrop.style.pointerEvents = 'none';
            });
        } else {
            // Regular modal handling
            modal.style.zIndex = '99999';
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.zIndex = '100000';
            }
            const modalDialog = modal.querySelector('.modal-dialog');
            if (modalDialog) {
                modalDialog.style.zIndex = '100001';
            }
            
            // Show backdrop for regular modals
            backdrops.forEach(function(backdrop) {
                backdrop.style.display = 'block';
                backdrop.style.opacity = '0.5';
                backdrop.style.visibility = 'visible';
                backdrop.style.pointerEvents = 'auto';
                backdrop.style.zIndex = '99997';
            });
        }
    });
};

// Fix z-index every 100ms for the first 2 seconds after page load
let fixCount = 0;
const fixInterval = setInterval(function() {
    if (fixCount < 20) {
        window.fixModalZIndex();
        fixCount++;
    } else {
        clearInterval(fixInterval);
    }
}, 100); 