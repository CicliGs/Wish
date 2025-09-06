function loadCacheStatus() {
    fetch('/cache/status')
        .then(response => response.json())
        .then(data => {
            const statusDiv = document.getElementById('redis-status');
            if (data.status === 'success') {
                statusDiv.className = 'redis-status redis-connected fade-in';
                statusDiv.innerHTML = `
                    <div class="text-center w-100">
                        <div class="status-indicator status-connected mb-3">
                            <i class="bi bi-check-circle-fill"></i>
                            ${window.cacheTranslations?.cache_working || 'Кеш работает'}
                        </div>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-label">${window.cacheTranslations?.driver_label || 'Драйвер'}</div>
                                <div class="stat-value">
                                    <span class="stat-badge badge-info">${data.data.driver}</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">${window.cacheTranslations?.store_label || 'Хранилище'}</div>
                                <div class="stat-value">
                                    <span class="stat-badge badge-primary">${data.data.store}</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">${window.cacheTranslations?.prefix_label || 'Префикс'}</div>
                                <div class="stat-value">
                                    <span class="stat-badge badge-warning">${data.data.prefix}</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">${window.cacheTranslations?.description_label || 'Описание'}</div>
                                <div class="stat-value">
                                    <span class="stat-badge badge-secondary">${data.data.description}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                statusDiv.className = 'redis-status redis-error fade-in';
                statusDiv.innerHTML = `
                    <div class="text-center">
                        <div class="status-indicator status-error mb-3">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            ${window.cacheTranslations?.status_error || 'Ошибка получения статуса'}
                        </div>
                        <p class="text-danger">${data.message || (window.cacheTranslations?.failed_to_get_status || 'Не удалось получить статус кеша')}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            const statusDiv = document.getElementById('redis-status');
            statusDiv.className = 'redis-status redis-error fade-in';
            statusDiv.innerHTML = `
                <div class="text-center">
                    <div class="status-indicator status-error mb-3">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        ${window.cacheTranslations?.connection_failed || 'Не удалось подключиться'}
                    </div>
                    <p class="text-danger">${window.cacheTranslations?.failed_to_get_status || 'Не удалось получить статус кеша'}</p>
                </div>
            `;
        });
}

function clearStaticCache() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Очистка...';
    button.disabled = true;
    
    fetch('/cache/clear-static', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Кеш статического контента очищен', 'success');
        } else {
            showNotification('Не удалось очистить кеш статического контента', 'error');
        }
    })
    .catch(error => {
        showNotification('Ошибка при очистке кеша статического контента', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function clearImageCache() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Очистка...';
    button.disabled = true;
    
    fetch('/cache/clear-images', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Кеш изображений очищен', 'success');
        } else {
            showNotification('Не удалось очистить кеш изображений', 'error');
        }
    })
    .catch(error => {
        showNotification('Ошибка при очистке кеша изображений', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function clearAssetCache() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Очистка...';
    button.disabled = true;
    
    fetch('/cache/clear-assets', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Кеш ассетов (CSS/JS) очищен', 'success');
        } else {
            showNotification('Не удалось очистить кеш ассетов', 'error');
        }
    })
    .catch(error => {
        showNotification('Ошибка при очистке кеша ассетов', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function clearAvatarCache() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Очистка...';
    button.disabled = true;
    
    fetch('/cache/clear-avatars', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Кеш аватаров очищен', 'success');
        } else {
            showNotification('Не удалось очистить кеш аватаров', 'error');
        }
    })
    .catch(error => {
        showNotification('Ошибка при очистке кеша аватаров', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function clearAllCache() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Очистка...';
    button.disabled = true;
    
    fetch('/cache/clear-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Весь кэш очищен', 'success');
        } else {
            showNotification('Не удалось очистить весь кэш', 'error');
        }
    })
    .catch(error => {
        showNotification('Ошибка при очистке кэша', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function showNotification(message, type) {
    if (window.systemNotifications) {
        window.systemNotifications.show(message, type);
    } else {
        // Fallback для обратной совместимости
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill me-2"></i>
            ${message}
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Load cache status when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadCacheStatus();
    
    // Refresh status every 30 seconds
    setInterval(loadCacheStatus, 30000);
}); 