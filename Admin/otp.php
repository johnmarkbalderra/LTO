<?php 
include('db-connect.php');
include 'navbar.php';

// Fetch data
$sql = "SELECT id, email, otp, created_at FROM otp_verification";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>OTP Table</title>
</head>
<body>
<div class="container-fluid py-5" id="page-container">
        <div class="row justify-content-center">
            <div class="col-md-10 text-dark rounded-3 shadow-lg bg-white p-4">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>OTP</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        // Output data of each row with a sequential count
                        $count = 1;
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $count++ . "</td>";
                            echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["otp"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>0 results</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        </div>
        </div>
        </div>

</body>
</html>

<?php
$conn->close();
?>
