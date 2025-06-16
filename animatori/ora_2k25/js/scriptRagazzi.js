document.addEventListener('DOMContentLoaded', function() {
    const timelineContainer = document.getElementById('timeline-container');
    
    // Carica gli orari dal file JSON
    fetch('orariRagazzi.json')
        .then(response => {
            if (!response.ok) {
                throw new Error('Errore nel caricamento degli orari');
            }
            return response.json();
        })
        .then(data => {
            displayTimeline(data.orari);
        })
        .catch(error => {
            console.error('Si è verificato un errore:', error);
            timelineContainer.innerHTML = `<div class="error">Impossibile caricare il programma. Si prega di riprovare più tardi.</div>`;
        });

    function displayTimeline(events) {
        timelineContainer.innerHTML = '';
        
        events.forEach((event, index) => {
            const eventElement = document.createElement('div');
            eventElement.className = `evento ${index % 2 === 0 ? 'right' : 'right'}`;
            
            eventElement.innerHTML = `
                <div class="contenuto">
                    <div class="orario">${event.orario}</div>
                    <h3>${event.attivita}</h3>
                    ${event.descrizione ? `<p>${event.descrizione}</p>` : ''}
                </div>
            `;
            
            timelineContainer.appendChild(eventElement);
        });
    }
});