<?php
// PHP file to establish a connection to the MariaDB server.
// Configuration matches the typical setup for this environment.
$servername = "localhost";
$username = "root";
$password = "";    // Must match MYSQL_ROOT_PASSWORD
$database = "livestockdb"; // New database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
  // Graceful exit if connection fails
  die("Connection failed: " . $conn->connect_error);
}

// Set character set to support Thai/UTF-8 data
$conn->set_charset("utf8mb4");

// Note: We skip the "  Connected successfully!" echo to prevent breaking HTML output
?>