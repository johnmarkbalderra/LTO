$(function () {
    var calendar;
    var Calendar = FullCalendar.Calendar;
    var events = [];

// Populate events array from scheds
    if (!!scheds) {
        Object.keys(scheds).map(k => {
            var row = scheds[k];

            // Only include events that are not waitlisted
            if (row.waitlist == 0) {
                var event = {
                    id: row.schedule_id,
                    title: row.full_name,
                    start: row.start_datetime,
                    extendedProps: {
                        user_id: row.user_id,
                        vehicle_id: row.vehicle_id || null,
                        phone_number: row.phone_number,
                        email: row.email,
                        vehicle_type: row.vehicle_type,
                        sdate: row.sdate
                    }
                };

                events.push(event); // Add the event to the calendar events
            }
        });
    }

    // Define dynamic holidays and closed days
    var dynamicHolidays = generateDynamicHolidays();
    console.log("Dynamic Holidays:", dynamicHolidays);

    // Admin-managed holidays fetched from PHP
    var adminHolidays = typeof adminHolidays !== 'undefined' ? adminHolidays : [];
    console.log("Admin Holidays (before formatting):", adminHolidays);
    // Add dynamic holidays to events array
    // Dynamic holidays
    var dynamicHolidays = generateDynamicHolidays();
    events = events.concat(dynamicHolidays);

    // Fetch holidays from server (fetch_holidays.php)
    $.ajax({
        url: 'fetch_holidays.php', // Fetch holidays from PHP
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                data.holidays.forEach(function(holiday) {
                    var formattedHoliday = {
                        id: 'holiday_' + holiday.id, // Unique ID for holiday
                        title: holiday.title,
                        start: holiday.start_date,
                        end: holiday.end_date ? holiday.end_date : null,
                        allDay: holiday.all_day,
                        display: 'background',
                        color: holiday.color || '#ff9f89', // Default color if not specified
                        overlap: false,
                        editable: false, // Make holidays non-editable
                        extendedProps: {
                            isHoliday: true
                        }
                    };
                    events.push(formattedHoliday); // Add holiday to events array
                });
                // Add appointment count events
                if (typeof appointmentCounts !== 'undefined') {
                    Object.keys(appointmentCounts).forEach(function(date) {
                        var count = appointmentCounts[date];
                        var remainingSlots = maxSlotsPerDay - count;

                        var event = {
                            title: count + '/' + maxSlotsPerDay + ' appointments',
                            start: date,
                            allDay: true,
                            display: 'background',
                            extendedProps: {
                                appointmentCount: count,
                                remainingSlots: remainingSlots,
                                maxSlots: maxSlotsPerDay
                            }
                        };

                        // If the day is full, mark it differently
                        if (count >= maxSlotsPerDay) {
                            event.color = '#ff9f89'; // Red color for full days
                        } else {
                            event.color = '#d1e7dd'; // Light green for available days
                        }

                        events.push(event);
                    });
                }
                console.log("All Holidays (after fetching):", events);

                // Initialize FullCalendar after fetching holidays
                calendar = new Calendar(document.getElementById('calendar'), {
                    headerToolbar: {
                        left: 'prev,next today',
                        right: 'dayGridMonth,dayGridWeek,list',
                        center: 'title',
                    },
                    selectable: true,
                    themeSystem: 'bootstrap',
                    events: events, // Pass all events including holidays
                    businessHours: {
                        daysOfWeek: [1, 2, 3, 4, 5], // Monday - Friday
                        startTime: '08:00',
                        endTime: '16:00',
                    },
                    selectAllow: function(selectInfo) {
                        var start = selectInfo.start;
                        var end = selectInfo.end;
                        var dateStr = start.toISOString().split('T')[0];

                        // Check if the day is fully booked
                        if (appointmentCounts && appointmentCounts[dateStr] !== undefined && appointmentCounts[dateStr] >= maxSlotsPerDay) {
                            return false; // Disallow selection on full days
                        }

                        // Check if date is a holiday
                        for (var i = 0; i < events.length; i++) {
                            var holiday = events[i];
                            if (holiday.extendedProps.isHoliday) {
                                var holidayStart = new Date(holiday.start);
                                var holidayEnd = holiday.end ? new Date(holiday.end) : holidayStart;

                                // Normalize time for all-day events
                                holidayStart.setHours(0, 0, 0, 0);
                                holidayEnd.setHours(23, 59, 59, 999);
                                var selStart = new Date(start);
                                var selEnd = new Date(end);
                                selStart.setHours(0, 0, 0, 0);
                                selEnd.setHours(23, 59, 59, 999);

                                if (selStart <= holidayEnd && selEnd >= holidayStart) {
                                    return false; // Prevent selection on holidays
                                }
                            }
                        }

                        // Prevent selection on weekends
                        var day = start.getDay(); // 0 = Sunday, 6 = Saturday
                        if (day == 0 || day == 6) {
                            return false;
                        }

                        // Prevent selection outside business hours
                        var hour = start.getHours();
                        if (hour < 8 || hour >= 16) {
                            return false;
                        }

                        return true;
                    },
                    eventClick: function(info) {
                        // If clicked event is a holiday, show alert
                        if (info.event.extendedProps.isHoliday) {
                            alert("This day is a holiday: " + info.event.title);
                            return;
                        }
                        showEventDetails(info);
                    },
                    editable: true,
                    eventContent: function(arg) {
                        if (arg.event.display === 'background' && arg.event.extendedProps.appointmentCount !== undefined) {
                            var countEl = document.createElement('div');
                            countEl.className = 'appointment-count';

                            if (arg.event.extendedProps.appointmentCount >= arg.event.extendedProps.maxSlots) {
                                // Day is full
                                countEl.innerHTML = '<b>Fully Booked</b>';
                            } else {
                                // Show remaining slots
                                countEl.innerHTML = '<b>' + arg.event.extendedProps.appointmentCount + '/' + arg.event.extendedProps.maxSlots + '</b> Appointments';
                            }

                            return { domNodes: [countEl] };
                        }
                    }
                });

                calendar.render(); // Render calendar after initializing with events
            } else {
                alert('Failed to fetch holidays: ' + data.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('Error fetching holidays: ' + textStatus);
        }
    });

    // -------------------------
    // Holiday Management Functions
    // -------------------------

    // Function to generate dynamic holidays (e.g., Good Friday)
    function generateDynamicHolidays() {
        var holidays = [];
        var currentYear = new Date().getFullYear();
        var years = [];
        for (var y = currentYear - 1; y <= currentYear + 5; y++) {
            years.push(y);
        }

        years.forEach(function(year) {
            // New Year's Day
            holidays.push({
                title: "New Year's Day",
                start: year + '-01-01',
                allDay: true,
                display: 'background',
                color: '#ff9f89',
                overlap: false
            });

            holidays.push({
                title: "All Saints Day",
                start: year + '-11-01',
                allDay: true,
                display: 'background',
                color: '#ff9f89',
                overlap: false
            });

            holidays.push({
                title: "All Souls Day",
                start: year + '-11-02',
                allDay: true,
                display: 'background',
                color: '#ff9f89',
                overlap: false
            });

            // Martin Luther King Jr. Day - 3rd Monday of January
            // var mlkDay = getNthWeekdayOfMonth(year, 0, 1, 3);
            // holidays.push({
            //     title: "Martin Luther King Jr. Day",
            //     start: mlkDay,
            //     allDay: true,
            //     display: 'background',
            //     color: '#ff9f89',
            //     overlap: false
            // });

            // Presidents' Day - 3rd Monday of February
            // var presidentsDay = getNthWeekdayOfMonth(year, 1, 1, 3);
            // holidays.push({
            //     title: "Presidents' Day",
            //     start: presidentsDay,
            //     allDay: true,
            //     display: 'background',
            //     color: '#ff9f89',
            //     overlap: false
            // });

            // Memorial Day - Last Monday of May
            // var memorialDay = getLastWeekdayOfMonth(year, 4, 1);
            // holidays.push({
            //     title: "Memorial Day",
            //     start: memorialDay,
            //     allDay: true,
            //     display: 'background',
            //     color: '#ff9f89',
            //     overlap: false
            // });

            // Independence Day
            holidays.push({
                title: "Independence Day",
                start: year + '-07-04',
                allDay: true,
                display: 'background',
                color: '#ff9f89',
                overlap: false
            });

            // Labor Day - First Monday of September
            // var laborDay = getNthWeekdayOfMonth(year, 8, 1, 1);
            // holidays.push({
            //     title: "Labor Day",
            //     start: laborDay,
            //     allDay: true,
            //     display: 'background',
            //     color: '#ff9f89',
            //     overlap: false
            // });

            // Columbus Day - Second Monday of October
            // var columbusDay = getNthWeekdayOfMonth(year, 9, 1, 2);
            // holidays.push({
            //     title: "Columbus Day",
            //     start: columbusDay,
            //     allDay: true,
            //     display: 'background',
            //     color: '#ff9f89',
            //     overlap: false
            // });

            // Veterans Day
            // holidays.push({
            //     title: "Veterans Day",
            //     start: year + '-11-11',
            //     allDay: true,
            //     display: 'background',
            //     color: '#ff9f89',
            //     overlap: false
            // });

            // Thanksgiving Day - Fourth Thursday of November
            // var thanksgivingDay = getNthWeekdayOfMonth(year, 10, 4, 4);
            // holidays.push({
            //     title: "Thanksgiving Day",
            //     start: thanksgivingDay,
            //     allDay: true,
            //     display: 'background',
            //     color: '#ff9f89',
            //     overlap: false
            // });

            // Christmas Day
            holidays.push({
                title: "Christmas Day",
                start: year + '-12-25',
                allDay: true,
                display: 'background',
                color: '#ff9f89',
                overlap: false
            });

            // Good Friday - 2 days before Easter Sunday
            // var easterSunday = calculateEaster(year);
            // var goodFriday = new Date(easterSunday);
            // goodFriday.setDate(goodFriday.getDate() - 2);
            // holidays.push({
            //     title: "Good Friday",
            //     start: formatDate(goodFriday),
            //     allDay: true,
            //     display: 'background',
            //     color: '#ff9f89',
            //     overlap: false
            // });
        });

        return holidays;
    }

    // Helper functions
    function getNthWeekdayOfMonth(year, month, weekday, n) {
        var date = new Date(year, month, 1);
        var count = 0;
        while (date.getMonth() === month) {
            if (date.getDay() === weekday) {
                count++;
                if (count === n) {
                    return formatDate(date);
                }
            }
            date.setDate(date.getDate() + 1);
        }
        return null;
    }

    function getLastWeekdayOfMonth(year, month, weekday) {
        var date = new Date(year, month + 1, 0); // Last day of the month
        while (date.getDay() !== weekday) {
            date.setDate(date.getDate() - 1);
        }
        return formatDate(date);
    }

    function formatDate(date) {
        var year = date.getFullYear();
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var day = ('0' + date.getDate()).slice(-2);
        return year + '-' + month + '-' + day;
    }

    // Calculate Easter Sunday (Western)
    function calculateEaster(year) {
        var f = Math.floor,
            // Golden Number - 1
            G = year % 19,
            C = f(year / 100),
            H = (C - f(C / 4) - f((8 * C + 13) / 25) + 19 * G + 15) % 30,
            I = H - f(H / 28) * (1 - f(29 / (H + 1)) * f((21 - G) / 11)),
            J = (year + f(year / 4) + I + 2 - C + f(C / 4)) % 7,
            L = I - J,
            month = 3 + f((L + 40) / 44),
            day = L + 28 - 31 * f(month / 4);
        return new Date(year, month - 1, day);
    }

    // -------------------------
    // Holiday Management Functions
    // -------------------------

    // Function to populate holidays table in Manage Holidays Modal
    function populateHolidaysTable() {
        $.ajax({
            url: 'fetch_holidays.php', // Ensure this script exists and returns holidays in JSON
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    var tbody = $('#holidaysTable tbody');
                    tbody.empty();
                    data.holidays.forEach(function(holiday) {
                        var row = `
                            <tr data-id="${holiday.id}">
                                <td>${holiday.title}</td>
                                <td>${holiday.start_date}</td>
                                <td>${holiday.end_date || '-'}</td>
                                <td>${holiday.all_day ? 'Yes' : 'No'}</td>
                                <td><span style="display:inline-block;width:20px;height:20px;background-color:${holiday.color || '#ff9f89'};"></span></td>
                                <td>
                                    <button class="btn btn-sm btn-warning editHolidayBtn"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="btn btn-sm btn-danger deleteHolidayBtn"><i class="fas fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    alert('Failed to fetch holidays: ' + data.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error fetching holidays: ' + textStatus);
            }
        });
    }

    // Event handler for opening Manage Holidays Modal
    $('#manageHolidaysModal').on('shown.bs.modal', function () {
        populateHolidaysTable();
    });

    // Event handler for Add Holiday button
    $('#addHolidayBtn').click(function() {
        $('#addEditHolidayModalLabel').text('Add Holiday');
        $('#holidayForm')[0].reset();
        $('#holidayId').val('');
        $('#holidayColor').val('#ff9f89'); // Default color
        $('#addEditHolidayModal').modal('show');
    });

    // Event handler for Edit Holiday button
    $(document).on('click', '.editHolidayBtn', function() {
        var tr = $(this).closest('tr');
        var id = tr.data('id');
        var title = tr.find('td:nth-child(1)').text();
        var start_date = tr.find('td:nth-child(2)').text();
        var end_date = tr.find('td:nth-child(3)').text() !== '-' ? tr.find('td:nth-child(3)').text() : '';
        var all_day = tr.find('td:nth-child(4)').text() === 'Yes';
        var color = tr.find('td:nth-child(5) span').css('background-color');

        // Convert RGB to HEX
        var rgb = color.match(/\d+/g);
        var hex = "#" + ((1 << 24) + (parseInt(rgb[0]) << 16) + (parseInt(rgb[1]) << 8) + parseInt(rgb[2])).toString(16).slice(1);

        $('#addEditHolidayModalLabel').text('Edit Holiday');
        $('#holidayId').val(id);
        $('#holidayTitle').val(title);
        $('#holidayStartDate').val(start_date);
        $('#holidayEndDate').val(end_date);
        $('#holidayAllDay').prop('checked', all_day);
        $('#holidayColor').val(hex);
        $('#addEditHolidayModal').modal('show');
    });

    // Event handler for Delete Holiday button
    $(document).on('click', '.deleteHolidayBtn', function() {
        if (!confirm('Are you sure you want to delete this holiday?')) return;

        var tr = $(this).closest('tr');
        var id = tr.data('id');

        $.ajax({
            url: 'manage_holidays.php', // Ensure this script handles deletion
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'delete',
                id: id
            },
            success: function(data) {
                if (data.success) {
                    alert(data.message);
                    populateHolidaysTable();
                    refreshCalendar(); // Refresh calendar to remove deleted holiday
                } else {
                    alert('Failed to delete holiday: ' + data.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error deleting holiday: ' + textStatus);
            }
        });
    });

    // Event handler for Add/Edit Holiday form submission
    $('#holidayForm').submit(function(e) {
        e.preventDefault();
        var id = $('#holidayId').val();
        var action = id ? 'edit' : 'add';
        var formData = {
            action: action,
            id: id,
            title: $('#holidayTitle').val(),
            start_date: $('#holidayStartDate').val(),
            end_date: $('#holidayEndDate').val() || null,
            all_day: $('#holidayAllDay').is(':checked') ? 1 : 0,
            color: $('#holidayColor').val()
        };

        // Simple Frontend Validation
        if (formData.title.trim() === '' || formData.start_date === '') {
            alert('Please fill in all required fields.');
            return;
        }

        $.ajax({
            url: 'manage_holidays.php', // Ensure this script handles add/edit
            method: 'POST',
            dataType: 'json',
            data: formData,
            success: function(data) {
                if (data.success) {
                    alert(data.message);
                    $('#addEditHolidayModal').modal('hide');
                    populateHolidaysTable();
                    refreshCalendar(); // Refresh calendar to show new/updated holiday
                } else {
                    alert('Failed to save holiday: ' + data.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error saving holiday: ' + textStatus);
            }
        });
    });

    // Function to refresh calendar with updated holidays
    function refreshCalendar() {
        // Remove existing admin holidays from calendar
        allHolidays.forEach(function(holiday) {
            if (holiday.extendedProps && holiday.extendedProps.isHoliday) {
                var event = calendar.getEventById(holiday.id);
                if (event) event.remove();
            }
        });

        // Fetch updated admin holidays from the server
        $.ajax({
            url: 'fetch_holidays.php', // Ensure this script returns updated holidays
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    data.holidays.forEach(function(holiday) {
                        var formattedHoliday = {
                            id: 'holiday_' + holiday.id,
                            title: holiday.title,
                            start: holiday.start_date,
                            end: holiday.end_date ? holiday.end_date : null,
                            allDay: holiday.all_day,
                            display: 'background',
                            color: holiday.color || '#ff9f89',
                            overlap: false,
                            editable: false,
                            extendedProps: {
                                isHoliday: true
                            }
                        };
                        calendar.addEvent(formattedHoliday);
                        allHolidays.push(formattedHoliday);
                    });

                    // Re-render the calendar
                    calendar.refetchEvents();
                } else {
                    alert('Failed to refresh holidays: ' + data.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error refreshing holidays: ' + textStatus);
            }
        });
    }

    // -------------------------
    // Existing Functions
    // -------------------------

// Event Details - Modal Handler
function showEventDetails(info) {
    var _details = $('#event-details-modal');
    var eventObj = info.event.extendedProps;

    if (!!scheds[info.event.id]) {
        _details.find('#full_name').text(info.event.title);
        _details.find('#phone_number').text(eventObj.phone_number);
        _details.find('#email').text(eventObj.email);
        _details.find('#vehicle_type').text(eventObj.vehicle_type);
        _details.find('#start').text(eventObj.sdate);

        // Set data attributes for the "To Wait" button
        $('#to-wait-btn').attr('data-schedule-id', info.event.id);  // Setting the schedule_id for the button

        console.log('Schedule ID set to:', info.event.id);  // Debugging

        // Set data attributes for the "Passed and Notify" button (if needed)
        $('#view-vehicles-btn').data('user-id', eventObj.user_id);
        $('#passed-notify-btn').attr('data-user-id', eventObj.user_id);
        $('#passed-notify-btn').attr('data-vehicle-id', eventObj.vehicle_id);
        $('#failed-notify-btn').attr('data-user-id', eventObj.user_id);
        $('#failed-notify-btn').attr('data-vehicle-id', eventObj.vehicle_id);

        // Enable or disable the button based on vehicle_id presence
        if (eventObj.user_id && eventObj.vehicle_id) {
            $('#passed-notify-btn').prop('disabled', false);
        } else {
            $('#passed-notify-btn').prop('disabled', true);
        }
        

        _details.modal('show');
    } else {
        alert("Event is undefined");
    }
}

    // Handle Passed & Notify User Button Click
    $('#passed-notify-btn').click(function () {
        var userId = $(this).attr('data-user-id');
        var vehicleId = $(this).attr('data-vehicle-id');

        console.log("Passed Notify Button Clicked - User ID:", userId);
        console.log("Passed Notify Button Clicked - Vehicle ID:", vehicleId);

        if (userId && vehicleId) {
            notifyUserPass(userId, vehicleId);
        } else if (userId) {
            // Prompt to select a vehicle
            $('#user-vehicles-modal').modal('show');
        } else {
            alert('Missing user ID.');
        }
    });

    // Handle Failed & Notify User Button Click
    $('#failed-notify-btn').click(function () {
        var userId = $(this).attr('data-user-id');
        var vehicleId = $(this).attr('data-vehicle-id');

        console.log("Failure Notification Button Clicked - User ID:", userId);
        console.log("Failure Notification Button Clicked - Vehicle ID:", vehicleId);

        if (userId && vehicleId) {
            notifyUserFail(userId, vehicleId);
        } else if (userId) {
            // Prompt to select a vehicle
            $('#user-vehicles-modal').modal('show');
        } else {
            alert('Missing user ID.');
        }
    });

    // Notify User Pass Function
    function notifyUserPass(userId, vehicleId) {
        console.log("Notify User - User ID:", userId);
        console.log("Notify User - Vehicle ID:", vehicleId);

        fetch('notify_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                vehicle_id: vehicleId,
                message: 'Your vehicle has passed the pollution test.'
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('User notified successfully!');
                    location.reload();  // Optionally reload the page
                } else {
                    alert('Failed to notify the user: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error caught:', error);
                alert('An error occurred while notifying the user.');
            });
    }


    // Notify User Fail Function
    function notifyUserFail(userId, vehicleId) {
        console.log("Notify User - User ID:", userId);
        console.log("Notify User - Vehicle ID:", vehicleId);

        fetch('notify_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                vehicle_id: vehicleId,
                message: 'Your vehicle has FAILED the pollution test.'
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('User notified successfully!');
                    location.reload();  // Optionally reload the page
                } else {
                    alert('Failed to notify the user: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error caught:', error);
                alert('An error occurred while notifying the user.');
            });
    }


    // Handle "View Vehicles" Button Click
    $(document).on('click', '#view-vehicles-btn', function () {
        var userId = $(this).data('user-id');
        if (userId) {
            loadUserVehicles(userId);
            $('#user-vehicles-modal').modal('show');
        } else {
            alert('User ID is missing.');
        }
    });

    // Load User Vehicles
    function loadUserVehicles(userId) {
        console.log('Loading vehicles for user:', userId);
        $.ajax({
            url: 'get_user_vehicles.php', // Ensure this matches the PHP file name
            method: 'GET',
            data: { user_id: userId },
            success: function (response) {
                console.log("Vehicles Loaded:", response); // Debugging
                $('#vehicle-list').html(response);
            },
            error: function () {
                alert('Failed to load vehicles.');
            }
        });
    }

    // Handle "Passed" Button Click for dynamically added buttons
    $(document).on('click', '.passed-vehicle-btn', function () {
        var userId = $(this).data('user-id');
        var vehicleId = $(this).data('vehicle-id');

        if (userId && vehicleId) {
            var src = 'passed_paper.php?user_id=' + userId + '&vehicle_id=' + vehicleId;
            $('#passed-iframe').attr('src', src);
            $('#passed-modal').modal('show');
        } else {
            alert('User ID or Vehicle ID is missing.');
        }
    });
    // Handle "Failed" Button Click for dynamically added buttons
    $(document).on('click', '.failed-vehicle-btn', function () {
        var userId = $(this).data('user-id');
        var vehicleId = $(this).data('vehicle-id');

        if (userId && vehicleId) {
            var src = 'failed_paper.php?user_id=' + userId + '&vehicle_id=' + vehicleId;
            $('#failed-iframe').attr('src', src);
            $('#failed-modal').modal('show');
        } else {
            alert('User ID or Vehicle ID is missing.');
        }
    });

    

    //To wait button
    document.getElementById('to-wait-btn').addEventListener('click', function () {
    var scheduleId = this.getAttribute('data-schedule-id');

    if (!scheduleId) {
        alert('No schedule ID found.');
        console.log('No schedule ID found.');
        return;
    }

    console.log('Sending schedule ID: ' + scheduleId); // Debug log

    fetch('to_wait.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ schedule_id: scheduleId })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response from server:', data); // Debug log

        if (data.success) {
            alert('Appointment moved to waitlist successfully.');
            
            // Remove the event from the calendar
            var event = calendar.getEventById(scheduleId);
            if (event) {
                event.remove(); // Remove the event from calendar
            }

            // Automatically refresh the page after success
            location.reload();  // Refresh the page to reflect changes
        } else {
            alert('Failed to move the appointment to waitlist: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error in fetch:', error); // Log any fetch errors
        alert('An error occurred: ' + error.message);
    });
});

});
