/**
 * Система уведомлений для отображения ошибок и оповещений
 * НЕ для уведомлений от других пользователей
 */

class SystemNotifications {
    constructor() {
        this.notifications = [];
        this.maxNotifications = 3; // Максимальное количество уведомлений одновременно
    }

    /**
     * Показать системное уведомление
     * @param {string} message - Сообщение
     * @param {string} type - Тип уведомления (success, error, info, warning)
     * @param {number} duration - Длительность отображения в миллисекундах (по умолчанию 5000)
     */
    show(message, type = 'info', duration = 5000) {
        // Ограничиваем количество уведомлений
        if (this.notifications.length >= this.maxNotifications) {
            this.removeOldest();
        }

        const notification = this.createNotification(message, type);
        this.notifications.push(notification);
        
        document.body.appendChild(notification);

        // Автоматическое удаление
        if (duration > 0) {
            setTimeout(() => {
                this.remove(notification);
            }, duration);
        }

        return notification;
    }

    /**
     * Создать элемент уведомления
     */
    createNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `system-notification ${type}`;
        
        const icon = this.getIcon(type);
        
        notification.innerHTML = `
            <i class="bi ${icon}"></i>
            ${message}
        `;

        return notification;
    }

    /**
     * Получить иконку для типа уведомления
     */
    getIcon(type) {
        const icons = {
            success: 'bi-check-circle-fill',
            error: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill',
            warning: 'bi-exclamation-triangle-fill'
        };
        
        return icons[type] || icons.info;
    }

    /**
     * Удалить уведомление
     */
    remove(notification) {
        if (notification && notification.parentNode) {
            notification.classList.add('fade-out');
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
                
                // Удаляем из массива
                const index = this.notifications.indexOf(notification);
                if (index > -1) {
                    this.notifications.splice(index, 1);
                }
            }, 300);
        }
    }

    /**
     * Удалить самое старое уведомление
     */
    removeOldest() {
        if (this.notifications.length > 0) {
            this.remove(this.notifications[0]);
        }
    }

    /**
     * Очистить все уведомления
     */
    clear() {
        this.notifications.forEach(notification => {
            this.remove(notification);
        });
    }

    /**
     * Показать успешное уведомление
     */
    success(message, duration = 5000) {
        return this.show(message, 'success', duration);
    }

    /**
     * Показать ошибку
     */
    error(message, duration = 5000) {
        return this.show(message, 'error', duration);
    }

    /**
     * Показать информационное уведомление
     */
    info(message, duration = 5000) {
        return this.show(message, 'info', duration);
    }

    /**
     * Показать предупреждение
     */
    warning(message, duration = 5000) {
        return this.show(message, 'warning', duration);
    }
}

// Создаем глобальный экземпляр
window.systemNotifications = new SystemNotifications();

// Глобальные функции для обратной совместимости
window.showSystemNotification = (message, type, duration) => {
    return window.systemNotifications.show(message, type, duration);
};

window.showSystemSuccess = (message, duration) => {
    return window.systemNotifications.success(message, duration);
};

window.showSystemError = (message, duration) => {
    return window.systemNotifications.error(message, duration);
};

window.showSystemInfo = (message, duration) => {
    return window.systemNotifications.info(message, duration);
};

window.showSystemWarning = (message, duration) => {
    return window.systemNotifications.warning(message, duration);
};
