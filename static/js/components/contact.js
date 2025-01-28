document.addEventListener('DOMContentLoaded', () => {
    const contactForm = document.getElementById('contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(contactForm);
            const data = Object.fromEntries(formData.entries());
            
            // In a real application, you would send this data to a server
            console.log('Form submitted with data:', data);
            
            // Show success message
            alert('Grazie per averci contattato! Ti risponderemo il prima possibile.');
            
            // Reset form
            contactForm.reset();
        });
    }

    // Add smooth scrolling to FAQ items
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        item.addEventListener('click', () => {
            item.classList.toggle('active');
        });
    });
});
