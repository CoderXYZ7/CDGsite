document.addEventListener('DOMContentLoaded', () => {
    // Event filtering
    const filterButtons = document.querySelectorAll('.filter-btn');
    const eventCards = document.querySelectorAll('.event-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            button.classList.add('active');

            const filter = button.dataset.filter;
            
            eventCards.forEach(card => {
                if (filter === 'all' || card.dataset.category === filter) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Event registration handling
    const registerButtons = document.querySelectorAll('.event-card .button');
    
    registerButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const eventCard = button.closest('.event-card');
            const eventName = eventCard.querySelector('h4').textContent;
            const eventDate = eventCard.querySelector('.event-date').textContent.trim();
            
            alert(`Grazie per il tuo interesse per l'evento "${eventName}" del ${eventDate}. \nTi contatteremo presto per confermare la tua iscrizione.`);
        });
    });
});
