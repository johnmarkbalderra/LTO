<?php
include 'navbar.php';
require_once 'dbconnect.php'; // Include database connection
include '../php/setting.php';



// Retrieve user ID from session
$user_id = $_SESSION['user_id'];

// Fetch user data from database
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if user exists
if (!$user) {
    echo "User not found!";
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
            $update_query = "UPDATE users SET image = ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $image_path, $user_id);
            $update_stmt->execute();

            // Refresh the page to display the new image
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
    <div class="card" style="border-radius: 20px; border: none; box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);">
        <div class="card-header" style="background-color: #f7f7f7; border-radius: 20px 20px 0 0;">
            <h3 class="text-center" style="font-family: 'San Francisco', Arial, sans-serif; font-weight: 600; color: #333;">User Profile</h3>
        </div>
        <div class="card-body p-4" style="border-radius: 20px; background-color: #ffffff;">
            <div class="row">
                <!-- Profile Image -->
                <div class="col-md-4 text-center mb-4">
                    <div class="profile-image-wrapper" style="position: relative;">
                        <img src="<?php echo !empty($user['image']) ? htmlspecialchars($user['image']) : 'default.png'; ?>" alt="Profile Image" class="img-fluid rounded-circle" style="max-width: 150px; height: 150px; object-fit: cover; border: 4px solid #f7f7f7; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
                        <form action="profile.php" method="POST" enctype="multipart/form-data" class="mt-3">
                            <input type="file" name="profile_image" class="form-control form-control-sm" style="border-radius: 12px; padding: 8px 12px; background-color: #f7f7f7; font-size: 14px;">
                            <button type="submit" class="btn btn-primary mt-2 w-100" style="border-radius: 12px; background-color: #007aff; border: none; padding: 10px; font-size: 16px;">Update Profile Picture</button>
                        </form>
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="col-md-8">
                    <h4 class="mb-3" style="font-family: 'San Francisco', Arial, sans-serif; font-weight: 600; color: #333;"><?php echo htmlspecialchars($user['first_name'] . ' ' . ($user['middle_name'] ?? '') . ' ' . $user['last_name']); ?></h4>
                    <div class="profile-details">
                        <p><strong>Email:</strong> <span style="color: #333;"><?php echo htmlspecialchars($user['email']); ?></span></p>
                        <p><strong>Address:</strong> <span style="color: #333;"><?php echo nl2br(htmlspecialchars($user['address'] ?? 'Not available')); ?></span></p>
                        <p><strong>Telephone:</strong> <span style="color: #333;"><?php echo htmlspecialchars($user['telephone'] ?? 'Not available'); ?></span></p>
                        <p><strong>Status:</strong> <span style="color: <?php echo $user['IsActive'] ? '#28a745' : '#dc3545'; ?>;"><?php echo $user['IsActive'] ? 'Active' : 'Inactive'; ?></span></p>
                    </div>

                    <!-- Edit Profile Button -->
                    <div class="mt-4 text-center">
                        <a href="edit_profile.php" class="btn btn-info text-light w-100" style="border-radius: 12px; background-color: #007aff; padding: 12px; font-size: 16px; transition: background-color 0.3s ease-in-out; text-align: center;">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Optional Enhancements: Hover Effects for Buttons -->
<style>
    .btn-info:hover {
        background-color: #0056b3;
        transition: background-color 0.3s ease-in-out;
    }
    .profile-image-wrapper:hover img {
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        transition: box-shadow 0.3s ease-in-out;
    }
    .profile-details p {
        font-size: 14px;
        line-height: 1.6;
    }
</style>


    <!-- Scripts -->
    <script src="script.js"></script>
</body>
</html>
