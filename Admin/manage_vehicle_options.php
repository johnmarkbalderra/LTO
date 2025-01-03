<?php
// Include your database connection
include('db-connect.php');

// Function to fetch options from the database
function fetchOptions($conn, $table, $column) {
    $sql = "SELECT id, $column FROM $table ORDER BY $column ASC";
    $result = $conn->query($sql);
    $options = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }
    } else {
        echo json_encode(['error' => 'No options found in the table']); // Log error if no options are found
    }

    // Check what the result is
    error_log(print_r($options, true));

    return $options;
}

// Handle different requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch data for options based on the table and column
    if (isset($_GET['table']) && isset($_GET['column'])) {
        $table = $_GET['table'];
        $column = $_GET['column'];
        $options = fetchOptions($conn, $table, $column);
        echo json_encode($options);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $table = $_POST['table'];
        $column = $_POST['column'];
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $option_name = $_POST['option_name'];

        // Sanitize input
        $option_name = mysqli_real_escape_string($conn, $option_name);

        if ($action == 'add') {
            // Add new option
            $sql = "INSERT INTO $table ($column) VALUES ('$option_name')";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
        } elseif ($action == 'edit' && $id) {
            // Edit existing option
            $sql = "UPDATE $table SET $column = '$option_name' WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
        } elseif ($action == 'delete' && $id) {
            // Delete an option
            $sql = "DELETE FROM $table WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
        }
    }
}
?>
