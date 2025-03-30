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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $school_id = $conn->real_escape_string($_POST['school_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $region = isset($_POST['region']) ? $conn->real_escape_string($_POST['region']) : ''; // Add check for region
    $children_served = (int)$_POST['children_served'];
    
    // Check if school_id already exists
    $check_query = "SELECT * FROM schools WHERE school_id = '$school_id'";
    $check_result = $conn->query($check_query);
    
    if ($check_result && $check_result->num_rows > 0) {
        $error = "A school with this ID already exists. Please choose a different ID.";
    } else {
        // Insert new school
        $insert_query = "INSERT INTO schools (school_id, name, description, region, children_served) 
                         VALUES ('$school_id', '$name', '$description', '$region', $children_served)";
        
        if ($conn->query($insert_query) === TRUE) {
            // Create directory for school images
            $school_dir = "../images/" . $school_id;
            if (!file_exists($school_dir)) {
                mkdir($school_dir, 0777, true);
            }
            
            $success = "School added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add School - Yedire Frewoch</title>
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
            <?php include 'includes/admin_sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Add New School</h2>
                    <a href="manage_schools.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Schools
                    </a>
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
                                <label for="name">School Name</label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       placeholder="e.g., Addis Ababa School">
                            </div>
                            
                            <div class="form-group">
                                <label for="school_id">School ID</label>
                                <input type="text" class="form-control" id="school_id" name="school_id" required 
                                       placeholder="e.g., school1, addis_ababa_school (no spaces, use underscores)">
                                <small class="form-text text-muted">This will be used in URLs and file paths. Use only letters, numbers, and underscores.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" 
                                          placeholder="Brief description of the school and its meal program"></textarea>
                            </div>
                            
                            <div class="form-row">
                                
                                <div class="form-group col-md-6">
                                    <div class="form-row">
                                        <!-- Region field removed -->
                                        <div class="form-group col-md-12">
                                            <label for="children_served">Children Served Daily</label>
                                            <input type="number" class="form-control" id="children_served" name="children_served" 
                                                   min="0" value="0">
                                        </div>
                                    </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save School
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Auto-generate school_id from name
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value;
            const schoolId = name.toLowerCase()
                .replace(/\s+/g, '_')        // Replace spaces with underscores
                .replace(/[^a-z0-9_]/g, ''); // Remove special characters
            
            document.getElementById('school_id').value = schoolId;
        });
    </script>
</body>
</html>