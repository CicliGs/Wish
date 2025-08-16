// Wait for QRious to load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof QRious === 'undefined') {
        // Try alternative CDN
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js';
        script.onload = function() {
            initializeQRData();
        };
        document.head.appendChild(script);
    } else {
        initializeQRData();
    }
});

function initializeQRData() {
    // This will be populated by the server-side rendering
    if (window.wishListQrData) {
        // Load our QR script
        const qrScript = document.createElement('script');
        qrScript.src = "/js/wishlist-qr.js";
        qrScript.onload = function() {
            // Trigger initial generation after script loads
            setTimeout(function() {
                if (window.generateQRCodes) {
                    window.generateQRCodes();
                }
            }, 1000);
        };
        document.head.appendChild(qrScript);
    }
}

// Additional modal event handlers
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for QR buttons
    document.querySelectorAll('[data-bs-target^="#qrModal-"]').forEach(function(button) {
        button.addEventListener('click', function() {
            const modalId = this.getAttribute('data-bs-target');
            const wishListId = modalId.replace('#qrModal-', '');
            
            // Generate QR code when button is clicked
            setTimeout(function() {
                if (window.generateQRForModal) {
                    window.generateQRForModal(modalId.replace('#', ''));
                }
            }, 500);
        });
    });
}); 