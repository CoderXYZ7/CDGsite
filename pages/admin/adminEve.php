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
        <div class="page-header">
            <h1><i class="fas fa-calendar-alt"></i> Event Scheduler</h1>
            <p class="page-subtitle">Manage and schedule events for your organization</p>
        </div>

        <!-- Week Selection Section -->
        <div class="section week-selector">
            <div class="section-header">
                <h2><i class="fas fa-calendar-week"></i> Week Selection</h2>
            </div>
            <div class="form-row">
                <div class="form-control">
                    <label for="start-date">Select Sunday to start the week from:</label>
                    <input type="date" id="start-date" onchange="validateSunday()">
                </div>
                <button onclick="generateWeek()" class="primary-btn">
                    <i class="fas fa-sync-alt"></i> Generate Week
                </button>
            </div>
            <div id="week-days" class="week-days"></div>
        </div>

        <!-- Create Event Section -->
        <div class="section create-event-section">
            <div class="section-header">
                <h2><i class="fas fa-plus-circle"></i> Create New Event</h2>
            </div>

            <!-- Compact Form Layout -->
            <div class="compact-form">
                <!-- Row 1: Event Type & Title -->
                <div class="form-row">
                    <div class="form-control event-type-control">
                        <label>Event Type:</label>
                        <div class="radio-group compact">
                            <label class="radio-option">
                                <input type="radio" name="event-type" value="single" checked>
                                <span class="radio-label">Single</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="event-type" value="continuous">
                                <span class="radio-label">Continuous</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-control">
                        <label for="event-title">Title:</label>
                        <input type="text" id="event-title" placeholder="Event title">
                    </div>
                </div>

                <!-- Row 2: Date & Time -->
                <div class="form-row">
                    <div class="form-control">
                        <label for="selected-day">Selected Day:</label>
                        <input type="text" id="selected-day" readonly>
                    </div>
                    <div class="form-control">
                        <label for="event-time">Start Time:</label>
                        <input type="time" id="event-time" onchange="updateDurationPreview()">
                    </div>
                    <div class="form-control">
                        <button onclick="showCustomDateDialog()" class="secondary-btn compact">
                            <i class="fas fa-calendar-plus"></i> Custom Date
                        </button>
                    </div>
                </div>

                <!-- Row 3: Place & Description -->
                <div class="form-row">
                    <div class="form-control">
                        <label for="event-place">Place:</label>
                        <select id="event-place">
                            <option value="">Select place</option>
                            <option value="Conference Room A">Conference Room A</option>
                            <option value="Conference Room B">Conference Room B</option>
                            <option value="Auditorium">Auditorium</option>
                            <option value="Cafeteria">Cafeteria</option>
                            <option value="Meeting Room 1">Meeting Room 1</option>
                            <option value="Meeting Room 2">Meeting Room 2</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <button onclick="showCustomPlaceDialog()" class="secondary-btn compact">
                            <i class="fas fa-plus"></i> Custom
                        </button>
                    </div>
                    <div class="form-control">
                        <label for="event-description">Description:</label>
                        <input type="text" id="event-description" placeholder="Brief description">
                    </div>
                </div>

                <!-- Continuous Event Options -->
                <div id="end-datetime-section" class="continuous-options" style="display: none;">
                    <div class="form-row">
                        <div class="form-control">
                            <label for="event-end-date">End Date:</label>
                            <input type="date" id="event-end-date" onchange="updateDurationPreview()">
                        </div>
                        <div class="form-control">
                            <label for="event-end-time">End Time:</label>
                            <input type="time" id="event-end-time" onchange="updateDurationPreview()">
                        </div>
                        <div class="form-control">
                            <div id="duration-preview" class="duration-info">
                                <i class="fas fa-clock"></i> Duration: calculating...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="form-actions">
                    <button onclick="addEvent()" class="primary-btn">
                        <i class="fas fa-save"></i> Create Event
                    </button>
                </div>
            </div>
        </div>

        <!-- Event List Section -->
        <div class="section event-list-section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Event List</h2>
                <div class="section-actions">
                    <button onclick="sortEvents('date')" class="action-btn">
                        <i class="fas fa-sort-amount-down"></i> Sort by Date
                    </button>
                    <button onclick="sortEvents('place')" class="action-btn">
                        <i class="fas fa-sort-alpha-down"></i> Sort by Place
                    </button>
                    <button onclick="exportToCsv()" class="action-btn">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>
            </div>

            <div id="events-container">
                <div class="table-container">
                    <table id="events-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th><i class="fas fa-calendar-day"></i> Date/Time</th>
                                <th><i class="fas fa-map-marker-alt"></i> Place</th>
                                <th><i class="fas fa-heading"></i> Title</th>
                                <th><i class="fas fa-align-left"></i> Description</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
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
