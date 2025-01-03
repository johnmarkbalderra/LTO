<?php
require_once "db-connect.php"; // Include database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and Validate Input
    $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $middleName = filter_input(INPUT_POST, 'middle_name', FILTER_SANITIZE_STRING);
    $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // Don't sanitize passwords yet
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

    // Validate Required Fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        echo json_encode(array('success' => false, 'message' => 'First name, last name, email, and password are required.'));
        exit;
    }

    // Validate Email Format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(array('success' => false, 'message' => 'Invalid email format.'));
        exit;
    }

    // Check if user already exists
    $checkSql = "SELECT first_name, last_name, email FROM admin WHERE first_name = ? AND last_name = ? AND email = ?";
    $checkStmt = $conn->prepare($checkSql);
    if ($checkStmt === false) {
        echo json_encode(array('success' => false, 'message' => 'Prepare failed: (' . $conn->errno . ') ' . $conn->error));
        exit;
    }
    $checkStmt->bind_param("sss", $firstName, $lastName, $email);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
      //register
        echo json_encode(array('success' => false, 'message' => 'User already registered. Please log in.'));
        $checkStmt->close();
        $conn->close();
        exit;
    }
    $checkStmt->close();

    // Handle Image Upload (Adapt to your storage method)
    $imagePath = null; 
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // ... (Implement image upload, validation, and storage logic here)
        // Make sure to sanitize file names and validate file types!
        // Example for image upload logic
        $imageDir = 'uploads/';
        $imagePath = $imageDir . basename($_FILES['image']['name']);
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            echo json_encode(array('success' => false, 'message' => 'Failed to upload image.'));
            exit;
        }
    }

    // Password Hashing (Use a strong algorithm like bcrypt)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Prepare and Execute SQL Query (with Prepared Statements)
    $sql = "INSERT INTO admin (first_name, middle_name, last_name, email, password, address, image)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(array('success' => false, 'message' => 'Prepare failed: (' . $mysqli->errno . ') ' . $mysqli->error));
        exit;
    }
    $stmt->bind_param("sssssss", $firstName, $middleName, $lastName, $email, $hashedPassword, $address, $imagePath);

    if ($stmt->execute()) {
        echo json_encode(array('success' => true, 'redirect' => 'index.php'));
    } else {
        // Handle Database Error (Log error details if possible)
        echo json_encode(array('success' => false, 'message' => 'Database error: ' . $stmt->error));
    }

    $stmt->close(); // Close prepared statement
}

$conn->close(); // Close database connection
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Registration</title>
    <!-- Bootstrap CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
  </head>
  <body class="bg-light">
    
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="card mt-5">
            <div class="card-body">
              <h2 class="card-title mb-4">Admin Registration</h2>
              <form
                action="registration.php"
                method="post"
                enctype="multipart/form-data"
              >
                <div class="mb-3">
                  <label for="first_name" class="form-label">First Name:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="first_name"
                    name="first_name"
                    required
                  />
                </div>

                <div class="mb-3">
                  <label for="middle_name" class="form-label"
                    >Middle Name:</label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="middle_name"
                    name="middle_name"
                  />
                </div>

                <div class="mb-3">
                  <label for="last_name" class="form-label">Last Name:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="last_name"
                    name="last_name"
                    required
                  />
                </div>

                <div class="mb-3">
                  <label for="address" class="form-label">Address:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="address"
                    name="address"
                  />
                </div>

                <div class="mb-3">
                  <label for="email" class="form-label">Email:</label>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    required
                  />
                </div>

                <div class="mb-3">
                  <label for="password" class="form-label">Password:</label>
                  <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    required
                  />
                </div>

                <div class="mb-3">
                  <label for="image" class="form-label">Profile Image:</label>
                  <input
                    type="file"
                    class="form-control"
                    id="image"
                    name="image"
                  />
                </div>

                <button type="submit" class="btn btn-primary">Register</button>
                <a href="../index.html">Login</a>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
