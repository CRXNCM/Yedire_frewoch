<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

// Process deletion if requested
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get image path before deleting
    $sql = "SELECT image_path FROM gallery_images WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $image_path);
                if(mysqli_stmt_fetch($stmt)) {
                    // Delete from database
                    $delete_sql = "DELETE FROM gallery_images WHERE id = ?";
                    if($delete_stmt = mysqli_prepare($conn, $delete_sql)) {
                        mysqli_stmt_bind_param($delete_stmt, "i", $id);
                        if(mysqli_stmt_execute($delete_stmt)) {
                            // Delete file from server
                            $file_path = "../" . $image_path;
                            if(file_exists($file_path)) {
                                unlink($file_path);
                            }
                            $success_msg = "Image deleted successfully.";
                        } else {
                            $error_msg = "Error deleting image from database.";
                        }
                        mysqli_stmt_close($delete_stmt);
                    }
                }
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get filter values
$school_filter = isset($_GET['school']) ? $_GET['school'] : '';
$region_filter = isset($_GET['region']) ? $_GET['region'] : '';

// Build query based on filters
$where_clause = "";
$params = [];
$types = "";

if(!empty($school_filter)) {
    $where_clause .= " AND s.id = ?";
    $params[] = $school_filter;
    $types .= "i";
}

if(!empty($region_filter)) {
    $where_clause .= " AND s.region = ?";
    $params[] = $region_filter;
    $types .= "s";
}

// Get total records for pagination
$count_sql = "SELECT COUNT(*) FROM gallery_images g JOIN schools s ON g.school_id = s.id WHERE 1=1" . $where_clause;
$count_stmt = mysqli_prepare($conn, $count_sql);

if(!empty($types)) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}

mysqli_stmt_execute($count_stmt);
mysqli_stmt_bind_result($count_stmt, $total_records);
mysqli_stmt_fetch($count_stmt);
mysqli_stmt_close($count_stmt);

$total_pages = ceil($total_records / $records_per_page);

// Get gallery images with pagination and filters
$gallery_images = [];
$sql = "SELECT g.id, g.title, g.description, g.image_path, g.upload_date, s.name as school_name, s.region 
        FROM gallery_images g 
        JOIN schools s ON g.school_id = s.id 
        WHERE 1=1" . $where_clause . "
        ORDER BY g.upload_date DESC 
        LIMIT ?, ?";

$stmt = mysqli_prepare($conn, $sql);
$params[] = $offset;
$params[] = $records_per_page;
$types .= "ii";

if(!empty($types)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while($row = mysqli_fetch_assoc($result)) {
    $gallery_images[] = $row;
}
mysqli_stmt_close($stmt);

// Get schools for filter dropdown
$schools = [];
$sql = "SELECT id, name, region FROM schools ORDER BY region, name";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $schools[] = $row;
    }
}

// Get unique regions for filter dropdown
$regions = [];
$sql = "SELECT DISTINCT region FROM schools ORDER BY region";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $regions[] = $row['region'];
    }
}

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - Yedire Frewoch</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #f8f9fa;
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .nav-link {
            font-weight: 500;
            color: #333;
        }
        .nav-link.active {
            color: #756aee;
        }
        main {
            padding-top: 48px;
        }
        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: 1rem;
            background-color: #756aee;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
        }
        .navbar .navbar-toggler {
            top: .25rem;
            right: 1rem;
        }
        .gallery-container {
            background: white;
            padding: 20px;
            border
