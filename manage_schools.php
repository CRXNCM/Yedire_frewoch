<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'yedire_frewoch';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete school if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $school_id = $conn->real_escape_string($_GET['delete']);
    
    // First, get the image names to delete the files
    $image_query = "SELECT image_name FROM school_images WHERE school_id = '$school_id'";
    $image_result = $conn->query($image_query);
    
    if ($image_result && $image_result->num_rows > 0) {
        while ($image_row = $image_result->fetch_assoc()) {
            $image_path = "../images/" . $school_id . "/" . $image_row['image_name'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
    }
    
    // Delete the school directory
    $school_dir = "../images/" . $school_id;
    if (is_dir($school_dir)) {
        rmdir($school_dir);
    }
    
    // Delete from database
    $conn->query("DELETE FROM school_images WHERE school_id = '$school_id'");
    $conn->query("DELETE FROM schools WHERE school_id = '$school_id'");
    
    header("Location: manage_schools.php?deleted=1");
    exit;
}

// Get all schools
$schools = [];
$result = $conn->query("SELECT * FROM schools ORDER BY name");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schools[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schools - Yedire Frewoch</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
            position: fixed;
            width: 250px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 15px 20px;
        }
        .sidebar .nav-link:hover {
            color: #fff;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,.1);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .logout-btn {
            position: absolute;
            bottom: 20px;
            width: 80%;
            left: 10%;
        }
        .school-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .school-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/admin_sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Schools</h2>
                    <a href="add_school.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New School
                    </a>
                </div>

                <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                    <div class="alert alert-success">School has been successfully deleted.</div>
                <?php endif; ?>

                <?php if (empty($schools)): ?>
                    <div class="alert alert-info">No schools found. Add your first school using the button above.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($schools as $school): ?>
                            <div class="col-md-4">
                                <div class="card school-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($school['name']); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>ID:</strong> <?php echo htmlspecialchars($school['school_id']); ?></p>
                                        <p><strong>Region:</strong> <?php echo htmlspecialchars($school['region']); ?></p>
                                        <p><strong>Children Served:</strong> <?php echo htmlspecialchars($school['children_served']); ?></p>
                                        <p class="text-muted small">Created: <?php echo date('M d, Y', strtotime($school['created_at'])); ?></p>
                                        
                                        <div class="mt-3">
                                            <a href="edit_school.php?id=<?php echo $school['school_id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="view_school_images.php?id=<?php echo $school['school_id']; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-images"></i> Images
                                            </a>
                                            <a href="manage_schools.php?delete=<?php echo $school['school_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this school? This will also delete all associated images.')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>