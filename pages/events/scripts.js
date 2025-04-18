// File validation for PDF upload
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('pdf_file');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const fileName = this.value.split('\\').pop();
            
            // Check if file has the correct format (YYYY-MM-DD.pdf)
            const filePattern = /^\d{4}-\d{2}-\d{2}\.pdf$/;
            
            if (!filePattern.test(fileName)) {
                alert('Please use the required format: YYYY-MM-DD.pdf');
                this.value = ''; // Clear the file input
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 1s';
            
            setTimeout(function() {
                alert.style.display = 'none';
            }, 1000);
        }, 5000);
    });
});