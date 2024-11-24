<head>
    <!-- [Your existing head content] -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Booking</title>
    <link rel="icon" type="image/jpg" href="assets/img/LTO.jpg" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Flatpickr JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <style>

        body, html {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
            background-color: #f9f9f9;
            color: #333;
         }
        /* Optional: Style for available slots in the date picker */
        .available-slots {
            font-size: 0.75em;
            color: #555;
            margin-left: 5px;
        }
        /* Style for holidays */
        .holiday {
            background-color: #ff9f89 !important;
            color: white !important;
        }
        .btn-custom {
            width: 200px; /* Set a fixed width */
            min-height: 45px; /* Ensure all buttons have a consistent height */
            font-size: 16px; /* Adjust font size for uniformity */
            display: inline-block; /* Ensure buttons align properly */
        }
        /* Profile Image in Dropdown */
        .dropdown-item img {
            border-radius: 50%;
            width: 30px;
            height: 30px;
            margin-right: 10px;
        }
    
        .dropdown-item {
            font-size: 1rem; /* Consistent font size for dropdown items */
        }
        .dropdown-menu {
            font-size: 1rem; /* Ensure consistent font size in the dropdown menu */
        }

        .navbar {
            background-color: rgba(255, 255, 255, 0.85);
            box-shadow: 0 1px 10px rgba(0, 0, 0, 0.1);
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
require_once "session.php";
require_once "dbconnect.php"; // Ensure this path is correct

// Retrieve user information from the session or database
$userId = $_SESSION['user_id'] ?? null;

// Initialize $first_name and $last_name to avoid undefined variable warnings
$first_name = "Guest";
$last_name = "";

// Fetch user details if the user ID is set
if ($userId) {
    $stmt = $conn->prepare("SELECT image, first_name, last_name FROM users WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            $first_name = $user['first_name'];
            $last_name = $user['last_name'];
        }
    } else {
        // Handle SQL prepare error
        error_log("Failed to prepare statement: " . $conn->error);
    }
}

// Fetch unread notifications count for the user
$notif_count = 0;
if ($userId) {
    $notif_query = $conn->prepare("SELECT COUNT(*) AS unread_count FROM `notifications` WHERE `user_id` = ? AND `is_read` = 0");
    if ($notif_query) {
        $notif_query->bind_param("i", $userId);
        $notif_query->execute();
        $notif_result = $notif_query->get_result();
        $notif_data = $notif_result->fetch_assoc();
        $notif_count = $notif_data['unread_count'] ?? 0;
        $notif_query->close();
    } else {
        // Handle SQL prepare error
        error_log("Failed to prepare notification count statement: " . $conn->error);
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-sm">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="icon/Land_Transportation_Office.svg" width="30" height="30" class="d-inline-block align-top" alt="LTO Logo">
            <span class="ms-2 d-none d-sm-inline fs-5" style="font-weight: 600;">DUMA Emissions Testing Accredited by LTO</span>
        </a>

        <!-- Toggler for responsive navbar -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links and User Menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Home Link -->
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>

                <!-- Vehicle Link -->
                <li class="nav-item">
                    <a class="nav-link" href="vehicle_main.php">Vehicle</a>
                </li>

                <!-- Notifications Link -->
                <li class="nav-item">
                    <a class="nav-link" href="notifications.php">
                        Notifications <span class="badge bg-danger"><?= $notif_count ?></span>
                    </a>
                </li>

                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <!-- Profile Image -->
                        <img src="<?php echo !empty($user['image']) ? htmlspecialchars($user['image']) : 'default.png'; ?>" alt="Profile Image" class="rounded-circle" width="30" height="30">
                        <span class="ms-2"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="profile.php"><img src="<?php echo !empty($user['image']) ? htmlspecialchars($user['image']) : 'default.png'; ?>" alt="Profile Image"> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../php/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
