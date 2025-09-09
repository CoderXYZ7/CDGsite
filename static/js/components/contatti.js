document.addEventListener('DOMContentLoaded', () => {
    const contactForm = document.getElementById('contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const form = e.target;
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;

            // Disable button and show sending message
            submitButton.disabled = true;
            submitButton.innerHTML = 'Invio in corso...';

            const formData = {
                name: form.name.value,
                email: form.email.value,
                subject: form.subject.value,
                message: form.message.value
            };

            fetch('../api/contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                // Create a message element
                let feedbackMessage = form.querySelector('.feedback-message');
                if (!feedbackMessage) {
                    feedbackMessage = document.createElement('p');
                    feedbackMessage.className = 'feedback-message';
                    form.appendChild(feedbackMessage);
                }

                if (data.status === 'success') {
                    feedbackMessage.textContent = data.message;
                    feedbackMessage.style.color = 'green';
                    form.reset();
                } else {
                    feedbackMessage.textContent = data.message || 'Si è verificato un errore.';
                    feedbackMessage.style.color = 'red';
                }
            })
            .catch(error => {
                let feedbackMessage = form.querySelector('.feedback-message');
                if (!feedbackMessage) {
                    feedbackMessage = document.createElement('p');
                    feedbackMessage.className = 'feedback-message';
                    form.appendChild(feedbackMessage);
                }
                feedbackMessage.textContent = 'Errore di rete. Riprova più tardi.';
                feedbackMessage.style.color = 'red';
                console.error('Error:', error);
            })
            .finally(() => {
                // Re-enable button and restore text
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
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
