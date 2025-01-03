<?php
include 'navbar.php'; 
require_once 'db-connect.php'; // Include database connection

// Queries for analytics
$totalAdmins = $conn->query("SELECT COUNT(*) as count FROM admin")->fetch_assoc()['count'] ?? 0;
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'] ?? 0;
$totalAppointments = $conn->query("SELECT COUNT(*) as count FROM schedule_list")->fetch_assoc()['count'] ?? 0;
$pendingAppointments = $conn->query("SELECT COUNT(*) as count FROM schedule_list WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0;
$approvedAppointments = $conn->query("SELECT COUNT(*) as count FROM schedule_list WHERE status = 'approved'")->fetch_assoc()['count'] ?? 0;
$totalVehicles = $conn->query("SELECT COUNT(*) as count FROM vehicle")->fetch_assoc()['count'] ?? 0;

// Fetch details
$adminDetails = $conn->query("SELECT * FROM admin");
$userDetails = $conn->query("SELECT * FROM users");
$appointmentDetails = $conn->query("SELECT * FROM schedule_list");
$pendingDetails = $conn->query("SELECT * FROM schedule_list WHERE status = 'pending'");
$approvedDetails = $conn->query("SELECT * FROM schedule_list WHERE status = 'approved'");
$vehicleDetails = $conn->query("SELECT * FROM vehicle");
?>

<!DOCTYPE html>
<html lang="en">

<body>
<div class="container my-5">
  <div class="row container-fluid shadow-lg p-3 mb-5 bg-white rounded">
    <h1 class="text-center mb-4">Dashboard Analytics</h1>
    <div class="col-md-4">
      <div class="card text-white bg-primary mb-3" onclick="showModal('adminModal')">
        <div class="card-body text-center">
          <i class="fas fa-user-shield fa-2x mb-3"></i>
          <h2 class="card-title"><?php echo $totalAdmins; ?></h2>
          <p class="card-text">Total Admins</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-secondary mb-3" onclick="showModal('userModal')">
        <div class="card-body text-center">
          <i class="fas fa-users fa-2x mb-3"></i>
          <h2 class="card-title"><?php echo $totalUsers; ?></h2>
          <p class="card-text">Total Users</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-success mb-3" onclick="showModal('appointmentModal')">
        <div class="card-body text-center">
          <i class="fas fa-calendar-check fa-2x mb-3"></i>
          <h2 class="card-title"><?php echo $totalAppointments; ?></h2>
          <p class="card-text">Total Appointments</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-warning mb-3" onclick="showModal('pendingModal')">
        <div class="card-body text-center">
          <i class="fas fa-hourglass-half fa-2x mb-3"></i>
          <h2 class="card-title"><?php echo $pendingAppointments; ?></h2>
          <p class="card-text">Pending Appointments</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-info mb-3" onclick="showModal('approvedModal')">
        <div class="card-body text-center">
          <i class="fas fa-thumbs-up fa-2x mb-3"></i>
          <h2 class="card-title"><?php echo $approvedAppointments; ?></h2>
          <p class="card-text">Approved Appointments</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-white bg-dark mb-3" onclick="showModal('vehicleModal')">
        <div class="card-body text-center">
          <i class="fas fa-car fa-2x mb-3"></i>
          <h2 class="card-title"><?php echo $totalVehicles; ?></h2>
          <p class="card-text">Total Vehicles</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modals -->
<div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="adminModalLabel">Admin Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Email</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $i = 1;
              while ($row = $adminDetails->fetch_assoc()): 
            ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo $row['first_name']; ?></td>
              <td><?php echo $row['last_name']; ?></td>
              <td><?php echo $row['email']; ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="userModalLabel">User Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $i = 1;
              while ($row = $userDetails->fetch_assoc()): 
            ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo $row['first_name']; ?></td>
              <td><?php echo $row['email']; ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="appointmentModalLabel">Appointment Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>User ID</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $i = 1;
              while ($row = $appointmentDetails->fetch_assoc()): 
            ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo $row['user_id']; ?></td>
              <td><?php echo $row['status']; ?></td>
              <td><?php echo $row['start_datetime']; ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="pendingModal" tabindex="-1" aria-labelledby="pendingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pendingModalLabel">Pending Appointments</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>User ID</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $i = 1;
              while ($row = $pendingDetails->fetch_assoc()): 
            ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo $row['user_id']; ?></td>
              <td><?php echo $row['status']; ?></td>
              <td><?php echo $row['start_datetime']; ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="approvedModal" tabindex="-1" aria-labelledby="approvedModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="approvedModalLabel">Approved Appointments</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>User ID</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $i = 1;
              while ($row = $approvedDetails->fetch_assoc()): 
            ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo $row['user_id']; ?></td>
              <td><?php echo $row['status']; ?></td>
              <td><?php echo $row['start_datetime']; ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="vehicleModalLabel">Vehicle Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>ID</th>
                <th>Plate Number</th>
                <th>Vehicle Brand</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $i = 1;
                while ($row = $vehicleDetails->fetch_assoc()): 
              ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo $row['plate_number']; ?></td>
                <td><?php echo $row['vehicle_brand']; ?></td>
                <td><?php echo $row['status']; ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>




    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script> -->
    <script>
        function showModal(modalId) {
            var modal = new bootstrap.Modal(document.getElementById(modalId));
            modal.show();
        }
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
