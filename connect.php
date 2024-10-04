<?php
// Database connection details
$servername = "localhost";  // Change this if necessary
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "machine_health_monitoring"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if GET data is received
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Retrieve data from GET request
    $rpm_value = $_GET['rpm_value'];
    $current_value = $_GET['current_value'];
    $voltage_value = $_GET['voltage_value'];
    $noise_value = $_GET['noise_value'];
    $temperature_value = $_GET['temperature_value'];

    // Insert data into the sensordata table
    $sql = "INSERT INTO sensordata (rpm_value, current_value, voltage_value, noise_value, temperature_value, timestamp) 
            VALUES ('$rpm_value', '$current_value', '$voltage_value', '$noise_value', '$temperature_value', NOW())";

    if ($conn->query($sql) === TRUE) {
        echo "Data inserted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    echo "No GET data received.";
}

$conn->close(); // Close the database connection
?>
