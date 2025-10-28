// Автоматическое обновление CSRF токена при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    updateCsrfToken();
    
    // Обновляем токен каждые 5 минут на странице регистрации
    setInterval(updateCsrfToken, 5 * 60 * 1000);
    
    // Обновляем токен при фокусе на странице
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            updateCsrfToken();
        }
    });
    
    // Обработка ошибки 419 для формы регистрации
    const registerForm = document.querySelector('form[action*="register"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Проверяем, не отправляется ли форма уже
            if (submitBtn.disabled) {
                return;
            }
            
            e.preventDefault(); // Предотвращаем стандартную отправку
            
            submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Регистрация...';
            submitBtn.disabled = true;
            
            // Обновляем CSRF токен перед отправкой формы
            updateCsrfToken().then(() => {
                // Отправляем форму после обновления токена
                registerForm.submit();
            }).catch(error => {
                console.error('Failed to update CSRF token:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

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

// Добавляем CSS для спиннера
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
