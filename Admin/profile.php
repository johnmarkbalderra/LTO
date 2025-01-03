<?php  include 'navbar.php'; 
require_once 'db-connect.php'; // Include database connection

// Ensure 'uploads' directory exists
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true); // Create uploads directory if it doesn't exist
}

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
        
        if (move_uploaded_file($image['tmp_name'], $image_path)) {
    // Update image path in database
    $update_query = "UPDATE admin SET image = ? WHERE admin_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $image_path, $admin_id);
    $update_stmt->execute();

    echo "<script>window.location.href='profile.php';</script>";
exit();
} else {
    echo "Failed to upload image.";
}
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h3>Admin Profile</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Profile Image -->
<div class="col-md-4 text-center">
    <!-- Profile Image -->
    <img src="<?php echo !empty($admin['image']) ? htmlspecialchars($admin['image']) : 'default.png'; ?>" alt="Profile Image" class="img-fluid rounded-circle" style="max-width: 150px; height: 150px; object-fit: cover; border: 4px solid #f7f7f7; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">

    <!-- Update Profile Picture Form -->
    <form action="profile.php" method="POST" enctype="multipart/form-data" class="mt-3">
        <!-- Small file input -->
        <input type="file" name="profile_image" class="form-control form-control-sm">
        <button type="submit" class="btn btn-primary mt-2 w-100" style="border-radius: 12px; background-color: #007aff; border: none; padding: 10px; font-size: 16px;">Update Profile Picture</button>
    </form>
</div>


                    <!-- Profile Details -->
                    <div class="col-md-8">
                        <h4><?php echo htmlspecialchars($admin['first_name'] . ' ' . ($admin['middle_name'] ?? '') . ' ' . $admin['last_name']); ?></h4>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['email'] ?? 'Not available'); ?></p>
                        <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($admin['address'] ?? 'Not available')); ?></p>

                        <!-- Edit Profile Link -->
                        <a href="edit_profile.php" class="btn btn-info text-light w-100" style="border-radius: 12px; background-color: #007aff; padding: 12px; font-size: 16px; transition: background-color 0.3s ease-in-out; text-align: center;">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="./assets/js/script.js"></script>
</body>
</html>