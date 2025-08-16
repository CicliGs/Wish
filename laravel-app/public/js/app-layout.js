// Автоматическое обновление CSRF токена
function updateCsrfToken() {
    fetch('/csrf-token')
        .then(response => response.json())
        .then(data => {
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
            if (window.Laravel) {
                window.Laravel.csrfToken = data.token;
            }
        })
        .catch(error => {
            console.error('Failed to update CSRF token:', error);
        });
}

// Обновляем токен каждые 30 минут
setInterval(updateCsrfToken, 30 * 60 * 1000);

// Обновляем токен при фокусе на странице
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        updateCsrfToken();
    }
}); 