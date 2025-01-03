<?php
require_once "navbar.php";
require_once "db-connect.php"; // Include database connection file

$table_name = "vehicle";
$user_table = "users";
$relation_table = "user_vehicles";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize a variable to store the update message
$updateMessage = '';

// Update status if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vehicle_id = $_POST['vehicle_id'];
    $status = $_POST['status'];
    $sql = "UPDATE $table_name SET status='$status' WHERE vehicle_id=$vehicle_id";

    if ($conn->query($sql) === TRUE) {
        $updateMessage = "Record updated successfully";
    } else {
        $updateMessage = "Error updating record: " . $conn->error;
    }
}

// Retrieve vehicle data along with user details
$sql = "SELECT v.*, u.first_name, u.last_name, u.email 
        FROM $table_name v 
        JOIN $relation_table uv ON v.vehicle_id = uv.vehicle_id
        JOIN $user_table u ON uv.user_id = u.user_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vehicle List</title>
</head>
<body>
<div class="container-fluid py-5" id="page-container">
<div class="row justify-content-center">
    <div class="col-md-11 text-dark rounded-3 shadow-lg bg-white p-4">
        <h1>Vehicle List</h1>
        
        <?php if ($updateMessage !== ''): ?>
            <div class="alert alert-info"><?= $updateMessage ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="bg-secondary text-white">
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Body Type</th>
                        <th>Motor Vehicle File Number</th>
                        <th>Engine Number</th>
                        <th>Chassis Number</th>
                        <th>Plate Number</th>
                        <th>Vehicle Brand</th>
                        <th>Year Release</th>
                        <th>Vehicle Color</th>
                        <th>Vehicle Fuel</th>
                        <th>Status</th>
                        <th>OR/CR</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['first_name'] . "</td>";
                            echo "<td>" . $row['last_name'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                            echo "<td>" . $row['body_type'] . "</td>";
                            echo "<td>" . $row['motor_vehicle_file_number'] . "</td>";
                            echo "<td>" . $row['engine_number'] . "</td>";
                            echo "<td>" . $row['chassis_number'] . "</td>";
                            echo "<td>" . $row['plate_number'] . "</td>";
                            echo "<td>" . $row['vehicle_brand'] . "</td>";
                            echo "<td>" . $row['year_release'] . "</td>";
                            echo "<td>" . $row['vehicle_color'] . "</td>";
                            echo "<td>" . $row['vehicle_fuel'] . "</td>";
                            echo "<td>" . $row['status'] . "</td>";
                            echo "<td>
                                    <a href='#' data-bs-toggle='modal' data-bs-target='#imageModal' 
                                       data-image='../User/" . htmlspecialchars($row['vehicle_image']) . "'>
                                       <img src='../User/" . htmlspecialchars($row['vehicle_image']) . "' alt='Vehicle Image' width='100'>
                                    </a>
                                  </td>";
                            echo "<td>
                                    <form method='post' action='vehicle.php'>
                                        <input type='hidden' name='vehicle_id' value='" . $row['vehicle_id'] . "'>
                                        <button type='submit' name='status' value='approved' class='btn btn-success'>Approve</button>
                                        <button type='submit' name='status' value='disapproved' class='btn btn-danger'>Disapprove</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='17'>No vehicles found</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Vehicle Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="modalImage" alt="Vehicle Image" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to handle modal image -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var imageModal = document.getElementById('imageModal');
    imageModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var imageUrl = button.getAttribute('data-image');
        var modalImage = document.getElementById('modalImage');
        modalImage.src = imageUrl;
    });
});
</script>
</body>
</html>