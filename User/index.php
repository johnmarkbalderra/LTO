<?php

// Include database connection first
require_once "dbconnect.php"; 

// Include other necessary files
include 'navbar.php';
include '../php/setting.php'; 

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details from the database
$user_query = $conn->prepare("SELECT first_name, middle_name, last_name, email, telephone FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user_data = $user_result->fetch_assoc();

// Ensure the user's information is fetched properly
if ($user_data) {
    $full_name = trim($user_data['first_name'] . ' ' . $user_data['middle_name'] . ' ' . $user_data['last_name']);
    $email = $user_data['email'];
    $phone_number = $user_data['telephone'];
} else {
    // Fallbacks in case user data is missing
    $full_name = '';
    $email = '';
    $phone_number = '';
}

$user_query->close();

// Check if the user has registered vehicles
$vehicle_query = $conn->prepare("SELECT vehicle_id FROM user_vehicles WHERE user_id = ?");
$vehicle_query->bind_param("i", $user_id);
$vehicle_query->execute();
$vehicle_result = $vehicle_query->get_result();
$has_vehicle = $vehicle_result->num_rows > 0;
$vehicle_query->close();

// Fetch user's vehicles
$vehicles = [];
if ($has_vehicle) {
    $vehicles_query = $conn->prepare("
        SELECT v.vehicle_id, v.body_type, v.plate_number 
        FROM vehicle v
        INNER JOIN user_vehicles uv ON v.vehicle_id = uv.vehicle_id
        WHERE uv.user_id = ?
    ");
    $vehicles_query->bind_param("i", $user_id);
    $vehicles_query->execute();
    $vehicles_result = $vehicles_query->get_result();
    while ($row = $vehicles_result->fetch_assoc()) {
        $vehicles[] = $row;
    }
    $vehicles_query->close();
}

// Fetch user's latest appointment details, including waitlist status
$appointment_query = $conn->prepare("
    SELECT *, IF(waitlist = 1, 'Wait Listed', status) AS display_status 
    FROM schedule_list 
    WHERE user_id = ? AND status IN ('pending', 'approved','Canceled') 
    ORDER BY start_datetime DESC 
    LIMIT 1
");
$appointment_query->bind_param("i", $user_id);
$appointment_query->execute();
$appointment_result = $appointment_query->get_result();
$appointment = $appointment_result->fetch_assoc();

// Construct QR Code file path if appointment is approved
$qrFilePath = null;
if ($appointment && $appointment['status'] == 'approved') {
    // Assuming QR codes are stored in '../qr_codes/' directory with filename 'appointment_[schedule_id].png'
    $qrFilePath = '../qr_codes/appointment_' . $appointment['schedule_id'] . '.png';
}

$appointment_query->close();




// Fetch all appointments for the logged-in user
$appointments_query = $conn->prepare("SELECT * FROM schedule_list WHERE user_id = ? ORDER BY start_datetime DESC");
$appointments_query->bind_param("i", $user_id);
$appointments_query->execute();
$appointments_result = $appointments_query->get_result();
$appointments = [];
while ($row = $appointments_result->fetch_assoc()) {
    // Update status to 'Wait Listed' if waitlist is 1
    if ($row['waitlist'] == 1) {
        $row['status'] = 'Wait Listed';
    }
    $appointments[] = $row;
}
$appointments_query->close();

// Fetch all active appointments to determine if the user can have more appointments
$active_appointment_query = $conn->prepare("
    SELECT COUNT(DISTINCT vehicle_type) as appointment_count FROM schedule_list 
    WHERE user_id = ? AND status IN ('pending', 'approved','Canceled')");
$active_appointment_query->bind_param("i", $user_id);
$active_appointment_query->execute();
$active_appointment_result = $active_appointment_query->get_result();
$active_appointment_data = $active_appointment_result->fetch_assoc();
$active_appointments = $active_appointment_data['appointment_count'];
$active_appointment_query->close();




// Determine if the user can schedule another appointment
// Assuming one appointment per vehicle
$can_schedule_another = false;
if ($has_vehicle && count($vehicles) > $active_appointments) {
    $can_schedule_another = true;
}

// Debugging Statements (Remove or comment out in production)
error_log("Total Vehicles: " . count($vehicles));
error_log("Active Appointments (Distinct Vehicles): " . $active_appointments);
error_log("Can Schedule Another: " . ($can_schedule_another ? 'Yes' : 'No'));

// Close the database connection at the end (after all operations)
// Fetch user's vehicles and check their statuses
$vehicles_query = $conn->prepare("
    SELECT v.vehicle_id, v.body_type, v.plate_number, v.status
    FROM vehicle v
    INNER JOIN user_vehicles uv ON v.vehicle_id = uv.vehicle_id
    WHERE uv.user_id = ?
");
$vehicles_query->bind_param("i", $user_id);
$vehicles_query->execute();
$vehicles_result = $vehicles_query->get_result();

$vehicles = [];
$has_disapproved_vehicle = false;

while ($row = $vehicles_result->fetch_assoc()) {
    $vehicles[] = $row;
    if (strtolower($row['status']) === 'disapproved') {
        $has_disapproved_vehicle = true;
    }
}
$vehicles_query->close();
?>

<!doctype html>
<html lang="en">

<style>
    
</style>

<body>
    
<div class="container">
    <div class="row align-items-center">
        <div class="col-md-6 text-center">
            <h1 class="m-5">DUMA LTO Appointment System</h1>
            <img class="m-2 rounded" src="images/Land_Transportation_Office.svg.png" style="height: 200px;" alt="Land Transportation Office">
            <h2 class="m-5">Easy-Access for All</h2>
        </div>
        <div class="col-md-6 text-center">
            <?php if ($has_disapproved_vehicle): ?>
                <!-- Show message if the user has a disapproved vehicle -->
                <p class="text-danger"><i class="fas fa-exclamation-circle me-2"></i> Wait for the vehicle approval.</p>
            <?php elseif ($appointment && $appointment['status'] == 'pending'): ?>
                <!-- Show message if the user has a pending appointment -->
                <p class="text-warning"><i class="fas fa-hourglass-half me-2"></i> You have a pending appointment. Please wait for approval.</p>
                <button class="btn btn-info btn-custom" role="button" data-bs-toggle="modal" data-bs-target="#event-details-modal"><i class="fas fa-calendar-alt me-2"></i> My Appointment</button>
            <?php elseif ($appointment && $appointment['status'] == 'approved'): ?>
                <!-- Show My Appointment and Download QR Code buttons if the user has an approved appointment -->
                <button class="btn btn-info btn-custom" role="button" data-bs-toggle="modal" data-bs-target="#event-details-modal"><i class="fas fa-calendar-check me-2"></i> My Appointment</button>
                <br><br>
        <!-- <?php if ($qrFilePath && file_exists($qrFilePath)): ?>
            <a href="<?= htmlspecialchars($qrFilePath); ?>" download="my_schedule_qr.png" class="btn btn-primary btn-custom">Download My QR</a>
        <?php else: ?> -->
            <!-- <p class="text-danger">QR Code not available yet. Please wait for admin approval.</p> -->
        <?php endif; ?>

        <!-- Add "Get Another Appointment" button if the user has more vehicles than active appointments -->
        <?php if ($can_schedule_another): ?>
            <br><br>
            <button class="btn btn-success btn-custom" role="button" data-bs-toggle="modal" data-bs-target="#schedule-appointment-modal">Another Appointment</button>
        <?php endif; ?>
            <?php elseif (!$has_vehicle): ?>
                <!-- Show message if the user does not have any registered vehicle -->
                <p class="text-danger"><i class="fas fa-car-crash me-2"></i> You must register at least one vehicle before setting an appointment.</p>
                <a href="vehicle_reg.php" class="btn btn-primary btn-custom"><i class="fas fa-car me-2"></i> Register a Vehicle</a>
            <?php else: ?>
                <!-- Show Get an Appointment button if the user has 1 or more vehicles and no pending/approved appointments -->
                <button class="btn btn-success btn-custom" role="button" data-bs-toggle="modal" data-bs-target="#schedule-appointment-modal"><i class="fas fa-calendar-plus me-2"></i> Get an Appointment</button>
            <?php endif; ?>
        </div>















    </div>
</div>

<!-- Event Details Modal for My Appointment -->
<?php if ($appointment): ?>
        <<!-- Event Details Modal for My Appointment -->
<div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="event-details-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-1 shadow">
            <div class="modal-header bg-primary text-light rounded-1">
                <h5 class="modal-title"><i class="fas fa-calendar-alt me-2"></i> All Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body rounded-0">
                <div class="container-fluid">
                    <?php if (count($appointments) > 0): ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <dl>
                                        <!-- <dt class="text-muted">Appointment ID</dt>
                                        <dd class="fw-bold fs-5"><?= htmlspecialchars($appointment['schedule_id']); ?></dd> -->
                                        <dt class="text-muted text-b"><i class="fas fa-calendar-day me-2"></i> Date</dt>
                                        <dd><?= date('F j, Y, g:i a', strtotime($appointment['start_datetime'])); ?></dd>
                                        <dt class="text-muted"><i class="fas fa-car me-2"></i> Vehicle Type</dt>
                                        <dd><?= htmlspecialchars($appointment['vehicle_type']); ?></dd>
                                        <dt class="text-muted"><i class="fas fa-info-circle me-2"></i> Status</dt>
                                        <dd class="<?= $appointment['status'] == 'approved' ? 'text-success' : ($appointment['status'] == 'rejected' ? 'text-danger' : 'text-warning'); ?>">
                                            <?= htmlspecialchars(ucfirst($appointment['status'])); ?>
                                        </dd>
                                    </dl>
                                    <?php if ($appointment['status'] == 'approved'): ?>
                                        <?php 
                                            // Construct QR Code file path if appointment is approved
                                            $qrFilePath = '../qr_codes/appointment_' . $appointment['schedule_id'] . '.png';
                                        ?>
                                        <a href="<?= htmlspecialchars($qrFilePath); ?>" download="my_schedule_qr.png" class="btn btn-primary"><i class="fas fa-download me-2"></i> Download QR</a>
                                        <button type="button" class="btn btn-warning rounded-1 reschedule-button" data-schedule-id="<?= htmlspecialchars($appointment['schedule_id']); ?>" data-start-datetime="<?= htmlspecialchars($appointment['start_datetime']); ?>" data-vehicle-type="<?= htmlspecialchars($appointment['vehicle_type']); ?>" data-bs-toggle="modal" data-bs-target="#reschedule-appointment-modal"><i class="fas fa-calendar-alt me-2"></i> Reschedule</button>
                                        <button type="button" class="btn btn-danger rounded-1" data-bs-toggle="modal" data-bs-target="#cancel-appointment-modal"><i class="fas fa-times me-2"></i> Cancel</button>
                                    <?php elseif ($appointment['status'] == 'waitlist'): ?>
                                        <button type="button" class="btn btn-warning rounded-1 reschedule-button" data-schedule-id="<?= htmlspecialchars($appointment['schedule_id']); ?>" data-start-datetime="<?= htmlspecialchars($appointment['start_datetime']); ?>" data-vehicle-type="<?= htmlspecialchars($appointment['vehicle_type']); ?>" data-bs-toggle="modal" data-bs-target="#reschedule-appointment-modal"><i class="fas fa-calendar-alt me-2"></i> Reschedule</button>
                                        <button type="button" class="btn btn-danger rounded-1" data-bs-toggle="modal" data-bs-target="#cancel-appointment-modal"><i class="fas fa-times me-2"></i> Cancel</button>
                                    <?php elseif ($appointment['status'] != 'rejected'): ?>
                                        <!-- <button type="button" class="btn btn-warning rounded-1 reschedule-button" data-schedule-id="<?= htmlspecialchars($appointment['schedule_id']); ?>" data-start-datetime="<?= htmlspecialchars($appointment['start_datetime']); ?>" data-vehicle-type="<?= htmlspecialchars($appointment['vehicle_type']); ?>" data-bs-toggle="modal" data-bs-target="#reschedule-appointment-modal"><i class="fas fa-calendar-alt me-2"></i> Re-Schedule</button>
                                        <button type="button" class="btn btn-danger rounded-1" data-bs-toggle="modal" data-bs-target="#cancel-appointment-modal"><i class="fas fa-times me-2"></i> Cancel</button> -->
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted"><i class="fas fa-info-circle me-2"></i> No appointments found.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer rounded-0">
                <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Re-Schedule Appointment Modal -->
<div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="reschedule-appointment-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-1 shadow">
            <div class="modal-header bg-warning text-dark rounded-1">
                <h5 class="modal-title" id="reschedule-appointment-modal-label"><i class="fas fa-calendar-alt me-2"></i> Re-Schedule Your Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reschedule-form" action="reschedule_appointment.php" method="POST">
                    <input type="hidden" name="schedule_id" id="reschedule_schedule_id">

                    <!-- User Information Section -->
                    <div class="row mb-3">
                        <div class="col">
                            <label for="full_name_reschedule" class="form-label"><i class="fas fa-user me-2"></i> Full Name</label>
                            <input type="text" class="form-control rounded-0" name="full_name" id="full_name_reschedule" value="<?= htmlspecialchars($full_name); ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="phone_number_reschedule" class="form-label"><i class="fas fa-phone me-2"></i> Phone Number</label>
                            <input type="text" class="form-control rounded-0" name="phone_number" id="phone_number_reschedule" value="<?= htmlspecialchars($phone_number); ?>" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="email_reschedule" class="form-label"><i class="fas fa-envelope me-2"></i> Email</label>
                            <input type="email" class="form-control rounded-0" name="email" id="email_reschedule" value="<?= htmlspecialchars($email); ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="type_of_vehicle_reschedule" class="form-label"><i class="fas fa-car me-2"></i> Select your Vehicle</label>
                            <select class="form-select rounded-0" name="type_of_vehicle" id="type_of_vehicle_reschedule" required>
                                <option value=""><i class="fas fa-car me-2"></i> Select Vehicle</option>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <option value="<?= htmlspecialchars($vehicle['vehicle_id']); ?>">
                                        <?= htmlspecialchars($vehicle['body_type']); ?> - PN: <?= htmlspecialchars($vehicle['plate_number']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Reschedule Date and Time Section -->
                    <div class="row mb-3">
                        <div class="col">
                            <label for="reschedule_date" class="form-label"><i class="fas fa-calendar-day me-2"></i> Select Date</label>
                            <input type="text" class="form-control rounded-0" name="reschedule_date" id="reschedule_date" required>
                        </div>
                        <div class="col">
                            <label for="reschedule_time" class="form-label"><i class="fas fa-clock me-2"></i> Select Time</label>
                            <select class="form-select rounded-0" name="reschedule_time" id="reschedule_time" required>
                                <option value=""><i class="fas fa-clock me-2"></i> Select Time</option>
                                <!-- Time options will be dynamically populated via JavaScript -->
                            </select>
                        </div>
                    </div>
                
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Cancel Appointment Modal -->
<div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="cancel-appointment-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-1 shadow">
            <div class="modal-header bg-danger text-light rounded-1">
                <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i> Cancel Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body rounded-0">
                <p><i class="fas fa-exclamation-triangle me-2"></i> Are you sure you want to cancel your appointment?</p>
            </div>
            <div class="modal-footer rounded-0">
                <form id="cancel-form" action="cancel_appointment.php" method="POST">
                    <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($appointment['schedule_id']); ?>">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-check me-2"></i> Yes, Cancel Appointment</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> No, Keep Appointment</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Schedule Appointment Modal -->
<div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="schedule-appointment-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-1 shadow">
            <div class="modal-header bg-primary text-light rounded-1">
                <h3 class="modal-title mb-0"><i class="fas fa-calendar-plus me-2"></i> Schedule Your Appointment</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="save_schedule.php" method="post" id="schedule-form">
                    <input type="hidden" name="id" value="">
                    
                    <!-- User Information Section -->
                    <div class="row mb-3">
                        <div class="col">
                            <label for="full_name_schedule" class="form-label"><i class="fas fa-user me-2"></i> Full Name</label>
                            <input type="text" class="form-control rounded-0" name="full_name" id="full_name_schedule" value="<?= htmlspecialchars($full_name); ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="phone_number_schedule" class="form-label"><i class="fas fa-phone me-2"></i> Phone Number</label>
                            <input type="text" class="form-control rounded-0" name="phone_number" id="phone_number_schedule" value="<?= htmlspecialchars($phone_number); ?>" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="email_schedule" class="form-label"><i class="fas fa-envelope me-2"></i> Email</label>
                            <input type="email" class="form-control rounded-0" name="email" id="email_schedule" value="<?= htmlspecialchars($email); ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="type_of_vehicle_schedule" class="form-label"><i class="fas fa-car me-2"></i> Select your Vehicle</label>
                            <select class="form-select rounded-0" name="type_of_vehicle" id="type_of_vehicle_schedule" required>
                                <option value=""><i class="fas fa-car me-2"></i> Select Vehicle</option>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <option value="<?= htmlspecialchars($vehicle['vehicle_id']); ?>">
                                        <?= htmlspecialchars($vehicle['body_type']); ?> - PN: <?= htmlspecialchars($vehicle['plate_number']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Appointment Date and Time Section -->
                    <div class="row mb-3">
                        <div class="col">
                            <label for="appointment_date" class="form-label"><i class="fas fa-calendar-day me-2"></i> Select Date</label>
                            <input type="text" class="form-control rounded-0" name="appointment_date" id="appointment_date" required>
                        </div>
                        <div class="col">
                            <label for="appointment_time" class="form-label"><i class="fas fa-clock me-2"></i> Select Time</label>
                            <select class="form-select rounded-0" name="appointment_time" id="appointment_time" required>
                                <option value=""><i class="fas fa-clock me-2"></i> Select Time</option>
                                <!-- Time options will be dynamically populated via JavaScript -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button class="btn btn-primary rounded-0" type="submit"><i class="fa fa-save me-2"></i> Save</button>
                        <button class="btn btn-secondary rounded-0" type="button" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
<?php
// Close the database connection after all operations
$conn->close();
?>