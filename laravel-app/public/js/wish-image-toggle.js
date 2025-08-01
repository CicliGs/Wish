// Wish Image Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const btnUploadFile = document.getElementById('btn-upload-file');
    const btnUploadUrl = document.getElementById('btn-upload-url');
    const imageFileInput = document.getElementById('image_file');
    const imageUrlInput = document.getElementById('image');
    
    if (btnUploadFile && btnUploadUrl && imageFileInput && imageUrlInput) {
        // Set default state
        btnUploadFile.classList.add('active');
        imageFileInput.style.display = 'block';
        imageUrlInput.style.display = 'none';
        
        // File upload button
        btnUploadFile.addEventListener('click', function() {
            btnUploadFile.classList.add('active');
            btnUploadUrl.classList.remove('active');
            imageFileInput.style.display = 'block';
            imageUrlInput.style.display = 'none';
            imageUrlInput.value = '';
        });
        
        // URL input button
        btnUploadUrl.addEventListener('click', function() {
            btnUploadUrl.classList.add('active');
            btnUploadFile.classList.remove('active');
            imageUrlInput.style.display = 'block';
            imageFileInput.style.display = 'none';
            imageFileInput.value = '';
        });
    }
}); 