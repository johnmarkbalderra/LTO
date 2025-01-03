<?php
// Test script to simulate notify_user.php

$url = 'http://localhost/LTOO1/Admin/notify_user.php'; // Adjust to your correct local URL

// Example test data
$data = [
    'user_id' => 1,  // Replace with an actual user ID from your database
    'vehicle_id' => 2,  // Replace with an actual vehicle ID from your database
    'message' => 'Your vehicle has passed the pollution test.'
];

// Convert the data to JSON format
$data_json = json_encode($data);

// Set up cURL
$ch = curl_init($url);

// Configure cURL options
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data_json)
]);

// Execute the request
$response = curl_exec($ch);

// Close the cURL session
curl_close($ch);

// Output the response
echo $response;
