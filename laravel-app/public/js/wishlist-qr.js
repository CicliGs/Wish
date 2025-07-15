(function() {
    function generateQrCode(container, url) {
        if (!container.hasChildNodes()) {
            new QRious({
                element: container,
                value: url,
                size: 200
            });
        }
    }

    function clearQrCode(container) {
        container.innerHTML = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (!Array.isArray(window.wishListQrData)) return;
        window.wishListQrData.forEach(function(item) {
            var modal = document.getElementById('qrModal-' + item.id);
            var qrContainer = document.getElementById('qrcode-' + item.id);
            if (!modal || !qrContainer) return;
            modal.addEventListener('shown.bs.modal', function () {
                generateQrCode(qrContainer, item.url);
            });
            modal.addEventListener('hidden.bs.modal', function () {
                clearQrCode(qrContainer);
            });
        });
    });
})(); 