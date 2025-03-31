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

// Check if sponsors table exists, create if it doesn't
$sponsors_check = $conn->query("SHOW TABLES LIKE 'sponsors'");
if ($sponsors_check->num_rows == 0) {
    // Table doesn't exist, create it
    $create_sponsors_sql = "CREATE TABLE IF NOT EXISTS `sponsors` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `description` text,
        `logo_path` varchar(255) NOT NULL,
        `website_url` varchar(255) DEFAULT NULL,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($create_sponsors_sql);
}

$name = $description = $website_url = '';
$is_active = 1;
$errors = [];
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $website_url = trim($_POST['website_url']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate name
    if (empty($name)) {
        $errors[] = "Sponsor name is required";
    }
    
    // Validate logo upload
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Logo image is required";
    } else {
        $file = $_FILES['logo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading file: " . $file['error'];
        } elseif (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and GIF are allowed";
        } elseif ($file['size'] > $max_size) {
            $errors[] = "File size too large. Maximum size is 5MB";
        }
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        // Create sponsors directory if it doesn't exist
        $upload_dir = 'images/sponsors/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('sponsor_') . '.' . $file_extension;
        $logo_path = $upload_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path)) {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO sponsors (name, description, logo_path, website_url, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $name, $description, $logo_path, $website_url, $is_active);
            
            if ($stmt->execute()) {
                $success_message = "Sponsor added successfully!";
                // Clear form fields after successful submission
                $name = $description = $website_url = '';
                $is_active = 1;
            } else {
                $errors[] = "Error adding sponsor: " . $conn->error;
                // Remove uploaded file if database insert fails
                if (file_exists($logo_path)) {
                    unlink($logo_path);
                }
            }
            
            $stmt->close();
        } else {
            $errors[] = "Error moving uploaded file";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Sponsor - Yedire Frewoch</title>
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
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            display: none;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 d-none d-md-block sidebar">
                <div class="text-center mb-4">
                    <img src="images/logo.png" alt="Yedire Frewoch Logo" style="max-width: 150px;">
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_schools.php">
                            <i class="fas fa-school"></i> Manage Schools
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_images.php">
                            <i class="fas fa-images"></i> Manage Images
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_volunteers.php">
                            <i class="fas fa-hands-helping"></i> Manage Volunteers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_communities.php">
                            <i class="fas fa-users"></i> Manage Communities
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_urgent_messages.php">
                            <i class="fas fa-exclamation-circle"></i> Urgent Messages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_testimonials.php">
                            <i class="fas fa-quote-left"></i> Testimonials
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_sponsors.php">
                            <i class="fas fa-handshake"></i> Sponsors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php" target="_blank">
                            <i class="fas fa-eye"></i> View Website
                        </a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-danger logout-btn">Logout</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Add New Sponsor</h2>
                    <a href="manage_sponsors.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Sponsors
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Sponsor Information</h6>
                    </div>
                    <div class="card-body">
                        <form action="add_sponsor.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="name">Sponsor Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                                <small class="text-muted">Brief description of the sponsor (optional)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="logo">Logo Image <span class="text-danger">*</span></label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/*" required onchange="previewImage(this)">
                                    <label class="custom-file-label" for="logo">Choose file</label>
                                </div>
                                <small class="text-muted">Upload a logo image (JPG, PNG, or GIF, max 5MB)</small>
                                <img id="logoPreview" src="#" alt="Logo Preview" class="preview-image">
                            </div>
                            
                            <div class="form-group">
                                <label for="website_url">Website URL</label>
                                <input type="url" class="form-control" id="website_url" name="website_url" value="<?php echo htmlspecialchars($website_url); ?>" placeholder="https://example.com">
                                <small class="text-muted">Sponsor's website URL (optional)</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" <?php echo $is_active ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                                <small class="text-muted">Display this sponsor on the website</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Sponsor
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
        // Update file input label with selected filename
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
        
        // Preview image before upload
        function previewImage(input) {
            var preview = document.getElementById('logoPreview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>