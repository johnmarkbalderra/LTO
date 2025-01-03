<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Your Vehicles</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php
ob_start(); // Start output buffering

include 'navbar.php';
include '../php/setting.php';
require_once "dbconnect.php";
$user_id = $_SESSION['user_id'];

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

// Retrieve options in alphabetical order
$bodyTypes = fetchOptions($conn, 'body_types', 'type_name');  
$vehicleBrands = fetchOptions($conn, 'vehicle_brands', 'brand_name');  
$vehicleColors = fetchOptions($conn, 'vehicle_colors', 'color_name');  
$vehicleFuels = fetchOptions($conn, 'vehicle_fuels', 'fuel_name');  

$currentYear = date("Y");
$yearRange = range(1990, $currentYear);


// Handle form submission for adding a new vehicle
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve POST data
    $body_type = $_POST['body_type'] ?? '';
    $motor_vehicle_file_number = $_POST['motor_vehicle_file_number'] ?? '';
    $engine_number = $_POST['engine_number'] ?? '';
    $chassis_number = $_POST['chassis_number'] ?? '';
    $plate_number = $_POST['plate_number'] ?? '';
    $vehicle_brand = $_POST['vehicle_brand'] ?? '';
    $year_release = $_POST['year_release'] ?? '';
    $vehicle_color = $_POST['vehicle_color'] ?? '';
    $vehicle_fuel = $_POST['vehicle_fuel'] ?? '';

    // Handle file upload
    if (!empty($_FILES['vehicle_image']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["vehicle_image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image
        $check = getimagesize($_FILES["vehicle_image"]["tmp_name"]);
        if ($check === false) {
            die("File is not an image.");
        }

        // Check file size (500KB limit)
        if ($_FILES["vehicle_image"]["size"] > 500000) {
            die("File is too large.");
        }

        // Allow specific file formats
        $allowedFormats = ["jpg", "png", "jpeg", "gif"];
        if (!in_array($imageFileType, $allowedFormats)) {
            die("Only JPG, JPEG, PNG & GIF files are allowed.");
        }

        // Upload the file
        if (!move_uploaded_file($_FILES["vehicle_image"]["tmp_name"], $target_file)) {
            die("Error uploading the file.");
        }
    } else {
        die("No file selected.");
    }

    // Insert into the database
    $sql = "INSERT INTO vehicle (body_type, motor_vehicle_file_number, engine_number, chassis_number, plate_number, vehicle_brand, year_release, vehicle_color, vehicle_fuel, vehicle_image, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }

    // Set default status to "Pending"
    $status = 'Pending';

    $stmt->bind_param("ssssssissss", $body_type, $motor_vehicle_file_number, $engine_number, $chassis_number, $plate_number, $vehicle_brand, $year_release, $vehicle_color, $vehicle_fuel, $target_file, $status);
    if ($stmt->execute()) {
        $vehicle_id = $conn->insert_id;

        // Link the vehicle to the user
        $linkSql = "INSERT INTO user_vehicles (user_id, vehicle_id) VALUES (?, ?)";
        $linkStmt = $conn->prepare($linkSql);
        if ($linkStmt === false) {
            die('Prepare failed: ' . $conn->error);
        }
        $linkStmt->bind_param("ii", $user_id, $vehicle_id);
        $linkStmt->execute();
        $linkStmt->close();

        // Redirect after success
        header("Location: vehicle.php");
        exit();
    } else {
        die("Error inserting data: " . $stmt->error);
    }
}


// Fetch all vehicles associated with the user from the user_vehicles table
$sql = "SELECT v.*, v.status FROM vehicle v 
        JOIN user_vehicles uv ON v.vehicle_id = uv.vehicle_id 
        WHERE uv.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$vehicles = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

ob_end_flush();
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Your Registered Vehicles</h2>

    <?php if (empty($vehicles)): ?>
        <div class='alert alert-danger'>No vehicles found.</div>
    <?php else: ?>
        <?php foreach ($vehicles as $vehicle): ?>
            <div class="card shadow p-4 mb-4">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Body Type:</strong>
                        <p><?= htmlspecialchars($vehicle['body_type']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>MV File Number:</strong>
                        <p><?= htmlspecialchars($vehicle['motor_vehicle_file_number']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Engine Number:</strong>
                        <p><?= htmlspecialchars($vehicle['engine_number']) ?></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Chassis Number:</strong>
                        <p><?= htmlspecialchars($vehicle['chassis_number']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Plate Number:</strong>
                        <p><?= htmlspecialchars($vehicle['plate_number']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Vehicle Brand:</strong>
                        <p><?= htmlspecialchars($vehicle['vehicle_brand']) ?></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Year of Release:</strong>
                        <p><?= htmlspecialchars($vehicle['year_release']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Vehicle Color:</strong>
                        <p><?= htmlspecialchars($vehicle['vehicle_color']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <strong>Vehicle Fuel Type:</strong>
                        <p><?= htmlspecialchars($vehicle['vehicle_fuel']) ?></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Status:</strong>
                        <p class="status <?= strtolower($vehicle['status']) ?>"><?= htmlspecialchars($vehicle['status']) ?></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>Vehicle OR/CR:</strong>
                        <img src="<?= htmlspecialchars($vehicle['vehicle_image']) ?>" alt='Vehicle Image' width='150' data-bs-toggle="modal" data-bs-target="#vehicleImageModal" style="cursor: pointer;">
                    </div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-primary edit-vehicle-btn" data-bs-toggle="modal" data-bs-target="#editVehicleModal" 
                            data-vehicle-id="<?= $vehicle['vehicle_id'] ?>"
                            data-body-type="<?= htmlspecialchars($vehicle['body_type']) ?>"
                            data-motor-vehicle-file-number="<?= htmlspecialchars($vehicle['motor_vehicle_file_number']) ?>"
                            data-engine-number="<?= htmlspecialchars($vehicle['engine_number']) ?>"
                            data-chassis-number="<?= htmlspecialchars($vehicle['chassis_number']) ?>"
                            data-plate-number="<?= htmlspecialchars($vehicle['plate_number']) ?>"
                            data-vehicle-brand="<?= htmlspecialchars($vehicle['vehicle_brand']) ?>"
                            data-year-release="<?= htmlspecialchars($vehicle['year_release']) ?>"
                            data-vehicle-color="<?= htmlspecialchars($vehicle['vehicle_color']) ?>"
                            data-vehicle-fuel="<?= htmlspecialchars($vehicle['vehicle_fuel']) ?>">
                        Vehicle Details Changes
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="mt-4 text-center">
        <a href="vehicle_reg.php" class="btn btn-info text-light w-100" style="border-radius: 10px; background-color: #007aff; padding: 10px; font-size: 16px; transition: background-color 0.3s ease-in-out; text-align: center;">Add Vehicle</a>
    </div>
</div> 

<!-- Modal to Display Vehicle Image in Full Size -->
<div class="modal fade" id="vehicleImageModal" tabindex="-1" aria-labelledby="vehicleImageModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vehicleImageModalLabel">Vehicle OR/CR</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="<?= htmlspecialchars($vehicle['vehicle_image']) ?>" alt='Vehicle Image' class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- add vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addVehicleModalLabel">Register Your Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addVehicleForm" method="POST" action="add_vehicle.php" enctype="multipart/form-data">
                    <!-- Body Type & Vehicle Brand -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="body_type" class="form-label" style="font-weight: 500; color: #333;">Body Type</label>
                            <select class="form-select" id="body_type" name="body_type" required style="border-radius: 15px; padding: 12px 16px; background-color: #f4f4f4; font-size: 16px; color: #333; border: 1px solid #ccc;">
                                <option value="" disabled selected>Select Body Type</option>
                                <?php foreach ($bodyTypes as $type): ?>
                                    <option value="<?= htmlspecialchars($type['type_name']) ?>"><?= htmlspecialchars($type['type_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="vehicle_brand" class="form-label" style="font-weight: 500; color: #333;">Vehicle Brand</label>
                            <select class="form-select" id="vehicle_brand" name="vehicle_brand" required style="border-radius: 15px; padding: 12px 16px; background-color: #f4f4f4; font-size: 16px; color: #333; border: 1px solid #ccc;">
                                <option value="" disabled selected>Select Vehicle Brand</option>
                                <?php foreach ($vehicleBrands as $brand): ?>
                                    <option value="<?= htmlspecialchars($brand['brand_name']) ?>"><?= htmlspecialchars($brand['brand_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- MV File Number & Engine Number -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="motor_vehicle_file_number" class="form-label" style="font-weight: 500; color: #333;">MV File Number</label>
                            <input type="text" class="form-control" id="motor_vehicle_file_number" name="motor_vehicle_file_number" required style="border-radius: 15px; padding: 12px 16px; background-color: #f4f4f4; font-size: 16px; color: #333; border: 1px solid #ccc;">
                        </div>
                        <div class="col-md-6">
                            <label for="engine_number" class="form-label" style="font-weight: 500; color: #333;">Engine Number</label>
                            <input type="text" class="form-control" id="engine_number" name="engine_number" required style="border-radius: 15px; padding: 12px 16px; background-color: #f4f4f4; font-size: 16px; color: #333; border: 1px solid #ccc;">
                        </div>
                    </div>

                    <!-- Chassis Number & Plate Number -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="chassis_number" class="form-label" style="font-weight: 500; color: #333;">Chassis Number</label>
                            <input type="text" class="form-control" id="chassis_number" name="chassis_number" required style="border-radius: 15px; padding: 12px 16px; background-color: #f4f4f4; font-size: 16px; color: #333; border: 1px solid #ccc;">
                        </div>
                        <div class="col-md-6">
                            <label for="plate_number" class="form-label" style="font-weight: 500; color: #333;">Plate Number</label>
                            <input type="text" class="form-control" id="plate_number" name="plate_number" required style="border-radius: 15px; padding: 12px 16px; background-color: #f4f4f4; font-size: 16px; color: #333; border: 1px solid #ccc;">
                        </div>
                    </div>

                    <!-- Year of Release & Vehicle Color -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="year_release" class="form-label" style="font-weight: 500; color: #333;">Year of Release</label>
                            <select class="form-select" id="year_release" name="year_release" required style="border-radius: 15px; padding: 12px 16px; background-color: #f4f4f4; font-size: 16px; color: #333; border: 1px solid #ccc;">
                                <option value="" disabled selected>Select Year</option>
                                <?php foreach ($yearRange as $year): ?>
                                    <option value="<?= $year ?>"><?= $year ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="vehicle_color" class="form-label" style="font-weight: 500; color: #333;">Vehicle Color</label>
                            <select class="form-select" id="vehicle_color" name="vehicle_color" required style="border-radius: 15px; padding: 12px 16px; background-color: #f4f4f4; font-size: 16px; color: #333; border: 1px solid #ccc;">
                                <option value="" disabled selected>Select Color</option>
                                <?php foreach ($vehicleColors as $color): ?>
                                    <option value="<?= htmlspecialchars($color['color_name']) ?>"><?= htmlspecialchars($color['color_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Fuel Type & Vehicle Image -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="vehicle_fuel" class="form-label" style="font-weight: 500; color: #333;">Fuel Type</label>
                            <select class="form-select" id="vehicle_fuel" name="vehicle_fuel" required style="border-radius: 15px; padding: 12px 16px; background-color: #f4f4f4; font-size: 16px; color: #333; border: 1px solid #ccc;">
                                <option value="" disabled selected>Select Fuel Type</option>
                                <?php foreach ($vehicleFuels as $fuel): ?>
                                    <option value="<?= htmlspecialchars($fuel['fuel_name']) ?>"><?= htmlspecialchars($fuel['fuel_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
        <label for="vehicle_image" class="form-label" style="font-weight: 500; color: #333;">Upload OR/CR</label>
        <input type="file" class="form-control" id="vehicle_image" name="vehicle_image" accept="image/*" required style="border-radius: 15px; padding: 12px 16px; background-color: #f4f4f4; font-size: 16px; color: #333; border: 1px solid #ccc;">
    </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center mt-5">
                        <button type="submit" class="btn btn-primary btn-lg w-100" style="border-radius: 15px; background-color: #007aff; border: none; padding: 16px; font-size: 18px; transition: background-color 0.3s ease-in-out;">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Edit Vehicle Modal -->
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-labelledby="editVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVehicleModalLabel">Edit Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editVehicleForm" method="POST" action="edit_vehicle.php">
                    <input type="hidden" name="vehicle_id" id="edit_vehicle_id">
                    <div class="mb-3">
                        <label for="edit_body_type" class="form-label">Body Type</label>
                        <select class="form-select" id="edit_body_type" name="body_type" required>
                            <option value="" disabled selected>Select Body Type</option>
                            <?php foreach ($bodyTypes as $type): ?>
                                <option value="<?= htmlspecialchars($type['type_name']) ?>"><?= htmlspecialchars($type['type_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_motor_vehicle_file_number" class="form-label">MV File Number</label>
                        <input type="text" class="form-control" id="edit_motor_vehicle_file_number" name="motor_vehicle_file_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_engine_number" class="form-label">Engine Number</label>
                        <input type="text" class="form-control" id="edit_engine_number" name="engine_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_chassis_number" class="form-label">Chassis Number</label>
                        <input type="text" class="form-control" id="edit_chassis_number" name="chassis_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_plate_number" class="form-label">Plate Number</label>
                        <input type="text" class="form-control" id="edit_plate_number" name="plate_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_vehicle_brand" class="form-label">Vehicle Brand</label>
                        <select class="form-select" id="edit_vehicle_brand" name="vehicle_brand" required>
                            <option value="" disabled selected>Select Vehicle Brand</option>
                            <?php foreach ($vehicleBrands as $brand): ?>
                                <option value="<?= htmlspecialchars($brand['brand_name']) ?>"><?= htmlspecialchars($brand['brand_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_year_release" class="form-label">Year of Release</label>
                        <select class="form-select" id="edit_year_release" name="year_release" required>
                            <option value="" disabled selected>Select Year</option>
                            <?php foreach ($yearRange as $year): ?>
                                <option value="<?= $year ?>"><?= $year ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_vehicle_color" class="form-label">Vehicle Color</label>
                        <select class="form-select" id="edit_vehicle_color" name="vehicle_color" required>
                            <option value="" disabled selected>Select Vehicle Color</option>
                            <?php foreach ($vehicleColors as $color): ?>
                                <option value="<?= htmlspecialchars($color['color_name']) ?>"><?= htmlspecialchars($color['color_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_vehicle_fuel" class="form-label">Vehicle Fuel Type</label>
                        <select class="form-select" id="edit_vehicle_fuel" name="vehicle_fuel" required>
                            <option value="" disabled selected>Select Fuel Type</option>
                            <?php foreach ($vehicleFuels as $fuel): ?>
                                <option value="<?= htmlspecialchars($fuel['fuel_name']) ?>"><?= htmlspecialchars($fuel['fuel_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('addVehicleForm').addEventListener('submit', function (e) {
    const fileInput = document.getElementById('vehicle_image');
    if (!fileInput.value) {
        e.preventDefault();
        alert('Please select an image file to upload.');
    }
});

document.addEventListener('DOMContentLoaded', (event) => {
    var editVehicleModal = document.getElementById('editVehicleModal');
    editVehicleModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;

        // Retrieve data from the button's data attributes
        var vehicle_id = button.getAttribute('data-vehicle-id');
        var body_type = button.getAttribute('data-body-type');
        var motor_vehicle_file_number = button.getAttribute('data-motor-vehicle-file-number');
        var engine_number = button.getAttribute('data-engine-number');
        var chassis_number = button.getAttribute('data-chassis-number');
        var plate_number = button.getAttribute('data-plate-number');
        var vehicle_brand = button.getAttribute('data-vehicle-brand');
        var year_release = button.getAttribute('data-year-release');
        var vehicle_color = button.getAttribute('data-vehicle-color');
        var vehicle_fuel = button.getAttribute('data-vehicle-fuel');

        // Update the form fields with the vehicle data
        document.getElementById('edit_vehicle_id').value = vehicle_id;
        document.getElementById('edit_body_type').value = body_type;
        document.getElementById('edit_motor_vehicle_file_number').value = motor_vehicle_file_number;
        document.getElementById('edit_engine_number').value = engine_number;
        document.getElementById('edit_chassis_number').value = chassis_number;
        document.getElementById('edit_plate_number').value = plate_number;
        document.getElementById('edit_vehicle_brand').value = vehicle_brand;
        document.getElementById('edit_year_release').value = year_release;
        document.getElementById('edit_vehicle_color').value = vehicle_color;
        document.getElementById('edit_vehicle_fuel').value = vehicle_fuel;
    });
});

</script>


<script>
    // Prepopulate the edit modal with existing vehicle data
    $('#editVehicleModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var vehicle = {
            vehicle_id: button.data('vehicle-id'),
            body_type: button.data('body-type'),
            motor_vehicle_file_number: button.data('motor-vehicle-file-number'),
            engine_number: button.data('engine-number'),
            chassis_number: button.data('chassis-number'),
            plate_number: button.data('plate-number'),
            vehicle_brand: button.data('vehicle-brand'),
            year_release: button.data('year-release'),
            vehicle_color: button.data('vehicle-color'),
            vehicle_fuel: button.data('vehicle-fuel')
        };

        $('#edit_vehicle_id').val(vehicle.vehicle_id);
        $('#edit_body_type').val(vehicle.body_type);
        $('#edit_motor_vehicle_file_number').val(vehicle.motor_vehicle_file_number);
        $('#edit_engine_number').val(vehicle.engine_number);
        $('#edit_chassis_number').val(vehicle.chassis_number);
        $('#edit_plate_number').val(vehicle.plate_number);
        $('#edit_vehicle_brand').val(vehicle.vehicle_brand);
        $('#edit_year_release').val(vehicle.year_release);
        $('#edit_vehicle_color').val(vehicle.vehicle_color);
        $('#edit_vehicle_fuel').val(vehicle.vehicle_fuel);
    });
</script>
</body>
</html>
