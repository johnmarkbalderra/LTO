<?php 
include 'navbar.php'; 
require_once 'db-connect.php'; // Include database connection

// Retrieve admin ID from session
$admin_id = $_SESSION['admin_id'];

// Fetch admin data
$query = "SELECT * FROM admin WHERE admin_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Check if data was retrieved
if (!$admin) {
    echo "No data found for the admin with ID: $admin_id";
    exit();
}

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $image = $_FILES['profile_image'];
    if ($image['error'] == 0) {
        $image_path = 'uploads/' . basename($image['name']);
        
        // Ensure the 'uploads' directory exists
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        if (move_uploaded_file($image['tmp_name'], $image_path)) {
            // Update the image path in the database
            $update_query = "UPDATE admin SET image = ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $image_path, $user_id);
            $update_stmt->execute();
        } else {
            echo "Failed to upload image.";
        }
    }
}
// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // Update profile information in the database
    $update_query = "UPDATE admin SET first_name = ?, middle_name = ?, last_name = ?, email = ?, address = ? WHERE admin_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssssi", $first_name, $middle_name, $last_name, $email, $address, $admin_id);
    
    if ($update_stmt->execute()) {
        // Success message
        $success_message = "Profile updated successfully!";
    } else {
        // Error message
        $error_message = "Failed to update profile.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<body>
    <div class="container mt-5">
        
        <div class="card">
            <div class="card-header">
                <h3>Edit Profile</h3>
            </div>
            <div class="card-body">
                <a href="profile.php" class="btn btn-secondary mb-3">Back to Profile</a>
                <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
                    <!-- Profile Image -->
<div class="form-group text-center">
    <img src="<?php echo !empty($admin['image']) ? htmlspecialchars($admin['image']) : 'default.png'; ?>" alt="Profile Image" class="img-fluid" style="max-width: 150px; height: 150px; border-radius: 50%;">
    <input type="file" name="profile_image" class="form-control form-control-sm mt-2">
</div>
                
                <form action="edit_profile.php" method="POST">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($admin['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" value="<?php echo htmlspecialchars($admin['middle_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($admin['last_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea name="address" class="form-control" rows="4" required><?php echo htmlspecialchars($admin['address']); ?></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="./assets/js/jquery-3.6.0.min.js"></script>
    <script src="./assets/js/bootstrap.min.js"></script>
</body>
</html>
