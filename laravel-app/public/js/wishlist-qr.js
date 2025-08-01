// QR Code Generation for Wish Lists

// Function to generate QR code
function generateQRCode(containerId, url) {
    const qrContainer = document.getElementById(containerId);
    if (!qrContainer) {
        return;
    }
    
    // Clear container first
    qrContainer.innerHTML = '';
    
    // Check if QRious is available
    if (typeof QRious === 'undefined') {
        qrContainer.innerHTML = '<div class="text-danger">QRious library not loaded</div>';
        return;
    }
    
    try {
        // Create canvas element
        const canvas = document.createElement('canvas');
        qrContainer.appendChild(canvas);
        
        // Create QR code
        const qr = new QRious({
            element: canvas,
            value: url,
            size: 200,
            level: 'H',
            background: '#ffffff',
            foreground: '#000000'
        });
        
        // Add some styling to canvas
        canvas.style.borderRadius = '10px';
        canvas.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.1)';
        canvas.style.background = 'white';
        
    } catch (error) {
        qrContainer.innerHTML = '<div class="text-danger">Error generating QR code</div>';
    }
}

// Function to regenerate all QR codes
function regenerateAllQRCodes() {
    if (window.wishListQrData && window.wishListQrData.length > 0) {
        window.wishListQrData.forEach(function(data) {
            const containerId = 'qrcode-' + data.id;
            generateQRCode(containerId, data.url);
        });
    }
}

// Initialize QR codes when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for everything to load
    setTimeout(function() {
        if (window.wishListQrData && window.wishListQrData.length > 0) {
            regenerateAllQRCodes();
        }
    }, 500);
});

// Listen for modal show events
document.addEventListener('DOMContentLoaded', function() {
    // Find all QR modals and add event listeners
    const qrModals = document.querySelectorAll('[id^="qrModal-"]');
    
    qrModals.forEach(function(modal) {
        modal.addEventListener('shown.bs.modal', function() {
            setTimeout(regenerateAllQRCodes, 300);
        });
    });
    
    // Global modal event listener
    document.addEventListener('shown.bs.modal', function(event) {
        if (event.target.id && event.target.id.startsWith('qrModal-')) {
            setTimeout(regenerateAllQRCodes, 300);
        }
    });
});

// Global function to manually trigger QR generation
window.generateQRCodes = function() {
    regenerateAllQRCodes();
};

// Function to generate QR for specific modal
window.generateQRForModal = function(modalId) {
    const modalNumber = modalId.replace('qrModal-', '');
    const qrData = window.wishListQrData.find(data => data.id == modalNumber);
    
    if (qrData) {
        generateQRCode('qrcode-' + qrData.id, qrData.url);
    }
};

// Auto-regenerate QR codes every 2 seconds for the first 10 seconds
let qrRetryCount = 0;
const qrRetryInterval = setInterval(function() {
    if (qrRetryCount < 5) {
        regenerateAllQRCodes();
        qrRetryCount++;
    } else {
        clearInterval(qrRetryInterval);
    }
}, 2000);
