document.addEventListener('DOMContentLoaded', function() {
    // Handle currency selection
    const currencyDropdown = document.getElementById('currencyDropdown');
    if (currencyDropdown) {
        const currencyButtons = currencyDropdown.querySelectorAll('.dropdown-item');
        
        currencyButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const form = this.closest('form');
                const formData = new FormData(form);
                
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Loading...';
                this.disabled = true;
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the dropdown button text with proper icon
                        const dropdownButton = currencyDropdown.querySelector('.dropdown-toggle');
                        const currencyIcon = getCurrencyIcon(data.currency);
                        dropdownButton.innerHTML = `<i class="bi ${currencyIcon} me-1"></i>${data.currency}`;
                        
                        // Update active state
                        currencyButtons.forEach(btn => btn.classList.remove('active'));
                        this.classList.add('active');
                        
                        // Show success message
                        showAlert('success', data.message);
                        
                        // Reload page to update all prices
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showAlert('error', 'Failed to update currency');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'An error occurred while updating currency');
                })
                .finally(() => {
                    // Restore button state
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            });
        });
    }
});

// Function to show alerts (reuse existing alert system)
function showAlert(type, message) {
    const alertContainer = document.querySelector('.container');
    if (!alertContainer) return;
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.style.zIndex = '10002'; // Ensure alerts appear above everything
    alertDiv.innerHTML = `
        <div class="alert-message">
            <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>
            ${message}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    alertContainer.insertBefore(alertDiv, alertContainer.firstChild);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}

// Function to get currency icon
function getCurrencyIcon(currency) {
    switch(currency) {
        case 'BYN':
        case 'USD':
            return 'bi-currency-dollar';
        case 'EUR':
            return 'bi-currency-euro';
        case 'RUB':
            return 'bi-currency-ruble';
        default:
            return 'bi-currency-exchange';
    }
} 