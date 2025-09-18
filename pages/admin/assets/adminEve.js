            // Store the week dates and events
            let weekDates = [];
            let events = [];
            let selectedDayIndex = -1;
            let customDate = null;

            // API base URL
            const API_URL = '../../api/events.php';

            // Load events from API
            async function loadEvents() {
                try {
                    const response = await fetch(API_URL);
                    const data = await response.json();
                    events = data.map(event => ({
                        ...event,
                        date: new Date(event.date)
                    }));
                    displayEvents();
                } catch (error) {
                    console.error('Error loading events:', error);
                }
            }

            // Find the nearest Sunday
            function getNearestSunday() {
                const today = new Date();
                const day = today.getDay(); // 0 is Sunday
                if (day === 0) return today;

                // Calculate days to add to get to the next Sunday
                const daysToAdd = 7 - day;
                const nextSunday = new Date(today);
                nextSunday.setDate(today.getDate() + daysToAdd);
                return nextSunday;
            }

            // Set nearest Sunday as the default date
            window.onload = function() {
                const nearestSunday = getNearestSunday();
                document.getElementById('start-date').valueAsDate = nearestSunday;

                // Add event listener to only allow Sundays
                const dateInput = document.getElementById('start-date');
                dateInput.addEventListener('input', validateSunday);

                // Add event listeners for event type radio buttons
                const eventTypeRadios = document.querySelectorAll('input[name="event-type"]');
                eventTypeRadios.forEach(radio => {
                    radio.addEventListener('change', toggleEndDateTime);
                });

                // Load events
                loadEvents();
            };

            // Validate that the selected date is a Sunday
            function validateSunday() {
                const dateInput = document.getElementById('start-date');
                const selectedDate = new Date(dateInput.value);

                // Check if selected date is a Sunday (day 0)
                if (selectedDate.getDay() !== 0) {
                    alert("Please select a Sunday.");
                    // Find the nearest Sunday from the selected date
                    const day = selectedDate.getDay();
                    const daysToSunday = day === 0 ? 0 : 7 - day;
                    const nextSunday = new Date(selectedDate);
                    nextSunday.setDate(selectedDate.getDate() + daysToSunday);
                    dateInput.valueAsDate = nextSunday;
                }
            }

            // Toggle end date/time fields based on event type
            function toggleEndDateTime() {
                const eventType = document.querySelector('input[name="event-type"]:checked').value;
                const endDateTimeSection = document.getElementById('end-datetime-section');

                if (eventType === 'continuous') {
                    endDateTimeSection.style.display = 'block';
                } else {
                    endDateTimeSection.style.display = 'none';
                    // Clear end date/time values for single events
                    document.getElementById('event-end-date').value = '';
                    document.getElementById('event-end-time').value = '';
                }
            }

            // Format date in dd/mm/yyyy format for display
            function formatDate(date) {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            }

            // Format date in yyyy-mm-dd format for API
            function formatApiDate(date) {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${year}-${month}-${day}`;
            }

            // Format date in yyyy/mm/dd format for CSV
            function formatCsvDate(date) {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${year}/${month}/${day}`;
            }

            // Format short date (dd/mm)
            function formatShortDate(date) {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                return `${day}/${month}`;
            }

            // Generate the week starting from the selected Sunday
            function generateWeek() {
                const startDate = new Date(document.getElementById('start-date').value);

                // Make sure the selected date is a Sunday
                const dayOfWeek = startDate.getDay(); // Sunday is 0
                if (dayOfWeek !== 0) {
                    alert("Please select a Sunday as the start date.");
                    return;
                }

                weekDates = [];
                const weekContainer = document.getElementById('week-days');
                weekContainer.innerHTML = '';

                // Generate 7 days starting from the selected Sunday
                for (let i = 0; i < 7; i++) {
                    const date = new Date(startDate);
                    date.setDate(date.getDate() + i);
                    weekDates.push(date);

                    const dayButton = document.createElement('div');
                    dayButton.className = 'day-button';

                    const dayNameSpan = document.createElement('span');
                    dayNameSpan.className = 'day-name';
                    dayNameSpan.textContent = getDayName(date.getDay()).substr(0, 3); // Abbreviated day name
                    dayButton.appendChild(dayNameSpan);

                    const dayDateSpan = document.createElement('span');
                    dayDateSpan.className = 'day-date';
                    dayDateSpan.textContent = formatShortDate(date);
                    dayButton.appendChild(dayDateSpan);

                    dayButton.setAttribute('data-index', i);
                    dayButton.onclick = function() {
                        selectDay(parseInt(this.getAttribute('data-index')));
                    };

                    weekContainer.appendChild(dayButton);
                }
            }

            // Get the day name
            function getDayName(dayIndex) {
                const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                return days[dayIndex];
            }

            // Select a day
            function selectDay(index) {
                // Reset previously selected day
                const dayButtons = document.querySelectorAll('.day-button');
                dayButtons.forEach(button => button.classList.remove('selected'));

                // Mark selected day
                dayButtons[index].classList.add('selected');
                selectedDayIndex = index;
                customDate = null;

                // Display selected date
                const selectedDate = weekDates[index];
                document.getElementById('selected-day').value = `${getDayName(selectedDate.getDay()).substr(0, 3)} ${formatShortDate(selectedDate)}`;
            }

            // Show custom date dialog
            function showCustomDateDialog() {
                document.getElementById('custom-date-dialog').style.display = 'flex';
            }

            // Close custom date dialog
            function closeCustomDateDialog() {
                document.getElementById('custom-date-dialog').style.display = 'none';
            }

            // Add custom date
            function addCustomDate() {
                const dateInput = document.getElementById('custom-date');
                if (!dateInput.value) {
                    alert("Please select a date.");
                    return;
                }

                customDate = new Date(dateInput.value);
                selectedDayIndex = -1;

                // Deselect any selected day
                const dayButtons = document.querySelectorAll('.day-button');
                dayButtons.forEach(button => button.classList.remove('selected'));

                // Display the custom date
                document.getElementById('selected-day').value = `${getDayName(customDate.getDay()).substr(0, 3)} ${formatShortDate(customDate)} (Custom)`;

                closeCustomDateDialog();
            }

            // Show custom place dialog
            function showCustomPlaceDialog() {
                document.getElementById('custom-place-dialog').style.display = 'flex';
            }

            // Close custom place dialog
            function closeCustomPlaceDialog() {
                document.getElementById('custom-place-dialog').style.display = 'none';
            }

            // Add custom place
            function addCustomPlace() {
                const placeInput = document.getElementById('custom-place');
                if (!placeInput.value.trim()) {
                    alert("Please enter a place name.");
                    return;
                }

                const placeName = placeInput.value.trim();
                const placeSelect = document.getElementById('event-place');

                // Check if the place already exists
                let exists = false;
                for (let i = 0; i < placeSelect.options.length; i++) {
                    if (placeSelect.options[i].text === placeName) {
                        exists = true;
                        placeSelect.selectedIndex = i;
                        break;
                    }
                }

                // Add new option if it doesn't exist
                if (!exists) {
                    const option = document.createElement('option');
                    option.value = placeName;
                    option.text = placeName;
                    placeSelect.add(option);
                    placeSelect.value = placeName;
                }

                placeInput.value = '';
                closeCustomPlaceDialog();
            }

            // Add a new event
            async function addEvent() {
                if (selectedDayIndex === -1 && !customDate) {
                    alert("Please select a day or enter a custom date.");
                    return;
                }

                const title = document.getElementById('event-title').value;
                if (!title) {
                    alert("Please enter a title.");
                    return;
                }

                const time = document.getElementById('event-time').value;
                if (!time) {
                    alert("Please select a time.");
                    return;
                }

                const place = document.getElementById('event-place').value;
                if (!place) {
                    alert("Please select a place.");
                    return;
                }

                const description = document.getElementById('event-description').value;
                if (!description) {
                    alert("Please enter a description.");
                    return;
                }

                // Get event type
                const eventType = document.querySelector('input[name="event-type"]:checked').value;

                // Validate continuous event fields
                let endDate = null;
                let endTime = null;
                if (eventType === 'continuous') {
                    endDate = document.getElementById('event-end-date').value;
                    endTime = document.getElementById('event-end-time').value;

                    if (!endDate) {
                        alert("Please select an end date for continuous events.");
                        return;
                    }
                    if (!endTime) {
                        alert("Please select an end time for continuous events.");
                        return;
                    }

                    // Validate that end date/time is after start date/time
                    const selectedDate = customDate || weekDates[selectedDayIndex];
                    const startDateTime = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate(),
                                                  time.split(':')[0], time.split(':')[1]);
                    const endDateTime = new Date(endDate + 'T' + endTime);

                    if (endDateTime <= startDateTime) {
                        alert("End date/time must be after start date/time.");
                        return;
                    }
                }

                const selectedDate = customDate || weekDates[selectedDayIndex];
                const eventData = {
                    title: title,
                    description: description,
                    date: formatApiDate(selectedDate),
                    time: time,
                    place: place,
                    event_type: eventType,
                    end_date: endDate,
                    end_time: endTime
                };

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(eventData)
                    });

                    if (response.ok) {
                        alert("Event added successfully!");
                        loadEvents();

                        // Clear form
                        document.getElementById('event-title').value = '';
                        document.getElementById('event-time').value = '';
                        document.getElementById('event-place').value = '';
                        document.getElementById('event-description').value = '';
                        document.getElementById('event-end-date').value = '';
                        document.getElementById('event-end-time').value = '';
                        // Reset to single event
                        document.querySelector('input[name="event-type"][value="single"]').checked = true;
                        toggleEndDateTime();
                    } else {
                        alert("Error adding event.");
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert("Error adding event.");
                }
            }

            // Calculate duration for continuous events
            function calculateDuration(event) {
                if (event.event_type !== 'continuous' || !event.end_date || !event.end_time) {
                    return '-';
                }

                const startDateTime = new Date(event.date + 'T' + event.time);
                const endDateTime = new Date(event.end_date + 'T' + event.end_time);

                const diffMs = endDateTime - startDateTime;
                const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
                const diffHours = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));

                let duration = '';
                if (diffDays > 0) {
                    duration += `${diffDays}d `;
                }
                if (diffHours > 0) {
                    duration += `${diffHours}h `;
                }
                if (diffMinutes > 0) {
                    duration += `${diffMinutes}m`;
                }

                return duration.trim() || '0m';
            }

            // Display events
            function displayEvents() {
                const eventsTable = document.getElementById('events-list');
                eventsTable.innerHTML = '';

                if (events.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = '<td colspan="8" style="text-align: center;">No events added yet</td>';
                    eventsTable.appendChild(row);
                    return;
                }

                events.forEach(event => {
                    const row = document.createElement('tr');

                    // Type column
                    const typeCell = document.createElement('td');
                    typeCell.textContent = event.event_type === 'continuous' ? 'Continuous' : 'Single';
                    typeCell.style.fontWeight = 'bold';
                    typeCell.style.color = event.event_type === 'continuous' ? '#e53e3e' : '#2d3748';
                    row.appendChild(typeCell);

                    // Date column
                    const dateCell = document.createElement('td');
                    dateCell.textContent = formatDate(event.date);
                    row.appendChild(dateCell);

                    // Time column
                    const timeCell = document.createElement('td');
                    timeCell.textContent = event.time;
                    row.appendChild(timeCell);

                    // Duration column
                    const durationCell = document.createElement('td');
                    durationCell.textContent = calculateDuration(event);
                    durationCell.style.fontWeight = 'bold';
                    row.appendChild(durationCell);

                    // Place column
                    const placeCell = document.createElement('td');
                    placeCell.textContent = event.place;
                    row.appendChild(placeCell);

                    // Title column
                    const titleCell = document.createElement('td');
                    titleCell.textContent = event.title;
                    row.appendChild(titleCell);

                    // Description column
                    const descCell = document.createElement('td');
                    descCell.textContent = event.description;
                    row.appendChild(descCell);

                    // Action column
                    const actionCell = document.createElement('td');
                    const deleteBtn = document.createElement('button');
                    deleteBtn.textContent = 'Delete';
                    deleteBtn.onclick = function() {
                        deleteEvent(event.id);
                    };
                    actionCell.appendChild(deleteBtn);
                    row.appendChild(actionCell);

                    eventsTable.appendChild(row);
                });
            }

            // Delete an event
            async function deleteEvent(id) {
                if (!confirm("Are you sure you want to delete this event?")) return;

                try {
                    const response = await fetch(API_URL, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: id })
                    });

                    if (response.ok) {
                        alert("Event deleted successfully!");
                        loadEvents();
                    } else {
                        alert("Error deleting event.");
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert("Error deleting event.");
                }
            }

            // Sort events by date or place
            function sortEvents(criteria) {
                if (criteria === 'date') {
                    events.sort((a, b) => {
                        // Sort by date first
                        const dateCompare = a.date - b.date;
                        if (dateCompare !== 0) return dateCompare;

                        // Then by time
                        return a.time.localeCompare(b.time);
                    });
                } else if (criteria === 'place') {
                    events.sort((a, b) => {
                        // Sort by place first
                        const placeCompare = a.place.localeCompare(b.place);
                        if (placeCompare !== 0) return placeCompare;

                        // Then by date and time
                        const dateCompare = a.date - b.date;
                        if (dateCompare !== 0) return dateCompare;

                        return a.time.localeCompare(b.time);
                    });
                }

                displayEvents();
            }

            // Export events to CSV
            function exportToCsv() {
                if (events.length === 0) {
                    alert("No events to export.");
                    return;
                }

                let csvContent = "data:text/csv;charset=utf-8,";

                // Add headers
                csvContent += "date,hour,location,title,description\n";

                // Add data
                events.forEach(event => {
                    const dateStr = formatCsvDate(event.date); // Use yyyy/mm/dd format

                    // Escape fields to handle commas
                    const escapedTitle = `"${event.title.replace(/"/g, '""')}"`;
                    const escapedDesc = `"${event.description.replace(/"/g, '""')}"`;

                    csvContent += `${dateStr},${event.time},${event.place},${escapedTitle},${escapedDesc}\n`;
                });

                // Create download link
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "events.csv");
                document.body.appendChild(link);

                // Trigger download
                link.click();
                document.body.removeChild(link);
            }
