/**
 * Profile Page JavaScript
 * Обработка достижений и модального окна
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeProfile();
});

function initializeProfile() {
    // Инициализация модального окна достижений
    initializeAchievementsModal();
    
    // Инициализация анимаций
    initializeAnimations();
    
    // Инициализация интерактивных элементов
    initializeInteractiveElements();
}

function initializeAchievementsModal() {
    const modal = document.getElementById('allAchievementsModal');
    if (!modal) return;

    // Обработчик открытия модального окна
    modal.addEventListener('show.bs.modal', function() {
        console.log('Модальное окно достижений открыто');
        // Можно добавить дополнительную логику при открытии
    });

    // Обработчик закрытия модального окна
    modal.addEventListener('hidden.bs.modal', function() {
        console.log('Модальное окно достижений закрыто');
        // Можно добавить дополнительную логику при закрытии
    });

    // Обработчик клика по достижению
    const achievementItems = modal.querySelectorAll('.achievement-item');
    achievementItems.forEach(item => {
        item.addEventListener('click', function() {
            const title = this.getAttribute('title');
            const isReceived = this.classList.contains('opacity-50') === false;
            
            console.log('Клик по достижению:', title, 'Получено:', isReceived);
            
            // Можно добавить дополнительную логику при клике
            if (isReceived) {
                this.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 200);
            }
        });
    });
}

function initializeAnimations() {
    // Анимация появления элементов при загрузке страницы
    const animatedElements = document.querySelectorAll('.profile-widget, .achievement-icon-wrapper');
    
    animatedElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.6s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

function initializeInteractiveElements() {
    // Обработчик наведения на виджеты статистики
    const profileWidgets = document.querySelectorAll('.profile-widget');
    
    profileWidgets.forEach(widget => {
        widget.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        widget.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Обработчик наведения на достижения
    const achievementWrappers = document.querySelectorAll('.achievement-icon-wrapper');
    
    achievementWrappers.forEach(wrapper => {
        wrapper.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.05)';
        });
        
        wrapper.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

// Функция для обновления статистики (если понадобится в будущем)
function updateProfileStats() {
    // Здесь можно добавить AJAX запрос для обновления статистики
    console.log('Обновление статистики профиля...');
}

// Функция для обновления достижений (если понадобится в будущем)
function updateAchievements() {
    // Здесь можно добавить AJAX запрос для обновления достижений
    console.log('Обновление достижений...');
}

// Экспорт функций для использования в других модулях (если понадобится)
window.ProfileModule = {
    updateProfileStats,
    updateAchievements,
    initializeProfile
}; 