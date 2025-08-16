function loadRedisStatus() {
    fetch('/cache/status')
        .then(response => response.json())
        .then(data => {
            const statusDiv = document.getElementById('redis-status');
            if (data.status === 'connected') {
                statusDiv.className = 'redis-status redis-connected fade-in';
                statusDiv.innerHTML = `
                    <div class="text-center w-100">
                        <div class="status-indicator status-connected mb-3">
                            <i class="bi bi-check-circle-fill"></i>
                            Подключено
                        </div>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-label">Версия</div>
                                <div class="stat-value">
                                    <span class="stat-badge badge-info">${data.redis_version}</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Клиенты</div>
                                <div class="stat-value">
                                    <span class="stat-badge badge-primary">${data.connected_clients}</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Память</div>
                                <div class="stat-value">
                                    <span class="stat-badge badge-warning">${data.used_memory_human}</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">Время работы</div>
                                <div class="stat-value">
                                    <span class="stat-badge badge-secondary">${formatUptime(data.uptime_in_seconds)}</span>
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
                            Ошибка подключения
                        </div>
                        <p class="text-danger">${data.message}</p>
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
                        Не удалось подключиться
                    </div>
                    <p class="text-danger">Не удалось подключиться к Redis</p>
                </div>
            `;
        });
}

function formatUptime(seconds) {
    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    
    if (days > 0) return `${days}d ${hours}h`;
    if (hours > 0) return `${hours}h ${minutes}m`;
    return `${minutes}m`;
}

function clearPageCache() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Очистка...';
    button.disabled = true;
    
    fetch('/cache/clear-pages', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ pattern: 'page_cache:*' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Кэш страниц очищен', 'success');
        } else {
            showNotification('Не удалось очистить кэш страниц', 'error');
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

function clearAllCache() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Очистка...';
    button.disabled = true;
    
    fetch('/cache/clear-pages', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ pattern: '*' })
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
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill me-2"></i>
        ${message}
    `;
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Load Redis status when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadRedisStatus();
    
    // Refresh status every 30 seconds
    setInterval(loadRedisStatus, 30000);
}); 