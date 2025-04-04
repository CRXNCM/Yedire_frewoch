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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $urgency_level = $_POST['urgency_level'];
    $status = isset($_POST['status']) && $_POST['status'] == 'active' ? 'active' : 'inactive';
    $action_link = trim($_POST['action_link']);
    $action_text = trim($_POST['action_text']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF images are allowed";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors[] = "Image size should be less than 5MB";
        } else {
            $upload_dir = 'images/urgent/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $filename = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        // If activating this message, deactivate all others
        if ($status == 'active') {
            $deactivate_query = "UPDATE urgent_messages SET status = 'inactive'";
            $conn->query($deactivate_query);
        }
        
        $insert_query = "INSERT INTO urgent_messages (title, message, image_path, urgency_level, status, action_link, action_text) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssssss", $title, $message, $image_path, $urgency_level, $status, $action_link, $action_text);
        
        if ($stmt->execute()) {
            // Redirect to manage urgent messages page with success message
            $_SESSION['success_message'] = "Urgent message added successfully.";
            header("Location: manage_urgent_messages.php");
            exit;
        } else {
            $errors[] = "Error adding message: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Urgent Message - Yedire Frewoch</title>
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
        .preview-container {
            max-width: 100%;
            margin-top: 15px;
            display: none;
        }
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
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
                        <a class="nav-link active" href="manage_urgent_messages.php">
                            <i class="fas fa-exclamation-circle"></i> Urgent Messages
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
                    <h2>Add Urgent Message</h2>
                    <a href="manage_urgent_messages.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Messages
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

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="title">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                <small class="form-text text-muted">Describe the urgent situation or need. This will be displayed in the popup.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="image">Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="image" name="image" accept="image/jpeg, image/png, image/gif">
                                    <label class="custom-file-label" for="image">Choose file</label>
                                </div>
                                <small class="form-text text-muted">Optional. Recommended size: 600x400px. Max size: 5MB.</small>
                                <div class="preview-container" id="imagePreview">
                                    <img src="#" alt="Preview" class="preview-image">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="urgency_level">Urgency Level</label>
                                <select class="form-control" id="urgency_level" name="urgency_level">
                                    <option value="Urgent" <?php echo (isset($_POST['urgency_level']) && $_POST['urgency_level'] == 'Urgent') ? 'selected' : ''; ?>>Urgent (Red)</option>
                                    <option value="Important" <?php echo (isset($_POST['urgency_level']) && $_POST['urgency_level'] == 'Important') ? 'selected' : ''; ?>>Important (Orange)</option>
                                    <option value="Normal" <?php echo (isset($_POST['urgency_level']) && $_POST['urgency_level'] == 'Normal') ? 'selected' : ''; ?> selected>Normal (Blue)</option>
                                </select>
                                <small class="form-text text-muted">This determines the color of the popup header.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="action_link">Action Link</label>
                                <input type="url" class="form-control" id="action_link" name="action_link" value="<?php echo isset($_POST['action_link']) ? htmlspecialchars($_POST['action_link']) : ''; ?>">
                                <small class="form-text text-muted">Optional. URL where users can take action (e.g., donation page).</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="action_text">Action Button Text</label>
                                <input type="text" class="form-control" id="action_text" name="action_text" value="<?php echo isset($_POST['action_text']) ? htmlspecialchars($_POST['action_text']) : 'Help Now'; ?>">
                                <small class="form-text text-muted">Text to display on the action button. Default: "Help Now"</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="status" name="status" value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="status">Activate immediately</label>
                                </div>
                                <small class="form-text text-muted">If checked, this message will be displayed on the website and any other active messages will be deactivated.</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Message
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
        // Update custom file input label with selected filename
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
            
            // Image preview
            var preview = document.getElementById('imagePreview');
            var previewImg = preview.querySelector('img');
            
            var reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(e.target.files[0]);
        });
    </script>
</body>
</html>