<?php
include '../../config.php';
checkAuth();
checkTag('admin'); // Allowed tags
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Scheduler</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../static/css/styles.css">
    <style>
        :root {
            /* Color Variables */
            --primary-blue: #1a365d;
            --accent-red: #e53e3e;
            --attention-gradient: linear-gradient(to right, #e53e3e, #ff6a3d);
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --medium-gray: #666;
            --dark-gray: #333;
            
            /* Typography */
            --font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            --font-size-base: 1rem;
            --font-size-small: 0.875rem;
            --font-size-medium: 1.2rem;
            --font-size-large: 1.5rem;
            --font-size-xlarge: 2.5rem;
            --line-height: 1.6;
            
            /* Spacing */
            --space-xs: 0.5rem;
            --space-sm: 1rem;
            --space-md: 1.5rem;
            --space-lg: 2rem;
            --space-xl: 3rem;
            --space-xxl: 4rem;
            
            /* Borders */
            --border-radius-sm: 4px;
            --border-radius-md: 8px;
            --border-width: 1px;
            
            /* Shadows */
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            
            /* Transitions */
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: var(--font-family);
            line-height: var(--line-height);
            color: var(--dark-gray);
            background-color: var(--light-gray);
            margin: 0;
            padding: var(--space-xl);
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1, h2, h3 {
            color: var(--primary-blue);
            margin-bottom: var(--space-md);
        }
        
        h1 {
            font-size: var(--font-size-xlarge);
            text-align: center;
            margin-bottom: var(--space-xl);
        }
        
        h2 {
            font-size: var(--font-size-large);
            position: relative;
            padding-bottom: var(--space-xs);
        }
        
        h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--accent-red);
        }
        
        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-lg);
        }
        
        .section {
            background-color: var(--white);
            border-radius: var(--border-radius-md);
            padding: var(--space-lg);
            margin-bottom: var(--space-lg);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }
        
        .section:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .week-days {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-xs);
            margin-bottom: var(--space-md);
        }
        
        .day-button {
            padding: var(--space-xs);
            background-color: var(--white);
            border: var(--border-width) solid #ddd;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-size: var(--font-size-small);
            text-align: center;
            width: 70px;
            transition: var(--transition);
        }
        
        .day-button:hover {
            border-color: var(--accent-red);
        }
        
        .day-button .day-name {
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        
        .day-button .day-date {
            font-size: var(--font-size-small);
        }
        
        .day-button.selected {
            background-color: var(--accent-red);
            color: var(--white);
            border-color: var(--accent-red);
        }
        
        .form-group {
            margin-bottom: var(--space-md);
        }
        
        .form-row {
            display: flex;
            gap: var(--space-sm);
            align-items: flex-end;
        }
        
        .form-control {
            flex-grow: 1;
        }
        
        label {
            display: block;
            margin-bottom: var(--space-xs);
            font-weight: bold;
            color: var(--dark-gray);
        }
        
        input, select, textarea {
            width: 100%;
            padding: var(--space-sm);
            border: var(--border-width) solid #ddd;
            border-radius: var(--border-radius-sm);
            box-sizing: border-box;
            font-family: var(--font-family);
            transition: var(--transition);
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px rgba(26, 54, 93, 0.2);
        }
        
        button {
            background-color: var(--accent-red);
            color: var(--white);
            border: none;
            padding: var(--space-sm) var(--space-md);
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            white-space: nowrap;
            font-family: var(--font-family);
            font-weight: bold;
            transition: var(--transition);
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        button.secondary {
            background-color: transparent;
            color: var(--primary-blue);
            border: 2px solid var(--primary-blue);
        }
        
        button.secondary:hover {
            background-color: rgba(26, 54, 93, 0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: var(--space-md);
        }
        
        th, td {
            border: var(--border-width) solid #ddd;
            padding: var(--space-sm);
            text-align: left;
        }
        
        th {
            background-color: var(--light-gray);
            color: var(--primary-blue);
        }
        
        .sort-buttons {
            margin-bottom: var(--space-md);
            display: flex;
            gap: var(--space-sm);
        }
        
        .custom-input-dialog {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            justify-content: center;
            align-items: center;
        }
        
        .dialog-content {
            background-color: var(--white);
            padding: var(--space-lg);
            border-radius: var(--border-radius-md);
            width: 300px;
            box-shadow: var(--shadow-md);
        }
        
        .dialog-buttons {
            display: flex;
            justify-content: flex-end;
            gap: var(--space-sm);
            margin-top: var(--space-md);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            h1 {
                font-size: var(--font-size-large);
                margin-bottom: var(--space-lg);
            }
            
            h2 {
                font-size: var(--font-size-medium);
            }
            
            .section {
                padding: var(--space-md);
            }
        }
    </style>
</head>
<body>
    <div id="nav-placeholder"></div>
    <main class="main-wrapper">
        <h1>Event Scheduler</h1>
        
        <div class="section">
            <h2>Start Date</h2>
            <div class="form-row">
                <div class="form-control">
                    <label for="start-date">Select Sunday to start the week from:</label>
                    <input type="date" id="start-date" onchange="validateSunday()">
                </div>
                <button onclick="generateWeek()">Generate Week</button>
            </div>
            <div id="week-days" class="week-days"></div>
        </div>
        
        <div class="container">
            <div class="section">
                <h2>Create Event</h2>
                <div class="form-row">
                    <div class="form-control">
                        <label for="selected-day">Selected Day:</label>
                        <input type="text" id="selected-day" readonly>
                    </div>
                    <button onclick="showCustomDateDialog()" class="secondary">Custom Date</button>
                </div>
                <div class="form-group">
                    <label for="event-time">Time (24h format):</label>
                    <input type="time" id="event-time">
                </div>
                <div class="form-row">
                    <div class="form-control">
                        <label for="event-place">Place:</label>
                        <select id="event-place">
                            <option value="">Select a place</option>
                            <option value="Conference Room A">Conference Room A</option>
                            <option value="Conference Room B">Conference Room B</option>
                            <option value="Auditorium">Auditorium</option>
                            <option value="Cafeteria">Cafeteria</option>
                            <option value="Meeting Room 1">Meeting Room 1</option>
                            <option value="Meeting Room 2">Meeting Room 2</option>
                        </select>
                    </div>
                    <button onclick="showCustomPlaceDialog()" class="secondary">Add Custom</button>
                </div>
                <div class="form-group">
                    <label for="event-description">Description:</label>
                    <textarea id="event-description" rows="4"></textarea>
                </div>
                <button onclick="addEvent()">Add Event</button>
            </div>
            
            <div class="section">
                <h2>Event List</h2>
                <div class="sort-buttons">
                    <button onclick="sortEvents('date')">Sort by Date</button>
                    <button onclick="sortEvents('place')">Sort by Place</button>
                    <button onclick="exportToCsv()">Export to CSV</button>
                </div>
                <div id="events-container">
                    <table id="events-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Place</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="events-list"></tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Custom Date Dialog -->
        <div id="custom-date-dialog" class="custom-input-dialog">
            <div class="dialog-content">
                <h3>Enter Custom Date</h3>
                <div class="form-group">
                    <label for="custom-date">Date (dd/mm/yyyy):</label>
                    <input type="date" id="custom-date">
                </div>
                <div class="dialog-buttons">
                    <button onclick="closeCustomDateDialog()" class="secondary">Cancel</button>
                    <button onclick="addCustomDate()">Add Date</button>
                </div>
            </div>
        </div>
        
        <!-- Custom Place Dialog -->
        <div id="custom-place-dialog" class="custom-input-dialog">
            <div class="dialog-content">
                <h3>Enter Custom Place</h3>
                <div class="form-group">
                    <label for="custom-place">Place Name:</label>
                    <input type="text" id="custom-place">
                </div>
                <div class="dialog-buttons">
                    <button onclick="closeCustomPlaceDialog()" class="secondary">Cancel</button>
                    <button onclick="addCustomPlace()">Add Place</button>
                </div>
            </div>
        </div>

        <script>
            // Store the week dates and events
            let weekDates = [];
            let events = [];
            let selectedDayIndex = -1;
            let customDate = null;

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

            // Format date in dd/mm/yyyy format for display
            function formatDate(date) {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
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
            function addEvent() {
                if (selectedDayIndex === -1 && !customDate) {
                    alert("Please select a day or enter a custom date.");
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
                
                // Create new event
                const selectedDate = customDate || weekDates[selectedDayIndex];
                const event = {
                    id: Date.now(), // Unique ID using timestamp
                    date: new Date(selectedDate),
                    time: time,
                    place: place,
                    description: description
                };
                
                events.push(event);
                displayEvents();
                
                // Clear form
                document.getElementById('event-time').value = '';
                document.getElementById('event-place').value = '';
                document.getElementById('event-description').value = '';
            }
            
            // Display events
            function displayEvents() {
                const eventsTable = document.getElementById('events-list');
                eventsTable.innerHTML = '';
                
                if (events.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = '<td colspan="5" style="text-align: center;">No events added yet</td>';
                    eventsTable.appendChild(row);
                    return;
                }
                
                events.forEach(event => {
                    const row = document.createElement('tr');
                    
                    const dateCell = document.createElement('td');
                    dateCell.textContent = `${getDayName(event.date.getDay())} ${formatDate(event.date)}`;
                    row.appendChild(dateCell);
                    
                    const timeCell = document.createElement('td');
                    timeCell.textContent = event.time;
                    row.appendChild(timeCell);
                    
                    const placeCell = document.createElement('td');
                    placeCell.textContent = event.place;
                    row.appendChild(placeCell);
                    
                    const descCell = document.createElement('td');
                    descCell.textContent = event.description;
                    row.appendChild(descCell);
                    
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
            function deleteEvent(id) {
                events = events.filter(event => event.id !== id);
                displayEvents();
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
                csvContent += "date,hour,location,description\n";
                
                // Add data
                events.forEach(event => {
                    const dateStr = formatCsvDate(event.date); // Use yyyy/mm/dd format
                    
                    // Escape description to handle commas
                    const escapedDesc = `"${event.description.replace(/"/g, '""')}"`;
                    
                    csvContent += `${dateStr},${event.time},${event.place},${escapedDesc}\n`;
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
        </script>
    </main>
    <div id="admin-username" style="display: none;">
    <script src="adminNav.js"></script>
</body>
</html>
