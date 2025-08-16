// Modal Management Functions for Bootstrap 5
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        // Use Bootstrap 5 modal API
        const bootstrapModal = bootstrap.Modal.getInstance(modal);
        if (bootstrapModal) {
            bootstrapModal.hide();
        } else {
            // Fallback for manual modal management
            modal.style.display = 'none';
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            modal.removeAttribute('aria-modal');
            
            // Remove backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }
    }
}

// Close modal by clicking on backdrop
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-backdrop')) {
        const modal = document.querySelector('.modal.show');
        if (modal) {
            closeModal(modal.id);
        }
    }
});

// Close modal by pressing Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.querySelector('.modal.show');
        if (modal) {
            closeModal(modal.id);
        }
    }
});

// Ensure modals are properly initialized
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modals with Bootstrap 5
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        new bootstrap.Modal(modal, {
            backdrop: true,
            keyboard: true,
            focus: true
        });
    });
}); 