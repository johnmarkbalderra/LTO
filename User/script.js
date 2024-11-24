// Initialize Flatpickr for appointment scheduling
document.addEventListener('DOMContentLoaded', function () {
    // Attach event listeners to reschedule buttons to populate the reschedule modal
            const rescheduleButtons = document.querySelectorAll('.reschedule-button');
            rescheduleButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const scheduleId = button.getAttribute('data-schedule-id');
                    const startDatetime = button.getAttribute('data-start-datetime');
                    const vehicleType = button.getAttribute('data-vehicle-type');
                    
                    document.getElementById('reschedule_schedule_id').value = scheduleId;
                    document.getElementById('reschedule_date').value = startDatetime.split(' ')[0];
                    document.getElementById('type_of_vehicle_reschedule').value = vehicleType;
                });
            });
    var unavailableDates = []; // Fully booked or closed dates (as strings)
    var holidayDates = [];     // Dynamic holiday dates from database (as strings)
    var staticHolidays = [];   // Static holidays (hardcoded as strings)
    var slotsPerDay = {};      // Available slots for each day (as strings)
    var year = new Date().getFullYear(); // Current year

    // Function to fetch unavailable dates, holidays, and available slots from the server
    function fetchUnavailableDates() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'unavailable_date.php', true); // Fetch unavailable dates and holidays
        xhr.onload = function () {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                unavailableDates = response.unavailable_dates; // Fully booked or closed dates
                holidayDates = response.holiday_dates.map(function(holiday) {
                    return holiday.start_date; // Only consider the start_date for disabling
                }); // Holidays from the database
                slotsPerDay = response.slots_per_day;  // Remaining slots for each day

                // Add static holidays (hardcoded holidays)
                addStaticHolidays();

                // Initialize Flatpickr for both appointment and reschedule date pickers
                initializeDatepicker("#appointment_date", "#appointment_time");
                initializeDatepicker("#reschedule_date", "#reschedule_time");
            }
        };
        xhr.send();
    }

    // Add static holidays (hardcoded holidays as strings)
    function addStaticHolidays() {
        staticHolidays.push(year + '-01-01'); // New Year's Day
        staticHolidays.push(year + '-11-01'); // All Saints Day
        staticHolidays.push(year + '-11-02'); // All Souls Day

        // Calculate dynamic holidays as strings
        // var mlkDay = getNthWeekdayOfMonthAsString(year, 0, 1, 3); // 3rd Monday of January
        // staticHolidays.push(mlkDay);

        var presidentsDay = getNthWeekdayOfMonthAsString(year, 1, 1, 3); // 3rd Monday of February
        staticHolidays.push(presidentsDay);

        // var memorialDay = getLastWeekdayOfMonthAsString(year, 4, 1); // Last Monday of May
        // staticHolidays.push(memorialDay);

        staticHolidays.push(year + '-07-04'); // Independence Day

        // var laborDay = getNthWeekdayOfMonthAsString(year, 8, 1, 1); // 1st Monday of September
        // staticHolidays.push(laborDay);

        // var columbusDay = getNthWeekdayOfMonthAsString(year, 9, 1, 2); // 2nd Monday of October
        // staticHolidays.push(columbusDay);

        staticHolidays.push(year + '-11-11'); // Veterans Day

        // var thanksgivingDay = getNthWeekdayOfMonthAsString(year, 10, 4, 4); // 4th Thursday of November
        // staticHolidays.push(thanksgivingDay);

        staticHolidays.push(year + '-12-25'); // Christmas Day

        var easterSunday = calculateEasterAsString(year);
        var goodFriday = subtractDaysAsString(easterSunday, 2); // Good Friday
        staticHolidays.push(goodFriday);

        // Merge static holidays into the dynamic holidayDates array (as strings)
        holidayDates = holidayDates.concat(staticHolidays);
    }

    // Initialize Flatpickr for date selection
    function initializeDatepicker(dateSelector, timeSelector) {
    flatpickr(dateSelector, {
        dateFormat: "Y-m-d", // Date format for the date picker
        minDate: "today",    // Disable past dates
        altInput: true,      // Human-readable format
        altFormat: "F j, Y", // Display format for the date picker
        disable: [
            function (date) {
                // Convert date to YYYY-MM-DD format
                var dateStr = formatDateAsString(date);
                // Disable weekends and holidays (which are strings)
                return (date.getDay() === 6 || date.getDay() === 0) || unavailableDates.includes(dateStr);
            }
        ],
        onDayCreate: function (dObj, dStr, fp, dayElem) {
            var dateStr = formatDateAsString(dayElem.dateObj);

            // Highlight holidays in the calendar
            if (holidayDates.includes(dateStr)) {
                dayElem.classList.add('holiday');
                dayElem.style.backgroundColor = '#ff9f89'; // Use default holiday color
                dayElem.title = 'Holiday'; // Show holiday title on hover
            }

            // Show available slots for each day
            if (slotsPerDay[dateStr] !== undefined) {
                dayElem.innerHTML += `<span class="available-slots">(${slotsPerDay[dateStr]} slots)</span>`;
            }
        },
        onChange: function (selectedDates, dateStr) {
            // Fetch available time slots when a date is selected
            fetchAvailableTimes(dateStr, timeSelector);
        }
    });
}


    // Function to fetch available time slots for the selected date
    function fetchAvailableTimes(selectedDate, timeSelector) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_available_times.php?selected_date=' + selectedDate, true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                var availableTimes = JSON.parse(xhr.responseText);
                var timePicker = document.querySelector(timeSelector);

                timePicker.innerHTML = ''; // Clear any previous options

                availableTimes.forEach(function (time) {
                    var option = document.createElement('option');
                    option.value = time;
                    option.text = time;
                    timePicker.appendChild(option);
                });

                // Add a default option if no times are available
                if (availableTimes.length === 0) {
                    var option = document.createElement('option');
                    option.value = "";
                    option.text = "No available time slots";
                    timePicker.appendChild(option);
                }
            }
        };
        xhr.send();
    }

    // Utility function to get the Nth weekday of the month as a string
    function getNthWeekdayOfMonthAsString(year, month, weekday, nth) {
        var date = new Date(year, month, 1);
        var add = (weekday - date.getDay() + 7) % 7 + (nth - 1) * 7;
        date.setDate(1 + add);
        return formatDateAsString(date);
    }

    // Utility function to get the last weekday of the month as a string
    function getLastWeekdayOfMonthAsString(year, month, weekday) {
        var date = new Date(year, month + 1, 0);
        var daysToMove = (date.getDay() - weekday + 7) % 7;
        date.setDate(date.getDate() - daysToMove);
        return formatDateAsString(date);
    }

    // Utility function to subtract days from a date string (YYYY-MM-DD)
    function subtractDaysAsString(dateStr, days) {
        var date = new Date(dateStr);
        date.setDate(date.getDate() - days);
        return formatDateAsString(date);
    }

    // Utility function to calculate Easter as a string (YYYY-MM-DD)
    function calculateEasterAsString(year) {
        var f = Math.floor,
            G = year % 19,
            C = f(year / 100),
            H = (C - f(C / 4) - f((8 * C + 13) / 25) + 19 * G + 15) % 30,
            I = H - f(H / 28) * (1 - f(29 / (H + 1)) * f((21 - G) / 11)),
            J = (year + f(year / 4) + I + 2 - C + f(C / 4)) % 7,
            L = I - J,
            month = 3 + f((L + 40) / 44),
            day = L + 28 - 31 * f(month / 4);

        var easter = new Date(year, month - 1, day);
        return formatDateAsString(easter);
    }

    // Utility function to format a date as YYYY-MM-DD
    function formatDateAsString(date) {
        var month = '' + (date.getMonth() + 1);
        var day = '' + date.getDate();
        var year = date.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }

    // Fetch unavailable dates, holidays, and available slots per day
    fetchUnavailableDates();
});