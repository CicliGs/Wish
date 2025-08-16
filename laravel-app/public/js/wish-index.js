document.addEventListener('DOMContentLoaded', function() {
    // Wish data for modals - this will be populated by server-side rendering
    const wishData = window.wishData || {};

    // Handle wish image clicks
    document.querySelectorAll('.wish-image-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const wishId = this.getAttribute('data-wish-id');
            const wish = wishData[wishId];
            
            if (wish) {
                // Update modal content
                document.getElementById('wishImageModalLabel').textContent = wish.title;
                document.getElementById('wishModalImage').src = wish.image;
                
                // Update price
                const priceElement = document.getElementById('wishModalPrice');
                if (wish.price) {
                    priceElement.textContent = wish.formattedPrice || (wish.price + ' BYN');
                    priceElement.style.display = 'block';
                } else {
                    priceElement.style.display = 'none';
                }
                
                // Update URL
                const urlElement = document.getElementById('wishModalUrl');
                if (wish.url) {
                    urlElement.href = wish.url;
                    urlElement.style.display = 'inline-block';
                } else {
                    urlElement.style.display = 'none';
                }
                
                // Update status
                const statusElement = document.getElementById('wishModalStatus');
                if (wish.isReserved) {
                    statusElement.textContent = 'Зарезервировано';
                    statusElement.className = 'badge bg-success ms-2';
                } else {
                    statusElement.textContent = 'Доступно';
                    statusElement.className = 'badge bg-secondary ms-2';
                }
                
                // Update action buttons
                document.getElementById('wishModalEdit').href = wish.editUrl;
                document.getElementById('wishModalDelete').action = wish.deleteUrl;
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('wishImageModal'));
                modal.show();
            }
        });
    });
}); 