<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Your Vehicles</title>
    <link rel="stylesheet" href="styles.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    $body_type = $_POST['body_type'];
    $motor_vehicle_file_number = $_POST['motor_vehicle_file_number'];
    $engine_number = $_POST['engine_number'];
    $chassis_number = $_POST['chassis_number'];
    $plate_number = $_POST['plate_number'];
    $vehicle_brand = $_POST['vehicle_brand'];
    $year_release = $_POST['year_release'];
    $vehicle_color = $_POST['vehicle_color'];
    $vehicle_fuel = $_POST['vehicle_fuel'];

    // Insert vehicle information into the vehicle table
    $sql = "INSERT INTO vehicle (body_type, motor_vehicle_file_number, engine_number, chassis_number, plate_number, vehicle_brand, year_release, vehicle_color, vehicle_fuel) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param("ssssssiss", $body_type, $motor_vehicle_file_number, $engine_number, $chassis_number, $plate_number, $vehicle_brand, $year_release, $vehicle_color, $vehicle_fuel);
    $stmt->execute();

    // Get the last inserted vehicle_id
    $vehicle_id = $conn->insert_id;

    // Insert into the user_vehicles table to link the vehicle with the user
    $sql = "INSERT INTO user_vehicles (user_id, vehicle_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param("ii", $user_id, $vehicle_id);
    $stmt->execute();

    $stmt->close();
    $conn->close();

    // Redirect to vehicle.php after successful registration
    header("Location: vehicle.php");
    exit(); // Ensure no further code is executed after the redirection
}

// Fetch all vehicles associated with the user from the user_vehicles table
$sql = "SELECT v.* FROM vehicle v 
        JOIN user_vehicles uv ON v.vehicle_id = uv.vehicle_id 
        WHERE uv.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$vehicles = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

ob_end_flush(); // End output buffering

// Define static options (if not using dynamic options)
// $bodyTypes = ['Sedan', 'SUV', 'Hatchback', 'Coupe', 'Convertible', 'Minivan', 'Pickup Truck'];
// $vehicleBrands = [ 'Toyota', 'Honda', 'Ford', 'Chevrolet', 'BMW', 'Mercedes-Benz', 'Volkswagen', 'Audi', 'Volvo', 'Jaguar', 'Tesla', 'Porsche', 'Lexus', 'Subaru', 'Mazda', 'Hyundai', 'Kia', 'Nissan', 'Mitsubishi', 'Ferrari', 'Lamborghini', 'Maserati', 'Bentley', 'Rolls-Royce', 'Aston Martin', 'Land Rover', 'Peugeot', 'Renault', 'Fiat', 'Alfa Romeo', 'Chrysler', 'Jeep', 'Dodge', 'Ram', 'Cadillac', 'Buick', 'Abarth', 'Isuzu', 'Suzuki', 'Kia', 'Mitsubishi', 'Hyundai', 'Nissan', 'Mazda', 'Suzuki', 'Chevrolet', 'Audi', 'Volvo', 'Jaguar', 'Tesla', 'Porsche', 'Lexus', 'Subaru', 'Mazda', 'Hyundai', 'Kia', 'Nissan', 'Mitsubishi', 'Ferrari', 'Lamborghini', 'Maserati', 'Bentley', 'Rolls-Royce', 'Aston Martin', 'Land Rover', 'Peugeot', 'Renault', 'Fiat', 'Alfa Romeo', 'Chrysler', 'Jeep', 'Dodge', 'Ram', 'Cadillac', 'Buick' ];
// $vehicleColors = [ 'Black-Glossy', 'Black-Matte', 'Black-Metallic', 'Black-Pearlescent', 'Black-Satin', 'White-Glossy', 'White-Matte', 'White-Metallic', 'White-Pearlescent', 'White-Satin', 'Silver-Glossy', 'Silver-Matte', 'Silver-Metallic', 'Silver-Pearlescent', 'Silver-Satin', 'Gray-Glossy', 'Gray-Matte', 'Gray-Metallic', 'Gray-Pearlescent', 'Gray-Satin', 'Red-Glossy', 'Red-Matte', 'Red-Metallic', 'Red-Pearlescent', 'Red-Satin', 'Blue-Glossy', 'Blue-Matte', 'Blue-Metallic', 'Blue-Pearlescent', 'Blue-Satin', 'Yellow-Glossy', 'Yellow-Matte', 'Yellow-Metallic', 'Yellow-Pearlescent', 'Yellow-Satin', 'Green-Glossy', 'Green-Matte', 'Green-Metallic', 'Green-Pearlescent', 'Green-Satin', 'Burgundy-Glossy', 'Burgundy-Matte', 'Burgundy-Metallic', 'Burgundy-Pearlescent', 'Burgundy-Satin', 'Champagne-Glossy', 'Champagne-Matte', 'Champagne-Metallic', 'Champagne-Pearlescent', 'Champagne-Satin', 'Navy-Glossy', 'Navy-Matte', 'Navy-Metallic', 'Navy-Pearlescent', 'Navy-Satin', 'Emerald-Glossy', 'Emerald-Matte', 'Emerald-Metallic', 'Emerald-Pearlescent', 'Emerald-Satin', 'Aqua-Glossy', 'Aqua-Matte', 'Aqua-Metallic', 'Aqua-Pearlescent', 'Aqua-Satin', 'Magenta-Glossy', 'Magenta-Matte', 'Magenta-Metallic', 'Magenta-Pearlescent', 'Magenta-Satin', 'Orange-Glossy', 'Orange-Matte', 'Orange-Metallic', 'Orange-Pearlescent', 'Orange-Satin', 'Pink-Glossy', 'Pink-Matte', 'Pink-Metallic', 'Pink-Pearlescent', 'Pink-Satin', 'Brown-Glossy', 'Brown-Matte', 'Brown-Metallic', 'Brown-Pearlescent', 'Brown-Satin' ];
// $vehicleFuels = ['Gasoline', 'Diesel'];
// $currentYear = date("Y");
// $yearRange = range(1990, $currentYear);
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

    <!-- Button to Add Vehicle -->
    <div class="text-center mt-4">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addVehicleModal">Add Vehicle</button>
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

<!-- Add Vehicle Modal -->
<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title" id="addVehicleModalLabel">Register Your Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addVehicleForm" method="POST" action="add_vehicle.php">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="body_type" class="form-label">Body Type</label>
                            <select class="form-select" id="body_type" name="body_type" required>
                                <option value="" disabled selected>Select Body Type</option>
                                <?php foreach ($bodyTypes as $type): ?>
                                    <option value="<?= htmlspecialchars($type['type_name']) ?>"><?= htmlspecialchars($type['type_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="motor_vehicle_file_number" class="form-label">MV File Number</label>
                            <input type="text" class="form-control" id="motor_vehicle_file_number" name="motor_vehicle_file_number" required>
                        </div>
                        <div class="col-md-4">
                            <label for="engine_number" class="form-label">Engine Number</label>
                            <input type="text" class="form-control" id="engine_number" name="engine_number" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="chassis_number" class="form-label">Chassis Number</label>
                            <input type="text" class="form-control" id="chassis_number" name="chassis_number" required>
                        </div>
                        <div class="col-md-4">
                            <label for="plate_number" class="form-label">Plate Number</label>
                            <input type="text" class="form-control" id="plate_number" name="plate_number" required>
                        </div>
                        <div class="col-md-4">
                            <label for="vehicle_brand" class="form-label">Vehicle Brand</label>
                            <select class="form-select" id="vehicle_brand" name="vehicle_brand" required>
                                <option value="" disabled selected>Select Vehicle Brand</option>
                                <?php foreach ($vehicleBrands as $brand): ?>
                                    <option value="<?= htmlspecialchars($brand['brand_name']) ?>"><?= htmlspecialchars($brand['brand_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="year_release" class="form-label">Year of Release</label>
                            <select class="form-select" id="year_release" name="year_release" required>
                                <option value="" disabled selected>Select Year</option>
                                <?php foreach ($yearRange as $year): ?>
                                    <option value="<?= $year ?>"><?= $year ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="vehicle_color" class="form-label">Vehicle Color</label>
                            <select class="form-select" id="vehicle_color" name="vehicle_color" required>
                                <option value="" disabled selected>Select Color</option>
                                <?php foreach ($vehicleColors as $color): ?>
                                    <option value="<?= htmlspecialchars($color['color_name']) ?>"><?= htmlspecialchars($color['color_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="vehicle_fuel" class="form-label">Vehicle Fuel Type</label>
                            <select class="form-select" id="vehicle_fuel" name="vehicle_fuel" required>
                                <option value="" disabled selected>Select Fuel Type</option>
                                <?php foreach ($vehicleFuels as $fuel): ?>
                                    <option value="<?= htmlspecialchars($fuel['fuel_name']) ?>"><?= htmlspecialchars($fuel['fuel_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
