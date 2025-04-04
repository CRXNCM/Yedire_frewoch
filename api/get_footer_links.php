<?php
// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'yedire_frewoch';

// Set header to return JSON
header('Content-Type: application/json');

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Get active footer links ordered by display_order
$sql = "SELECT title, url FROM footer_links WHERE is_active = 1 ORDER BY display_order ASC";
$result = $conn->query($sql);

$links = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $links[] = $row;
    }
}

// Close connection
$conn->close();

// Return links as JSON
echo json_encode($links);
?>