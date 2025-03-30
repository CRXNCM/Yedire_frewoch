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

$success = '';
$error = '';
$school = null;

// Check if school ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_schools.php");
    exit;
}

$school_id = $conn->real_escape_string($_GET['id']);

// Get school data
$query = "SELECT * FROM schools WHERE school_id = '$school_id'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $school = $result->fetch_assoc();
} else {
    header("Location: manage_schools.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $region = $conn->real_escape_string($_POST['region']);
    $children_served = (int)$_POST['children_served'];
    
    // Update school
    $update_query = "UPDATE schools SET 
                    name = '$name', 
                    description = '$description', 
                    region = '$region', 
                    children_served = $children_served 
                    WHERE school_id = '$school_id'";
    
    if ($conn->query($update_query) === TRUE) {
        $success = "School updated successfully!";
        // Refresh school data
        $result = $conn->query($query);
        $school = $result->fetch_assoc();
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit School - Yedire Frewoch</title>
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
        .form-card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 d-none d-md-block sidebar">
                <div class="text-center mb-4">
                    <img src="../images/logo.png" alt="Yedire Frewoch Logo" style="max-width: 150px;">
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_schools.php">
                            <i class="fas fa-school"></i> Manage Schools
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_images.php">
                            <i class="fas fa-images"></i> Manage Images
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../gallery.html" target="_blank">
                            <i class="fas fa-eye"></i> View Gallery
                        </a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-danger logout-btn">Logout</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Edit School: <?php echo htmlspecialchars($school['name']); ?></h2>
                    <div>
                        <a href="view_school_images.php?id=<?php echo $school_id; ?>" class="btn btn-success mr-2">
                            <i class="fas fa-images"></i> Manage Images
                        </a>
                        <a href="manage_schools.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Schools
                        </a>
                    </div>
                </div>

                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card form-card">
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="school_id">School ID</label>
                                <input type="text" class="form-control" id="school_id" value="<?php echo htmlspecialchars($school['school_id']); ?>" readonly>
                                <small class="form-text text-muted">School ID cannot be changed.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="name">School Name</label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       value="<?php echo htmlspecialchars($school['name']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($school['description']); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="region">Region</label>
                                    <select class="form-control" id="region" name="region">
                                        <option value="Region 1" <?php echo ($school['region'] == 'Region 1') ? 'selected' : ''; ?>>Region 1</option>
                                        <option value="Region 2" <?php echo ($school['region'] == 'Region 2') ? 'selected' : ''; ?>>Region 2</option>
                                        <option value="Region 3" <?php echo ($school['region'] == 'Region 3') ? 'selected' : ''; ?>>Region 3</option>
                                        <option value="Other" <?php echo ($school['region'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <label for="children_served">Children Served Daily</label>
                                    <input type="number" class="form-control" id="children_served" name="children_served" 
                                           min="0" value="<?php echo (int)$school['children_served']; ?>">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update School
                            </button>
                        </form>
                    </div>
                </div>

                <!-- School Statistics -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">School Statistics</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get image count
                        $image_count = 0;
                        $image_query = "SELECT COUNT(*) as count FROM school_images WHERE school_id = '$school_id'";
                        $image_result = $conn->query($image_query);
                        if ($image_result && $image_result->num_rows > 0) {
                            $row = $image_result->fetch_assoc();
                            $image_count = $row['count'];
                        }
                        ?>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Created:</strong> <?php echo date('F d, Y', strtotime($school['created_at'])); ?></p>
                                <p><strong>Total Images:</strong> <?php echo $image_count; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Region:</strong> <?php echo htmlspecialchars($school['region']); ?></p>
                                <p><strong>Children Served:</strong> <?php echo (int)$school['children_served']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>