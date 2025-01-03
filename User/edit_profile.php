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
        } else {
            echo "Failed to upload image.";
        }
    }
}

// Handle profile data update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $telephone = $_POST['telephone'];
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];

    // Update user profile information
    $update_query = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, email = ?, address = ?, telephone = ?, password = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssssssi", $first_name, $middle_name, $last_name, $email, $address, $telephone, $password, $user_id);
    $update_stmt->execute();

    // Redirect to profile page after update
    header("Location: profile.php");
    exit();
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
    <img src="<?php echo !empty($user['image']) ? htmlspecialchars($user['image']) : 'default.png'; ?>" alt="Profile Image" class="img-fluid" style="max-width: 150px; height: 150px; border-radius: 50%;">
    <input type="file" name="profile_image" class="form-control form-control-sm mt-2">
</div>


                    <!-- User Details -->
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" name="middle_name" id="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea name="address" id="address" class="form-control"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="telephone">Telephone</label>
                        <input type="text" name="telephone" id="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="password">Password (Leave blank to keep current)</label>
                        <input type="password" name="password" id="password" class="form-control">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="update_profile" class="btn btn-primary mt-3">Update Profile</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="scriptjs"></script>
</body>
</html>
