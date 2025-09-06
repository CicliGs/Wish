/**
 * Управление уведомлениями в выпадающем списке
 */
class NotificationsManager {
    constructor() {
        this.notifications = [];
        this.isLoading = false;
        this.pollingInterval = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadNotifications();
        this.startPolling();
    }

    setupEventListeners() {
        // Кнопка "Отметить все как прочитанные"
        document.getElementById('markAllReadBtn')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.markAllAsRead();
        });

        // Предотвращение закрытия при клике внутри выпадающего списка
        document.querySelector('.notifications-dropdown')?.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Обработка клика по уведомлению
        document.getElementById('notificationsList')?.addEventListener('click', (e) => {
            const notificationItem = e.target.closest('.notification-item');
            if (notificationItem) {
                this.handleNotificationClick(notificationItem);
            }
        });
    }

    async loadNotifications() {
        if (this.isLoading) return;

        this.isLoading = true;
        this.showLoading();

        try {
            const response = await fetch('/notifications/unread', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.notifications = data.notifications || [];
                this.updateNotificationsDisplay();
                this.updateNotificationCount();
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            this.showError();
        } finally {
            this.isLoading = false;
        }
    }

    updateNotificationsDisplay() {
        const notificationsList = document.getElementById('notificationsList');
        const notificationsEmpty = document.getElementById('notificationsEmpty');
        const markAllReadBtn = document.getElementById('markAllReadBtn');

        if (!notificationsList || !notificationsEmpty) return;

        if (this.notifications.length === 0) {
            notificationsList.innerHTML = '';
            notificationsEmpty.style.display = 'block';
            markAllReadBtn.style.display = 'none';
        } else {
            notificationsEmpty.style.display = 'none';
            notificationsList.innerHTML = this.notifications.map(notification => 
                this.createNotificationHTML(notification)
            ).join('');
            
            // Показываем кнопку "Отметить все как прочитанные" только если есть непрочитанные
            const unreadCount = this.notifications.filter(n => !n.read_at).length;
            markAllReadBtn.style.display = unreadCount > 0 ? 'block' : 'none';
        }
    }

    createNotificationHTML(notification) {
        const isUnread = !notification.read_at;
        const timeAgo = this.getTimeAgo(notification.created_at);
        
        return `
            <div class="notification-item ${isUnread ? 'unread' : ''}" data-notification-id="${notification.id}">
                <div class="notification-header">
                    <div class="notification-message">
                        <strong>${notification.sender_name || 'Неизвестный пользователь'}</strong> ${this.translate('friend_added_gift', { name: notification.sender_name || 'Неизвестный пользователь' })}
                    </div>
                    <div class="notification-time">${timeAgo}</div>
                </div>
                <div class="notification-content">
                    <div class="notification-details">
                        <div class="notification-wish-title">"${notification.wish_title || 'Без названия'}"</div>
                        <div class="notification-wish-list">
                            ${this.translate('in_wishlist')} <a href="/user/${notification.sender_id}/wish-list/${notification.wish_list_id || ''}" class="wish-list-link">${notification.wish_list_title || 'Неизвестный список'}</a>
                        </div>
                    </div>
                </div>
                ${isUnread ? `
                    <div class="notification-actions">
                        <button class="btn mark-read-btn" onclick="notificationsManager.markAsRead(${notification.id})">
                            <i class="bi bi-check me-1"></i>${this.translate('mark_as_read')}
                        </button>
                    </div>
                ` : ''}
            </div>
        `;
    }

    updateNotificationCount() {
        const notificationCount = document.getElementById('notificationCount');
        const notificationCountText = document.getElementById('notificationCountText');
        
        if (!notificationCount || !notificationCountText) return;

        const unreadCount = this.notifications.filter(n => !n.read_at).length;
        
        if (unreadCount > 0) {
            notificationCount.style.display = 'flex';
            notificationCountText.textContent = unreadCount > 9 ? '9+' : unreadCount;
        } else {
            notificationCount.style.display = 'none';
        }
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch('/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ notification_id: notificationId })
            });

            if (response.ok) {
                // Обновляем локальное состояние
                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification) {
                    notification.read_at = new Date().toISOString();
                }
                
                this.updateNotificationsDisplay();
                this.updateNotificationCount();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                // Обновляем локальное состояние
                this.notifications.forEach(notification => {
                    notification.read_at = new Date().toISOString();
                });
                
                this.updateNotificationsDisplay();
                this.updateNotificationCount();
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    handleNotificationClick(notificationItem) {
        const notificationId = notificationItem.dataset.notificationId;
        const notification = this.notifications.find(n => n.id == notificationId);
        
        if (notification && !notification.read_at) {
            this.markAsRead(notification.id);
        }
    }

    showLoading() {
        const notificationsList = document.getElementById('notificationsList');
        const notificationsEmpty = document.getElementById('notificationsEmpty');
        
        if (notificationsList && notificationsEmpty) {
            notificationsList.innerHTML = `
                <div class="notifications-loading">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">${this.translate('loading_notifications')}</span>
                    </div>
                    <div class="mt-2">${this.translate('loading_notifications')}</div>
                </div>
            `;
            notificationsEmpty.style.display = 'none';
        }
    }

    showError() {
        const notificationsList = document.getElementById('notificationsList');
        const notificationsEmpty = document.getElementById('notificationsEmpty');
        
        if (notificationsList && notificationsEmpty) {
            notificationsList.innerHTML = `
                <div class="notifications-loading">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 1.5rem;"></i>
                    <div class="mt-2 text-muted">${this.translate('error_loading_notifications')}</div>
                </div>
            `;
            notificationsEmpty.style.display = 'none';
        }
    }

    getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) {
            return this.translate('just_now');
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return this.translate('minutes_ago', { count: minutes });
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return this.translate('hours_ago', { count: hours });
        } else {
            const days = Math.floor(diffInSeconds / 86400);
            return this.translate('days_ago', { count: days });
        }
    }

    translate(key, parameters = {}) {
        // Простая система переводов для JavaScript
        const translations = {
            'friend_added_gift': 'добавил новый подарок',
            'in_wishlist': 'в список',
            'mark_as_read': 'Отметить как прочитанное',
            'loading_notifications': 'Загрузка уведомлений...',
            'error_loading_notifications': 'Ошибка загрузки уведомлений',
            'just_now': 'только что',
            'minutes_ago': ':count мин. назад',
            'hours_ago': ':count ч. назад',
            'days_ago': ':count дн. назад'
        };

        let translation = translations[key] || key;
        
        // Заменяем параметры
        Object.keys(parameters).forEach(param => {
            translation = translation.replace(`:${param}`, parameters[param]);
        });

        return translation;
    }

    startPolling() {
        // Останавливаем предыдущий интервал, если он есть
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }

        // Запускаем новый интервал
        this.pollingInterval = setInterval(() => {
            this.loadNotifications();
        }, 30000); // Обновляем каждые 30 секунд
    }

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }

    // Метод для добавления нового уведомления (вызывается извне)
    addNotification(notification) {
        this.notifications.unshift(notification);
        this.updateNotificationsDisplay();
        this.updateNotificationCount();
        
        // Добавляем анимацию для нового уведомления
        const firstItem = document.querySelector('.notification-item');
        if (firstItem) {
            firstItem.classList.add('new');
            setTimeout(() => {
                firstItem.classList.remove('new');
            }, 600);
        }
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    window.notificationsManager = new NotificationsManager();
});

// Остановка polling при уходе со страницы
window.addEventListener('beforeunload', function() {
    if (window.notificationsManager) {
        window.notificationsManager.stopPolling();
    }
});

