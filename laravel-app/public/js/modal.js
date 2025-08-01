// Modal Management Functions
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    const backdrop = document.querySelector('.modal-backdrop');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
    }
    if (backdrop) {
        backdrop.remove();
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