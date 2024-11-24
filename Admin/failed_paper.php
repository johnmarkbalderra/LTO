<?php
require_once 'db-connect.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$vehicle_id = isset($_GET['vehicle_id']) ? intval($_GET['vehicle_id']) : null;

if ($user_id && $vehicle_id) {
    echo "User ID: " . htmlspecialchars($user_id) . "<br>";
    echo "Vehicle ID: " . htmlspecialchars($vehicle_id) . "<br>";

    $stmt = $conn->prepare("
        SELECT 
            u.first_name, 
            u.last_name,
            u.address,
            v.plate_number, 
            v.motor_vehicle_file_number, 
            v.engine_number, 
            v.chassis_number, 
            v.vehicle_fuel, 
            v.vehicle_brand, 
            v.body_type,
            s.start_datetime
        FROM 
            schedule_list s
        JOIN 
            users u ON s.user_id = u.user_id
        JOIN 
            user_vehicles uv ON u.user_id = uv.user_id
        JOIN 
            vehicle v ON uv.vehicle_id = v.vehicle_id
        WHERE 
            s.user_id = ? AND v.vehicle_id = ?");
    $stmt->bind_param("ii", $user_id, $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        // Now we proceed to display the details
        echo "<div class='container'>";
        echo "<div class='header'>";
        echo "<h1>DUMA-SI-BAY POLLUTION TEST CO., San Pablo</h1>";
        echo "<p>Brgy. San Nicolas, San Pablo City, Laguna</p>";
        echo "<p>RAA-2014-11-1405</p>";
        echo "<p>Current Date and Time: " . date('Y-m-d H:i:s') . "</p>";
        echo "</div>";

        echo "<div class='info'>";
        echo "<div class='info-left'>";
        echo "<p><strong>" . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . "</strong></p>";
        echo "<p>" . htmlspecialchars($user['address']) . "</p>";
        echo "<p>" . htmlspecialchars($user['plate_number']) . "</p>";
        echo "<p>" . htmlspecialchars($user['motor_vehicle_file_number']) . "</p>";
        echo "<p>" . htmlspecialchars($user['engine_number']) . "</p>";
        echo "<p>" . htmlspecialchars($user['chassis_number']) . "</p>";
        echo "<p>" . date('Y-m-d H:i:s', strtotime($user['start_datetime'])) . "</p>";
        echo "</div>";
        echo "<div class='info-right'>";
        echo "<p>" . htmlspecialchars($user['vehicle_fuel']) . "</p>";
        echo "<p>" . htmlspecialchars($user['vehicle_brand']) . "</p>";
        echo "<p>" . htmlspecialchars($user['body_type']) . "</p>";
        echo "<p>-</p>";
        echo "<p>PRIVATE</p>";
        echo "</div>";
        echo "</div>";

        echo "<div class='info'>";
        echo "<div class='info-left'>";
        echo "<p><strong>" . date('Y-m-d') . "</strong></p>";
        echo "<p><strong>" . date('Y-m-d', strtotime('+1 year')) . "</strong></p>";
        echo "</div>";
        echo "<div class='info-right'>";
        echo "<p>Eurolink Network International Corporation</p>";
        echo "</div>";
        echo "</div>";

        echo "<div class='content-section'>";
        echo "<div class='image-section'>";
        echo "<img src='path-to-image-file.jpg' alt='Test image'>";
        echo "</div>";
        echo "<div class='test-failed'>";
        echo "<p>FAILED</p>";
        echo "</div>";
        echo "</div>";

        // echo "<div class='signatures'>";
        // echo "<div class='left-signature'>";
        // echo "<p>HARLIES KUMAR C. DALYA</p>";
        // echo "<p>Signature</p>";
        // echo "</div>";
        // echo "<div class='centered-signature'>";
        // echo "<p>RUSSELL R. CALLING</p>";
        // echo "<p>18103500030373</p>";
        // echo "</div>";
        // echo "<div class='right-signature'>";
        // echo "<p>FOR REGISTRATION</p>";
        // echo "</div>";
        // echo "</div>";
        echo "</div>";
    } else {
        echo "User or Vehicle not found.<br>";
        echo "Query executed successfully but no records found for the given user_id and vehicle_id.<br>";
    }
} else {
    echo "Missing required data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Failed Pollution Test Certificate</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 20px;
            max-width: 700px;
            margin: 0 auto;
            border: 1px solid #000;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1, .footer p {
            margin: 5px;
            font-size: 16px;
        }
        .header h1 {
            font-weight: bold;
        }
        .info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .info-left, .info-right {
            width: 48%;
        }
        .info p {
            margin: 5px 0;
            line-height: 1.2;
        }
        .content-section {
            display: flex;
            margin-bottom: 20px;
        }
        .image-section {
            width: 50%;
        }
        img {
            max-width: 100%;
            height: auto;
        }
        .test-passed {
            width: 50%;
            text-align: left;
            font-size: 28px;
            font-weight: bold;
            color: green;
            margin: 0;
            align-self: flex-start;
        }
        .test-failed {
            width: 50%;
            text-align: left;
            font-size: 28px;
            font-weight: bold;
            color: red;
            margin: 0;
            align-self: flex-start;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signatures p {
            margin: 5px;
            text-align: center;
        }
        .centered-signature {
            text-align: center;
            width: 50%;
        }
        .left-signature {
            text-align: left;
            width: 25%;
        }
        .right-signature {
            text-align: right;
            width: 25%;
        }
    </style>
</head>
<body>
    <!-- <div class="container">
        Header
        <div class="header">
            <h1>DUMA-SI-BAY POLLUTION TEST CO., San Pablo</h1>
            <p>Brgy. San Nicolas, San Pablo City, Laguna</p>
            <p>RAA-2014-11-1405</p>
            <p>Current Date and Time</p>
        </div>

        Info Section
        <div class="info">
            <div class="info-left">
                <p><strong>User's Full Name</strong></p>
                <p>Address</p>
                <p>Plate Number</p>
                <p>File Number</p>
                <p>Engine Number</p>
                <p>Chasis Number</p>
                <p>Date and Time Issue</p>
            </div>
            <div class="info-right">
                <p>Fuel Type</p>
                <p>Brand/Make</p>
                <p>Type of Vehicle</p>
                <p>-</p>
                <p>Classification</p>
            </div>
        </div>

        Dates Section
        <div class="info">
            <div class="info-left">
                <p><strong>Date Issue</strong></p>
                <p><strong>Date Expiration</strong></p>
            </div>
            <div class="info-right">
                <p>Eurolink Network International Corporation</p>
            </div>
        </div>

        Content Section: Image on left and PASSED aligned with PRIVATE on the left
        <div class="content-section">
            <div class="image-section">
                <img src="path-to-image-file.jpg" alt="Test image">
            </div>
            <div class="test-failed">
                <p>FAILED</p>
            </div>
        </div>

        Footer with Signatures
        <div class="signatures">
            <div class="left-signature">
                <p>HARLIES KUMAR C. DALYA</p>
                <p>Signature</p>
            </div>
            <div class="centered-signature">
                <p>RUSSELL R. CALLING</p>
                <p>18103500030373</p>
            </div>
            <div class="right-signature">
                <p>FOR REGISTRATION</p>
            </div>
        </div>
    </div> -->
</body>
</html>
