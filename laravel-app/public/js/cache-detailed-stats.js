let statsData = {};

function loadDetailedStats() {
    fetch('/cache/status')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'connected') {
                statsData = data;
                updateUI(data);
            } else {
                showError('Redis connection failed: ' + data.message);
            }
        })
        .catch(error => {
            showError('Failed to load Redis statistics');
            console.error('Error:', error);
        });
}

function updateUI(data) {
    // Performance Metrics
    document.getElementById('hit-rate').textContent = data.hit_rate + '%';
    document.getElementById('hit-rate-bar').style.width = data.hit_rate + '%';
    
    document.getElementById('memory-usage').textContent = data.used_memory_human;
    document.getElementById('memory-bar').style.width = data.memory_usage_percent + '%';
    
    document.getElementById('connected-clients').textContent = data.connected_clients;
    document.getElementById('db-size').textContent = data.db_size;
    
    // System Information
    document.getElementById('redis-version').textContent = data.redis_version;
    document.getElementById('uptime').textContent = formatUptime(data.uptime_in_seconds);
    document.getElementById('commands-processed').textContent = formatNumber(data.total_commands_processed);
    document.getElementById('connections-received').textContent = formatNumber(data.total_connections_received);
    
    // Memory & Storage
    document.getElementById('peak-memory').textContent = data.used_memory_peak_human;
    document.getElementById('last-save').textContent = formatTimestamp(data.last_save_time);
    document.getElementById('keyspace-hits').textContent = formatNumber(data.keyspace_hits);
    document.getElementById('keyspace-misses').textContent = formatNumber(data.keyspace_misses);
    
    // Update progress bar colors based on values
    updateProgressBarColors(data);
}

function updateProgressBarColors(data) {
    const hitRateBar = document.getElementById('hit-rate-bar');
    const memoryBar = document.getElementById('memory-bar');
    
    // Hit rate colors
    if (data.hit_rate >= 80) {
        hitRateBar.className = 'progress-bar progress-success';
    } else if (data.hit_rate >= 60) {
        hitRateBar.className = 'progress-bar progress-warning';
    } else {
        hitRateBar.className = 'progress-bar progress-danger';
    }
    
    // Memory usage colors
    if (data.memory_usage_percent <= 60) {
        memoryBar.className = 'progress-bar progress-success';
    } else if (data.memory_usage_percent <= 80) {
        memoryBar.className = 'progress-bar progress-warning';
    } else {
        memoryBar.className = 'progress-bar progress-danger';
    }
}

function formatUptime(seconds) {
    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    
    if (days > 0) return `${days}d ${hours}h`;
    if (hours > 0) return `${hours}h ${minutes}m`;
    return `${minutes}m`;
}

function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

function formatTimestamp(timestamp) {
    if (!timestamp) return 'Never';
    
    const date = new Date(timestamp * 1000);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
    
    return date.toLocaleDateString();
}

function refreshStats() {
    const refreshBtn = document.getElementById('refresh-btn');
    refreshBtn.classList.add('loading');
    refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>';
    
    loadDetailedStats();
    
    setTimeout(() => {
        refreshBtn.classList.remove('loading');
        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
    }, 1000);
}

function showError(message) {
    if (window.systemNotifications) {
        window.systemNotifications.error(message);
    } else {
        // Fallback для обратной совместимости
        const notification = document.createElement('div');
        notification.className = 'alert alert-danger position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            ${message}
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
}

// Load stats when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadDetailedStats();
    
    // Auto-refresh every 30 seconds
    setInterval(loadDetailedStats, 30000);
});

// Add spinning animation for refresh button
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
}); 