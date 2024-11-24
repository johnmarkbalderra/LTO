<?php include('db-connect.php') 
?>
<!DOCTYPE html>
<html lang="en">


<?php 
include 'navbar.php';

    $schedules = $conn->query("
        SELECT sl.*, uv.user_id, uv.vehicle_id
        FROM schedule_list sl
        LEFT JOIN user_vehicles uv ON sl.user_id = uv.user_id
        WHERE sl.status = 'approved' AND sl.waitlist = 0
    ");

    $sched_res = [];
    foreach ($schedules->fetch_all(MYSQLI_ASSOC) as $row) {
        $row['sdate'] = date("F d, Y h:i A", strtotime($row['start_datetime']));
        $sched_res[$row['schedule_id']] = $row;
    }

    // Fetch holidays
    $holidays_query = $conn->query("SELECT * FROM `holidays` ORDER BY `start_date` ASC");
    $holidays_res = [];
    if ($holidays_query) {
    while ($holiday = $holidays_query->fetch_assoc()) {
        // Format dates as needed
        $holiday['start'] = $holiday['start_date'];
        $holiday['end'] = ($holiday['end_date'] && $holiday['end_date'] != '0000-00-00') ? $holiday['end_date'] : null;
        $holidays_res[] = $holiday;
    }
    }
    $holidays_res = [];
    foreach ($holidays_query->fetch_all(MYSQLI_ASSOC) as $holiday) {
        // Format dates as needed
        $holiday['start'] = $holiday['start_date'];
        $holiday['end'] = $holiday['end_date'] ? $holiday['end_date'] : $holiday['start_date'];
        $holidays_res[] = $holiday;
    }
    // Set the maximum number of slots per day
    $max_slots_per_day = 50;

    // Fetch daily appointment counts
    $appointment_counts_query = $conn->query("
        SELECT DATE(start_datetime) as date, COUNT(*) as total
        FROM schedule_list
        WHERE status = 'approved'
        GROUP BY DATE(start_datetime)
    ");
    $appointment_counts = [];
        while ($row = $appointment_counts_query->fetch_assoc()) {
    $appointment_counts[$row['date']] = $row['total'];
    }
?>

<script>
    var scheds = <?= isset($sched_res) ? json_encode($sched_res) : '[]'; ?>;
    var adminHolidays = <?= isset($holidays_res) ? json_encode($holidays_res) : '[]'; ?>;
    var appointmentCounts = <?= json_encode($appointment_counts); ?>;
    var maxSlotsPerDay = <?= $max_slots_per_day; ?>;
</script>

<style>

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f7;
            color: #333;
        }

        .btn {
            border-radius: 12px;
            padding: 10px 20px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .btn-info {
            background-color: #4C9EE8;
        }

        .btn-warning {
            background-color: #FFAC00;
        }

        .btn-success {
            background-color: #28A745;
        }

        .modal-content {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .modal-header, .modal-footer {
            background-color: #ffffff;
            border-bottom: 1px solid #ddd;
        }

        .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header, .footer {
            text-align: center;
            padding: 20px 0;
        }

        .header {
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        footer {
            background-color: #f5f5f7;
            padding: 15px 0;
        }
    </style>


<body class="bg-light">
    <!-- Management Buttons -->
    <div class="container py-5" id="page-container">
        <div class="row justify-content-center">
            <div class="col-md-10 text-dark rounded-3 shadow-sm bg-white p-4">
                
                <div class="d-flex justify-content-between mb-4 align-items-center">
                    <!-- View Wait List Button -->
                    <button class="btn btn-warning px-4 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#waitlistModal">
                        <i class="fas fa-clock me-2"></i> View Wait List
                    </button>
                    
                    <!-- Manage Holidays Button -->
                    <button class="btn btn-success px-4 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#manageHolidaysModal">
                        <i class="fas fa-calendar-plus me-2"></i> Manage Holidays
                    </button>

                    <!-- Edit Vehicle Variables Button -->
                    <button class="btn btn-info px-4 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#editVehicleModal">
                        <i class="fas fa-cogs me-2"></i> Edit Vehicle Variables
                    </button>
                </div>

                <div class="" id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Manage Holidays Modal -->
    <div class="modal fade" id="manageHolidaysModal" tabindex="-1" aria-labelledby="manageHolidaysModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-light rounded-1">
                    <h5 class="modal-title" id="manageHolidaysModalLabel">Manage Holidays</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add Holiday Button -->
                    <div class="mb-3">
                        <button class="btn btn-primary" id="addHolidayBtn"><i class="fas fa-plus"></i> Add Holiday</button>
                    </div>
                    <!-- Holidays Table -->
                    <table class="table table-bordered" id="holidaysTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>All Day</th>
                                <th>Color</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamically populated via script.js -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
        <!-- Add/Edit Holiday Modal -->
        <div class="modal fade" id="addEditHolidayModal" tabindex="-1" aria-labelledby="addEditHolidayModalLabel" aria-hidden="true">
            <div class="modal-dialog">
            <div class="modal-content">
                <form id="holidayForm">
                    <div class="modal-header bg-primary text-light rounded-1">
                        <h5 class="modal-title" id="addEditHolidayModalLabel">Add Holiday</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="holidayId" name="id">
                        <div class="mb-3">
                            <label for="holidayTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="holidayTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="holidayStartDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="holidayStartDate" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="holidayEndDate" class="form-label">End Date (Optional)</label>
                            <input type="date" class="form-control" id="holidayEndDate" name="end_date">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="holidayAllDay" name="all_day" checked>
                            <label class="form-check-label" for="holidayAllDay">All Day</label>
                        </div>
                        <div class="mb-3">
                            <label for="holidayColor" class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color" id="holidayColor" name="color" value="#ff9f89" title="Choose your color">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="saveHolidayBtn">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Wait List Modal -->
    <div class="modal fade" id="waitlistModal" tabindex="-1" aria-labelledby="waitlistModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark rounded-1">
                    <h5 class="modal-title" id="waitlistModalLabel">Waitlisted Appointments</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Waitlist Table -->
                    <table class="table table-bordered" id="waitlistTable">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Phone Number</th>
                                <th>Email</th>
                                <th>Vehicle Type</th>
                                <th>Appointment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamically populated via PHP -->
                            <?php
                            // Fetch waitlisted schedules with a subquery to get only one vehicle per user
                                $waitlist_query = $conn->query("
                                    SELECT sl.*, 
                                        (SELECT vehicle_id FROM user_vehicles WHERE user_id = sl.user_id LIMIT 1) as vehicle_id
                                    FROM schedule_list sl
                                    WHERE sl.waitlist = 1
                                ");

                            while ($row = $waitlist_query->fetch_assoc()) {
                                echo "
                                <tr>
                                    <td>{$row['full_name']}</td>
                                    <td>{$row['phone_number']}</td>
                                    <td>{$row['email']}</td>
                                    <td>{$row['vehicle_type']}</td>
                                    <td>" . date("F d, Y h:i A", strtotime($row['start_datetime'])) . "</td>
                                </tr>
                                ";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="event-details-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-0">
                <div class="modal-header bg-primary text-light rounded-1">
                    <h5 class="modal-title">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body rounded-0">
                    <div class="container-fluid">
                        <dl>
                            <dt class="text-muted">Name</dt>
                            <dd id="full_name" class="fw-bold fs-4"></dd>
                            <dt class="text-muted">Phone</dt>
                            <dd id="phone_number" class=""></dd>
                            <dt class="text-muted">Email</dt>
                            <dd id="email" class=""></dd>
                            <dt class="text-muted">Vehicle Type</dt>
                            <dd id="vehicle_type" class=""></dd>
                            <dt class="text-muted">Date</dt>
                            <dd id="start" class="fw-bold fs-5"></dd>
                        </dl>
                    </div>
                </div>
                <div class="modal-footer rounded-0">
                    <div class="text-end">
                        <!-- Button to waitlist -->
                        <button type="button" class="btn btn-warning btn-sm rounded-0" id="to-wait-btn" data-schedule-id="">
                            To Wait
                        </button>
                        <button type="button" class="btn btn-info" id="view-vehicles-btn" data-bs-toggle="modal" data-bs-target="#user-vehicles-modal">
                            View Vehicles
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- User Vehicles Modal -->
<div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="user-vehicles-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-0">
                <div class="modal-header bg-primary text-light rounded-1">
                    <h5 class="modal-title">User's Vehicles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body rounded-0">
                    <div class="container-fluid" id="vehicle-list">
                        <!-- Vehicles list will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer rounded-0">
                    <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Passed Modal -->
    <div class="modal fade" tabindex="-1" id="passed-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-light rounded-1">
                    <h5 class="modal-title">Pollution Test Certificate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                    <h5 class="modal-title">Please check your certificate of registration AND OFFICIAL RECEIPT to ensure all the details are correct!</h5>
                <div class="modal-body">
                    <!-- Load passed_paper.php content inside an iframe -->
                    <iframe id="passed-iframe" src="" width="100%" height="600px" style="border:none;"></iframe>
                </div>
                <div class="modal-footer">
                    <!-- Print Button -->
                    <button type="button" class="btn btn-primary" onclick="printPassedCertificate()" id="passed-notify-btn" data-schedule-id="SCHEDULE_ID">Passed & Print User</button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Failed Modal -->
    <div class="modal fade" tabindex="-1" id="failed-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-light rounded-1">
                    <h5 class="modal-title">Pollution Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                    <h5 class="modal-title">Please check your certificate of registration AND OFFICIAL RECEIPT to ensure all the details are correct!</h5>
                <div class="modal-body">
                    <!-- Load passed_paper.php content inside an iframe -->
                    <iframe id="failed-iframe" src="" width="100%" height="600px" style="border:none;"></iframe>
                </div>
                <div class="modal-footer">
                    <!-- Print Button -->
                    <!--<button type="button" class="btn btn-primary" onclick="printPassedCertificate()">Print</button>-->
                    <button type="button" class="btn btn-primary" onclick="printFailedCertificate()" id="failed-notify-btn">Failed & Print User</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>










<?php

// Function to fetch options from the database
function fetchOptions($conn, $table, $orderByColumn) {
    $sql = "SELECT * FROM $table ORDER BY $orderByColumn ASC";
    $result = $conn->query($sql);
    $options = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }
    }
    return $options;
}

// Fetch options from the database for different vehicle variables
$bodyTypes = fetchOptions($conn, 'body_types', 'type_name');
$vehicleBrands = fetchOptions($conn, 'vehicle_brands', 'brand_name');
$vehicleColors = fetchOptions($conn, 'vehicle_colors', 'color_name');
$vehicleFuels = fetchOptions($conn, 'vehicle_fuels', 'fuel_name');

// Function to add an option to the database
function addOption($conn, $table, $column, $name) {
    $sql = "INSERT INTO $table ($column) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->close();
}

// Function to edit an option in the database
function editOption($conn, $table, $column, $id, $name) {
    $sql = "UPDATE $table SET $column = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    $stmt->close();
}

// Function to delete an option from the database
function deleteOption($conn, $table, $id) {
    $sql = "DELETE FROM $table WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Handling form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $table = $_POST['table'];
        $column = $_POST['column'];
        
        if ($action === 'add') {
            $name = $_POST['name'];
            addOption($conn, $table, $column, $name);
        } elseif ($action === 'edit') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            editOption($conn, $table, $column, $id, $name);
        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            deleteOption($conn, $table, $id);
        }

        // Redirect or reload page after handling form submission
        header("Location: index.php");
        exit();
    }
}
?>


<!-- Full Vehicle Variables Modal -->
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-labelledby="editVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-light">
                <h5 class="modal-title" id="editVehicleModalLabel">Edit Vehicle Variables</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs" id="vehicleVariablesTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="bodyTypeTab" data-bs-toggle="tab" href="#bodyType" role="tab" aria-controls="bodyType" aria-selected="true">Body Types</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="vehicleBrandTab" data-bs-toggle="tab" href="#vehicleBrand" role="tab" aria-controls="vehicleBrand" aria-selected="false">Vehicle Brands</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="vehicleColorTab" data-bs-toggle="tab" href="#vehicleColor" role="tab" aria-controls="vehicleColor" aria-selected="false">Vehicle Colors</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="vehicleFuelTab" data-bs-toggle="tab" href="#vehicleFuel" role="tab" aria-controls="vehicleFuel" aria-selected="false">Vehicle Fuels</a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content mt-3" id="vehicleVariablesTabContent">
                    <!-- Body Types Tab -->
                    <div class="tab-pane fade show active" id="bodyType" role="tabpanel" aria-labelledby="bodyTypeTab">
                        <table class="table table-bordered" id="bodyTypeTable">
                            <thead>
                            <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addOptionModal" data-table="body_types" data-column="type_name">Add Body Type</button>
                                <tr>
                                    <th>ID</th>
                                    <th>Body Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($bodyTypes as $bodyType) {
                                    echo "<tr>";
                                    echo "<td>" . $bodyType['id'] . "</td>";  // Display the ID
                                    echo "<td>" . $bodyType['type_name'] . "</td>";
                                    echo "<td>
                                            <button class='btn btn-warning btn-sm edit-btn' data-id='{$bodyType['id']}' data-name='{$bodyType['type_name']}'>Edit</button> 
                                            <button class='btn btn-danger btn-sm delete-btn' data-id='{$bodyType['id']}'>Delete</button>
                                          </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Vehicle Brands Tab -->
                    <div class="tab-pane fade" id="vehicleBrand" role="tabpanel" aria-labelledby="vehicleBrandTab">
                        <table class="table table-bordered" id="vehicleBrandTable">
                            <thead>
                            <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addOptionModal" data-table="vehicle_brands" data-column="brand_name">Add Vehicle Brand</button>
                                <tr>
                                    <th>ID</th>
                                    <th>Vehicle Brand</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($vehicleBrands as $brand) {
                                    echo "<tr>";
                                    echo "<td>" . $brand['id'] . "</td>";  // Display the ID
                                    echo "<td>" . $brand['brand_name'] . "</td>";
                                    echo "<td>
                                            <button class='btn btn-warning btn-sm edit-btn' data-id='{$brand['id']}' data-name='{$brand['brand_name']}'>Edit</button> 
                                            <button class='btn btn-danger btn-sm delete-btn' data-id='{$brand['id']}'>Delete</button>
                                          </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Vehicle Colors Tab -->
                    <div class="tab-pane fade" id="vehicleColor" role="tabpanel" aria-labelledby="vehicleColorTab">
                        <table class="table table-bordered" id="vehicleColorTable">
                            <thead>
                            <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addOptionModal" data-table="vehicle_colors" data-column="color_name">Add Vehicle Color</button>
                                <tr>
                                    <th>ID</th>
                                    <th>Vehicle Color</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($vehicleColors as $color) {
                                    echo "<tr>";
                                    echo "<td>" . $color['id'] . "</td>";  // Display the ID
                                    echo "<td>" . $color['color_name'] . "</td>";
                                    echo "<td>
                                            <button class='btn btn-warning btn-sm edit-btn' data-id='{$color['id']}' data-name='{$color['color_name']}'>Edit</button> 
                                            <button class='btn btn-danger btn-sm delete-btn' data-id='{$color['id']}'>Delete</button>
                                          </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Vehicle Fuels Tab -->
                    <div class="tab-pane fade" id="vehicleFuel" role="tabpanel" aria-labelledby="vehicleFuelTab">
                        <table class="table table-bordered" id="vehicleFuelTable">
                            <thead>
                            <button class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addOptionModal" data-table="vehicle_fuels" data-column="fuel_name">Add Vehicle Fuel</button>
                                <tr>
                                    <th>ID</th>
                                    <th>Vehicle Fuel Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($vehicleFuels as $fuel) {
                                    echo "<tr>";
                                    echo "<td>" . $fuel['id'] . "</td>";  // Display the ID
                                    echo "<td>" . $fuel['fuel_name'] . "</td>";
                                    echo "<td>
                                            <button class='btn btn-warning btn-sm edit-btn' data-id='{$fuel['id']}' data-name='{$fuel['fuel_name']}'>Edit</button> 
                                            <button class='btn btn-danger btn-sm delete-btn' data-id='{$fuel['id']}'>Delete</button>
                                          </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <!-- <button type="button" class="btn btn-primary" id="saveVehicleVariablesBtn">Save Changes</button> -->
            </div>
        </div>
    </div>
</div>

<!-- Add Option Modal -->
<div class="modal fade" id="addOptionModal" tabindex="-1" aria-labelledby="addOptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-light">
                <h5 class="modal-title" id="addOptionModalLabel">Add New Option</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addOptionForm">
                    <div class="mb-3">
                        <label for="optionName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="optionName" name="optionName" required>
                        <input type="hidden" id="optionTable" name="optionTable">
                        <input type="hidden" id="optionColumn" name="optionColumn">
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Add Option</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Option Modal -->
<div class="modal fade" id="editOptionModal" tabindex="-1" aria-labelledby="editOptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editOptionModalLabel">Edit Option</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editOptionForm">
                    <div class="mb-3">
                        <label for="editOptionName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="editOptionName" name="editOptionName" required>
                        <input type="hidden" id="editOptionId" name="editOptionId">
                        <input type="hidden" id="editOptionTable" name="editOptionTable">
                        <input type="hidden" id="editOptionColumn" name="editOptionColumn">
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Option Modal -->
<div class="modal fade" id="deleteOptionModal" tabindex="-1" aria-labelledby="deleteOptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-light">
                <h5 class="modal-title" id="deleteOptionModalLabel">Delete Option</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="deleteOptionForm">
                    <p>Are you sure you want to delete this option?</p>
                    <input type="hidden" id="deleteOptionId" name="deleteOptionId">
                    <input type="hidden" id="deleteOptionTable" name="deleteOptionTable">
                    <input type="hidden" id="deleteOptionColumn" name="deleteOptionColumn">
                    <div class="text-center">
                        <button type="submit" class="btn btn-danger">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function printPassedCertificate() {
    var iframe = document.getElementById('passed-iframe');
    iframe.contentWindow.focus();
    iframe.contentWindow.print();

    // Assume that userId and vehicleId are available
    var vehicleId = document.getElementById('passed-notify-btn').getAttribute('data-vehicle-id');

    if (!vehicleId) {
        alert('Vehicle ID not found');
        return;
    }

    // Update the pass status to 1
    fetch('update_passandfail_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            vehicle_id: vehicleId  // Send vehicle_id to the backend
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
            alert('status updated successfully!');
        } else {
            alert('Failed to update pass status: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error caught:', error);
        alert('An error occurred while updating pass status.');
    });
}

    //fail
    function printFailedCertificate() {
            var iframe = document.getElementById('failed-iframe');
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        // Assume that userId, vehicleId, and scheduleId are available
    var scheduleId = document.getElementById('failed-notify-btn').getAttribute('data-schedule-id');

    if (!scheduleId) {
        alert('Schedule ID not found');
        return;
    }

    // Update the is_read status to 1
    fetch('update_passandfail_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            vehicle_id: vehicleId
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
            alert('status updated successfully!');
        } else {
            alert('Failed to update read status: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error caught:', error);
        alert('An error occurred while updating read status.');
    });
}


// Edit Vehicle Varibles modals script
<!-- JavaScript to handle modals -->
document.addEventListener('DOMContentLoaded', function () {
    const addOptionModal = new bootstrap.Modal(document.getElementById('addOptionModal'));
    const editOptionModal = new bootstrap.Modal(document.getElementById('editOptionModal'));
    const deleteOptionModal = new bootstrap.Modal(document.getElementById('deleteOptionModal'));

    function refreshTable() {
        // Fetch updated table content via AJAX and update the DOM
        fetch('fetch_vehicle_variables.php').then(response => response.text()).then(data => {
            document.getElementById('vehicleVariablesTabContent').innerHTML = data;
            // Reattach event listeners to the new buttons
            attachEventListeners();
        }).catch(error => console.error('Error:', error));
    }

    function attachEventListeners() {
        document.querySelectorAll('.add-btn').forEach(button => {
            button.addEventListener('click', function () {
                const table = this.getAttribute('data-table');
                const column = this.getAttribute('data-column');
                document.getElementById('optionTable').value = table;
                document.getElementById('optionColumn').value = column;
                document.getElementById('addOptionForm').reset();
                addOptionModal.show();
            });
        });

        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const table = this.getAttribute('data-table');
                const column = this.getAttribute('data-column');
                document.getElementById('editOptionId').value = id;
                document.getElementById('editOptionName').value = name;
                document.getElementById('editOptionTable').value = table;
                document.getElementById('editOptionColumn').value = column;
                editOptionModal.show();
            });
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const table = this.getAttribute('data-table');
                const column = this.getAttribute('data-column');
                document.getElementById('deleteOptionId').value = id;
                document.getElementById('deleteOptionTable').value = table;
                document.getElementById('deleteOptionColumn').value = column;
                deleteOptionModal.show();
            });
        });

        document.getElementById('addOptionForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('add_option.php', {
                method: 'POST',
                body: formData
            }).then(response => response.text()).then(data => {
                console.log(data); // For debugging
                addOptionModal.hide();
                refreshTable();
            }).catch(error => console.error('Error:', error));
        });

        document.getElementById('editOptionForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('edit_option.php', {
                method: 'POST',
                body: formData
            }).then(response => response.text()).then(data => {
                console.log(data); // For debugging
                editOptionModal.hide();
                refreshTable();
            }).catch(error => console.error('Error:', error));
        });

        document.getElementById('deleteOptionForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('delete_option.php', {
                method: 'POST',
                body: formData
            }).then(response => response.text()).then(data => {
                console.log(data); // For debugging
                deleteOptionModal.hide();
                refreshTable();
            }).catch(error => console.error('Error:', error));
        });
    }

    attachEventListeners();
});

</script>
<script src="./assets/js/script.js"></script>
</body>
</html>
