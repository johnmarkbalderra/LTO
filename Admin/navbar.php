<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LTO</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/fullcalendar/lib/main.min.css">
    <script src="./assets/js/jquery-3.6.0.min.js"></script>
    <script src="./assets/js/bootstrap.min.js"></script>
    <script src="./assets/fullcalendar/lib/main.min.js"></script>
    <style>

    body, html {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
    background-color: #f9f9f9;
    color: #333;
    }

        .appointment-count {
    position: absolute;
    bottom: 2px;
    left: 2px;
    background: rgba(13, 110, 253, 0.9); /* Bootstrap primary color */
    color: #fff;
    padding: 2px 4px;
    font-size: 12px;
    border-radius: 3px;
    z-index: 10;
    pointer-events: none;
}
.fc-daygrid-day-frame {
    position: relative;
}
        :root {
            --bs-success-rgb: 71, 222, 152 !important;
        }

        html,
        body {
            height: 100%;
            width: 100%;
        }

        .btn-info.text-light:hover,
        .btn-info.text-light:focus {
            background: #000;
        }

        table,
        tbody,
        td,
        tfoot,
        th,
        thead,
        tr {
            border-color: #ededed !important;
            border-style: solid;
            border-width: 1px !important;
        }

        
.navbar {
    background-color: rgba(255, 255, 255, 0.85);
    box-shadow: 0 1px 10px rgba(0, 0, 0, 0.1);
}
/* Profile Image in Dropdown */
.dropdown-item img {
            border-radius: 50%;
            width: 30px;
            height: 30px;
            margin-right: 10px;
        }

.navbar .nav-link {
    font-weight: 400;
    color: #5a5a5a;
    padding: 10px 18px;
    border-radius: 8px;
}

.navbar .nav-link:hover {
    background-color: #f0f0f5;
}

.modal-content {
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

button:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.5);
}



.navbar .dropdown-menu {
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-outline-primary:hover {
    background-color: #007bff;
    color: white;
}

.btn-outline-success:hover {
    background-color: #28a745;
    color: white;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    color: white;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    color: white;
}

    </style>
</head>

<?php
// navbar.php

// Start the session to manage user authentication
session_start();

// Include the database connection file
require_once "../dbconnect.php"; // Adjust the path if necessary

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // Redirect to the login page or display an error
    header("Location: login.php");
    exit();
}

// Fetch admin details from the database
$adminId = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT image, first_name, last_name FROM admin WHERE admin_id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// If admin details are not found, handle the error
if (!$admin) {
    echo "Admin not found.";
    exit();
}

$first_name = htmlspecialchars($admin['first_name']);
$last_name = htmlspecialchars($admin['last_name']);

// Fetch pending appointments for notifications
$notif_query = $conn->query("
    SELECT 
        sl.schedule_id, 
        CONCAT(u.first_name, ' ', IFNULL(u.middle_name, ''), ' ', u.last_name) AS full_name 
    FROM 
        `schedule_list` sl 
    JOIN 
        users u ON sl.user_id = u.user_id 
    WHERE 
        sl.`status` = 'pending'
");

if (!$notif_query) {
    // Handle query error
    echo "Database Query Failed: " . $conn->error;
    exit();
}

$notif_count = $notif_query->num_rows;
$notifications = $notif_query->fetch_all(MYSQLI_ASSOC);
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
  <div class="container-fluid">
    <!-- Brand -->
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="./assets/icon/Land_Transportation_Office.svg" width="32" height="32" class="d-inline-block align-top" alt="LTO Logo">
      <span class="ms-2 fs-6 text-dark font-weight-normal">DUMA Emissions Testing</span>
    </a>

    <!-- Toggler for responsive navbar -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links and Buttons -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <!-- QR Code Scanner Button -->
        <li class="nav-item">
          <button class="btn btn-outline-primary ms-2 rounded-pill d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#qrScannerModal">
            <i class="fas fa-qrcode me-2"></i> Scan QR Code
          </button>
        </li>

        <!-- Appointments Link -->
        <li class="nav-item">
          <a class="nav-link fs-6" href="index.php">Appointments</a>
        </li>

        <!-- Notifications Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle fs-6" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Notifications <span class="badge bg-danger"><?= $notif_count ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown">
            <?php if ($notif_count > 0): ?>
              <?php foreach ($notifications as $notification): ?>
                <li>
                  <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#notifModal" data-id="<?= $notification['schedule_id'] ?>">
                    New Appointment from <?= htmlspecialchars($notification['full_name']) ?>
                  </a>
                </li>
              <?php endforeach; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="mark_all_read.php">Mark all as read</a></li>
            <?php else: ?>
              <li><a class="dropdown-item" href="#">No new notifications</a></li>
            <?php endif; ?>
          </ul>
        </li>

        <!-- Users Link -->
        <li class="nav-item">
          <a class="nav-link fs-6" href="users.php">Users</a>
        </li>

        <!-- Admin Dropdown -->
        <!-- <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle fs-6" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?= $first_name . ' ' . $last_name ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
          </ul>
        </li> -->



        <!-- User Profile Dropdown -->
        <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <!-- Profile Image -->
                        <img src="<?php echo !empty($admin['image']) ? htmlspecialchars($admin['image']) : 'default.png'; ?>" alt="Profile Image" class="rounded-circle" width="30" height="30">
                        <span class="ms-2"><?= htmlspecialchars($admin['first_name'] . " " . $admin['last_name']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="profile.php"><img src="<?php echo !empty($admin['image']) ? htmlspecialchars($admin['image']) : 'default.png'; ?>" alt="Profile Image"> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../php/logout.php">Logout</a></li>
                    </ul>
                </li>

      </ul>
    </div>
  </div>
</nav>

<!-- Notifications Modal -->
<div class="modal fade" id="notifModal" tabindex="-1" aria-labelledby="notifModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded-3 shadow-lg">
      <div class="modal-header bg-primary text-light">
        <h5 class="modal-title" id="notifModalLabel">Appointment Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="appointment-details">
          <p>Loading appointment details...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-success" id="approveBtn">Approve</button>
        <button type="button" class="btn btn-outline-danger" id="disapproveBtn">Disapprove</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!-- QR Code Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-light rounded-1">
        <h5 class="modal-title" id="qrScannerModalLabel">QR Code Scanner</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="qrScannerCloseBtn"></button>
      </div>
      <div class="modal-body">
        <div id="qr-reader" style="width: 100%;"></div>
        <div id="qr-reader-results" class="mt-3">
          <!-- Scanned QR Code details will be displayed here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Include FontAwesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
      integrity="sha512-pb2t29n8D4lD6rWcYpP6s6k0ABbXB4VvY2mVHTEGrf0EIfOKQbH0eM8YmnhJpHg3gyNRV/wJN12NHlIabnEoLQ==" 
      crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Include html5-qrcode Library -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<!-- Include jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" 
        integrity="sha256-K+0HPHCOc4I5TjeH6K7qwKkO/QTwMYZ24QXc/fj2ekg=" 
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-q2RyPH8qrJrYtV6VZ1v4RTfOYO7whhFx4eXfnJAMql6F2IhUNcqA0+mbhOjktEf6" 
        crossorigin="anonymous"></script>

<!-- Custom JavaScript for Notifications and QR Code Scanner -->
<script>
$(document).ready(function() {
    // Handle Notification Modal Show Event
$('#notifModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var appointmentId = button.data('id'); // Extract info from data-* attributes
    var modal = $(this);

    // Store appointmentId in buttons for later use
    modal.find('#approveBtn').data('id', appointmentId);
    modal.find('#disapproveBtn').data('id', appointmentId);

    // Load appointment details via AJAX
    $.ajax({
        url: 'get_appointment_details.php', // Ensure this path is correct
        type: 'GET',
        data: { id: appointmentId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Populate the modal with appointment details
                $('#appointment-details').html(`
                    <dl>
                        <dt class="text-muted">Name</dt>
                        <dd class="fw-bold fs-4">${response.full_name}</dd>
                        <dt class="text-muted">Phone</dt>
                        <dd>${response.phone_number}</dd>
                        <dt class="text-muted">Email</dt>
                        <dd>${response.email}</dd>
                        <dt class="text-muted">Vehicle Type</dt>
                        <dd>${response.vehicle_type}</dd>
                        <dt class="text-muted">Date</dt>
                        <dd class="fw-bold fs-5">${new Date(response.start_datetime).toLocaleString()}</dd>
                    </dl>
                `);
            } else {
                // Display error message in the modal
                $('#appointment-details').html(`<div class="alert alert-danger">${response.error}</div>`);
            }
        },
        error: function(xhr, status, error) {
            console.error('Failed to load appointment details:', xhr.responseText);
            $('#appointment-details').html(`<div class="alert alert-danger">Failed to load appointment details.</div>`);
        }
    });
});


    // Approve Appointment Handler
    $('#approveBtn').on('click', function() {
        var appointmentId = $(this).data('id');

        // Confirmation before approving
        if (!confirm('Are you sure you want to approve this appointment?')) {
            return;
        }

        $.ajax({
            url: 'approve_or_mark_read.php', // Ensure this path is correct
            type: 'POST',
            data: { id: appointmentId, action: 'approve' }, // Pass action
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Appointment approved and QR code generated successfully.');
                    $('#notifModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    alert('Failed to approve appointment. Error: ' + response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', jqXHR.responseText);
                alert('AJAX error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    // Disapprove Appointment Handler
    $('#disapproveBtn').on('click', function() {
        var appointmentId = $(this).data('id');

        // Confirmation before disapproving
        if (!confirm('Are you sure you want to disapprove this appointment?')) {
            return;
        }

        $.ajax({
            url: 'disapprove_appointment.php', // Ensure this path is correct
            type: 'POST',
            data: { id: appointmentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Appointment has been disapproved successfully.');
                    $('#notifModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    alert('Failed to disapprove appointment. Error: ' + response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', jqXHR.responseText);
                alert('AJAX error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    // QR Code Scanner Initialization
    var html5QrcodeScanner;

    $('#qrScannerModal').on('shown.bs.modal', function () {
        // Initialize the QR Code Scanner
        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5Qrcode("qr-reader");

            // Configure the scanner
            var config = { fps: 10, qrbox: 300 };

            // Start scanning
            html5QrcodeScanner.start(
                { facingMode: "environment" }, // Use the back camera
                config,
                qrCodeSuccessCallback,
                qrCodeErrorCallback
            ).catch(err => {
                console.error("Unable to start scanning.", err);
                alert("Unable to access camera for scanning.");
            });
        }
    });

    $('#qrScannerModal').on('hidden.bs.modal', function () {
        // Stop scanning when the modal is closed
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(ignore => {
                html5QrcodeScanner.clear();
                html5QrcodeScanner = null;
                $('#qr-reader-results').html(''); // Clear previous results
            }).catch(err => {
                console.error("Unable to stop scanning.", err);
            });
        }
    });

    // Callback when QR Code is successfully scanned
    function qrCodeSuccessCallback(decodedText, decodedResult) {
    console.log(`QR Code detected: ${decodedText}`, decodedResult);

    // Stop the scanner after successful scan
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(ignore => {
            html5QrcodeScanner.clear();
            html5QrcodeScanner = null;
        }).catch(err => {
            console.error("Unable to stop scanning.", err);
        });
    }

    // Parse and display the results
    var appointmentDetails = parseQRCodeData(decodedText);

    if (appointmentDetails) {
        $('#qr-reader-results').html(`
            <h5 class="fs-6">QR Code Details:</h5>
            <dl>
                <dt>Name</dt><dd>${appointmentDetails.Name}</dd>
                <dt>Phone</dt><dd>${appointmentDetails.Phone}</dd>
                <dt>Email</dt><dd>${appointmentDetails.Email}</dd>
                <dt>Vehicle Type</dt><dd>${appointmentDetails.Vehicle}</dd>
                <dt>Date</dt><dd>${appointmentDetails.Date}</dd>
            </dl>
        `);
    } else {
        $('#qr-reader-results').html(`<div class="alert alert-danger">Invalid QR Code format.</div>`);
    }
}

    // Callback for QR Code scan errors (optional)
    function qrCodeErrorCallback(errorMessage) {
        // Optionally log scan errors or show them to the user
        console.warn(`QR Code scan error: ${errorMessage}`);
        // You can display scan errors if desired
        // $('#qr-reader-results').html(`<div class="text-danger">Scan Error: ${errorMessage}</div>`);
    }

    // Function to parse QR Code data
    function parseQRCodeData(qrData) {
        // Assuming the QR Code contains data in the following format:
        // "Appointment Details:
        // Name: John Doe
        // Phone: 1234567890
        // Email: johndoe@example.com
        // Vehicle: Car
        // Date: January 1, 2024, 10:00 AM"

        var lines = qrData.split('\n');
        var details = {};

        lines.forEach(function(line) {
            var parts = line.split(':');
            if (parts.length >= 2) {
                var key = parts[0].trim();
                var value = parts.slice(1).join(':').trim();
                details[key] = value;
            }
        });

        // Check if all required fields are present
        if (details['Name'] && details['Phone'] && details['Email'] && details['Vehicle'] && details['Date']) {
            return details;
        } else {
            return null;
        }
    }
});
</script>
