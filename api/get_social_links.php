<?php
// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'yedire_frewoch';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Get active social media links
$social_links = [];
$social_query = "SELECT * FROM social_links WHERE is_active = 1 ORDER BY display_order, platform";
$social_result = $conn->query($social_query);

if ($social_result && $social_result->num_rows > 0) {
    while ($row = $social_result->fetch_assoc()) {
        $social_links[] = $row;
    }
}

// Get active footer links
$footer_links = [];
$footer_query = "SELECT * FROM footer_links WHERE is_active = 1 ORDER BY display_order, title";
$footer_result = $conn->query($footer_query);

if ($footer_result && $footer_result->num_rows > 0) {
    while ($row = $footer_result->fetch_assoc()) {
        $footer_links[] = $row;
    }
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode([
    'social_links' => $social_links,
    'footer_links' => $footer_links
]);

$conn->close();
?>