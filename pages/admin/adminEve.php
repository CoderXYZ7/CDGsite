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
    <link rel="stylesheet" href="assets/adminEve.css">
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
                    <label for="event-time">Time:</label>
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
                    <label for="event-title">Title:</label>
                    <input type="text" id="event-title">
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
                                <th>Title</th>
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

        <script src="assets/adminEve.js"></script>
    </main>
    <div id="admin-username" style="display: none;">
    <script src="assets/adminNav.js"></script>
</body>
</html>
