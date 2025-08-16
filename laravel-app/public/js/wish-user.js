document.addEventListener('DOMContentLoaded', function() {
    // CSRF token для AJAX запросов
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Handle reserve button clicks
    document.querySelectorAll('.reserve-btn').forEach(function(button) {
        button.addEventListener('click', reserveHandler);
    });

    // Handle unreserve button clicks
    document.querySelectorAll('.unreserve-btn').forEach(function(button) {
        button.addEventListener('click', unreserveHandler);
    });

    function reserveHandler(e) {
        e.preventDefault();
        const wishId = this.getAttribute('data-wish-id');
        const button = this;
        
        // Disable button during request
        button.disabled = true;
        button.textContent = 'Обработка...';
        
        fetch(`/ajax/wishes/${wishId}/reserve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('success', data.message);
                
                // Update button to unreserve
                button.className = 'wish-btn btn-danger w-100 unreserve-btn';
                button.textContent = 'Отменить резерв';
                button.classList.remove('reserve-btn');
                button.classList.add('unreserve-btn');
                
                // Update wish status badge
                const wishCard = button.closest('.wish-card');
                const statusBadge = wishCard.querySelector('.badge');
                if (statusBadge) {
                    statusBadge.textContent = 'Зарезервировано вами';
                    statusBadge.className = 'badge bg-success ms-2';
                }
                
                // Remove old event listener and add new one
                button.removeEventListener('click', reserveHandler);
                button.addEventListener('click', unreserveHandler);
                
                // Re-enable button
                button.disabled = false;
            } else {
                showAlert('error', data.message);
                // Re-enable button
                button.disabled = false;
                button.textContent = 'Зарезервировать';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Произошла ошибка');
            // Re-enable button
            button.disabled = false;
            button.textContent = 'Зарезервировать';
        });
    }

    function unreserveHandler(e) {
        e.preventDefault();
        const wishId = this.getAttribute('data-wish-id');
        const button = this;
        
        // Disable button during request
        button.disabled = true;
        button.textContent = 'Обработка...';
        
        fetch(`/ajax/wishes/${wishId}/unreserve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('success', data.message);
                
                // Update button to reserve
                button.className = 'wish-btn btn-success w-100 reserve-btn';
                button.textContent = 'Зарезервировать';
                button.classList.remove('unreserve-btn');
                button.classList.add('reserve-btn');
                
                // Update wish status badge
                const wishCard = button.closest('.wish-card');
                const statusBadge = wishCard.querySelector('.badge');
                if (statusBadge) {
                    statusBadge.textContent = 'Доступно';
                    statusBadge.className = 'badge bg-secondary ms-2';
                }
                
                // Remove old event listener and add new one
                button.removeEventListener('click', unreserveHandler);
                button.addEventListener('click', reserveHandler);
                
                // Re-enable button
                button.disabled = false;
            } else {
                showAlert('error', data.message);
                // Re-enable button
                button.disabled = false;
                button.textContent = 'Отменить резерв';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Произошла ошибка');
            // Re-enable button
            button.disabled = false;
            button.textContent = 'Отменить резерв';
        });
    }

    // Function to show alerts
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <div class="alert-message">
                <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.classList.add('closing');
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 300);
            }
        }, 5000);
    }

    // Wish data for modals
    const wishData = window.wishData || {};

    // Handle wish image clicks
    document.querySelectorAll('.wish-image-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const wishId = this.getAttribute('data-wish-id');
            const wish = wishData[wishId];
            
            if (wish) {
                // Update modal content
                document.getElementById('wishImageModalLabel').textContent = wish.title;
                document.getElementById('wishModalImage').src = wish.image;
                
                // Update price
                const priceElement = document.getElementById('wishModalPrice');
                if (wish.price) {
                    priceElement.textContent = wish.formattedPrice || (wish.price + ' BYN');
                    priceElement.style.display = 'block';
                } else {
                    priceElement.style.display = 'none';
                }
                
                // Update URL
                const urlElement = document.getElementById('wishModalUrl');
                if (wish.url) {
                    urlElement.href = wish.url;
                    urlElement.style.display = 'inline-block';
                } else {
                    urlElement.style.display = 'none';
                }
                
                // Update status
                const statusElement = document.getElementById('wishModalStatus');
                if (wish.isReserved) {
                    statusElement.textContent = 'Зарезервировано';
                    statusElement.className = 'badge bg-success ms-2';
                } else {
                    statusElement.textContent = 'Доступно';
                    statusElement.className = 'badge bg-secondary ms-2';
                }
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('wishImageModal'));
                modal.show();
            }
        });
    });
});

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }
} 