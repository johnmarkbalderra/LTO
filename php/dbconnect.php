// <?php
// // Replace these values with your actual database credentials
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "land";

// // Create connection
// $mysqli = new mysqli($servername, $username, $password, $dbname);

// // Check connection
// if ($mysqli->connect_error) {
//     die("Connection failed: " . $mysqli->connect_error);
// }
// ?>
<?php
// PHP Data Objects(PDO) Sample Code:
try {
    $conn = new PDO("sqlsrv:server = tcp:johnadmin.database.windows.net,1433; Database = land", "CloudSAc76ee709", "markbaldera88_gmail.com#EXT#@markbaldera88gmail.onmicrosoft.com");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    print("Error connecting to SQL Server.");
    die(print_r($e));
}

// SQL Server Extension Sample Code:
$connectionInfo = array("UID" => "CloudSAc76ee709", "pwd" => "markbaldera88_gmail.com#EXT#@markbaldera88gmail.onmicrosoft.com", "Database" => "land", "LoginTimeout" => 30, "Encrypt" => 1, "TrustServerCertificate" => 0);
$serverName = "tcp:johnadmin.database.windows.net,1433";
$conn = sqlsrv_connect($serverName, $connectionInfo);
?>
