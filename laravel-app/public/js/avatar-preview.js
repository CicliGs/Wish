// Avatar Preview Functionality
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function(e) {
            const [file] = e.target.files;
            if (file) {
                avatarPreview.src = URL.createObjectURL(file);
            }
        });
    }
}); 