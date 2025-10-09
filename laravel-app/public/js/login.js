// Автоматическое обновление CSRF токена при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    updateCsrfToken();
    
    // Обновляем токен каждые 5 минут на странице входа
    setInterval(updateCsrfToken, 5 * 60 * 1000);
    
    // Обновляем токен при фокусе на странице
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            updateCsrfToken();
        }
    });
    
    // Обработка ошибки 419
    const loginForm = document.getElementById('loginForm');
    loginForm.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('loginBtn');
        const originalText = submitBtn.innerHTML;
        
        // Проверяем, не отправляется ли форма уже
        if (submitBtn.disabled) {
            return;
        }
        
        e.preventDefault(); // Предотвращаем стандартную отправку
        
        submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Вход...';
        submitBtn.disabled = true;
        
        // Обновляем CSRF токен перед отправкой формы
        updateCsrfToken().then(() => {
            // Отправляем форму после обновления токена
            loginForm.submit();
        }).catch(error => {
            console.error('Failed to update CSRF token:', error);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});

function updateCsrfToken() {
    return fetch('/csrf-token')
        .then(response => response.json())
        .then(data => {
            document.querySelector('input[name="_token"]').value = data.token;
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
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