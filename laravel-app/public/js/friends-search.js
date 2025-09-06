// Обработка отправки приглашений в друзья
document.addEventListener('DOMContentLoaded', function() {
    // Обработка всех форм отправки приглашений
    const friendRequestForms = document.querySelectorAll('form[action*="friends/request"]');
    
    friendRequestForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            const userId = this.action.split('/').pop();
            
            // Обновляем CSRF токен перед отправкой
            updateCsrfToken().then(() => {
                // Отправляем запрос
                const formData = new URLSearchParams(new FormData(this));
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            throw new Error('Server returned non-JSON response');
                        });
                    }
                })
                .then(data => {
                    if (data.success) {
                        // Обновляем статус на "Приглашение отправлено"
                        updateFriendStatus(userId, 'request_sent');
                        showNotification('Приглашение отправлено!', 'success');
                    } else {
                        showNotification(data.message || 'Ошибка при отправке приглашения', 'error');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Ошибка при отправке приглашения', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });
        });
    });
});

// Обновление статуса дружбы
function updateFriendStatus(userId, status) {
    const form = document.querySelector(`form[action*="friends/request/${userId}"]`);
    
    if (!form) {
        return;
    }
    
    const actionContainer = form.closest('div');
    
    if (status === 'request_sent') {
        actionContainer.innerHTML = `
            <div class="status-badge status-pending">
                <i class="bi bi-clock-history me-1"></i>
                Приглашение отправлено
            </div>
        `;
    }
}

// Обновление CSRF токена
function updateCsrfToken() {
    return fetch('/csrf-token')
        .then(response => response.json())
        .then(data => {
            const tokenInput = document.querySelector('input[name="_token"]');
            if (tokenInput) {
                tokenInput.value = data.token;
            }
            
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) {
                metaToken.setAttribute('content', data.token);
            }
            
            if (window.Laravel) {
                window.Laravel.csrfToken = data.token;
            }
        })
        .catch(error => {
            console.error('Failed to update CSRF token:', error);
        });
}

// Показ уведомлений
function showNotification(message, type) {
    if (window.systemNotifications) {
        window.systemNotifications.show(message, type);
    } else {
        // Fallback для обратной совместимости
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <div class="alert-message">
                <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
}
