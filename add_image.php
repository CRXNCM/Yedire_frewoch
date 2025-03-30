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

// Get all schools for dropdown
$schools = [];
$result = $conn->query("SELECT * FROM schools ORDER BY name");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schools[] = $row;
    }
}

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $school_id = $conn->real_escape_string($_POST['school_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Check if school exists
    $school_check = $conn->query("SELECT * FROM schools WHERE school_id = '$school_id'");
    if ($school_check->num_rows == 0) {
        $error = "Selected school does not exist.";
    } else {
        // Process uploaded files
        $upload_count = 0;
        $error_count = 0;
        
        // Make sure the school directory exists
        $school_dir = "../images/" . $school_id;
        if (!file_exists($school_dir)) {
            mkdir($school_dir, 0777, true);
        }
        
        // Process each uploaded file
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $file_count = count($_FILES['images']['name']);
            
            for ($i = 0; $i < $file_count; $i++) {
                $file_name = $_FILES['images']['name'][$i];
                $file_tmp = $_FILES['images']['tmp_name'][$i];
                $file_error = $_FILES['images']['error'][$i];
                
                // Skip if there was an upload error
                if ($file_error !== UPLOAD_ERR_OK) {
                    $error_count++;
                    continue;
                }
                
                // Generate a unique filename
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_file_name = uniqid('img_') . '.' . $file_ext;
                $upload_path = $school_dir . '/' . $new_file_name;
                
                // Check file type
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($file_ext, $allowed_types)) {
                    $error_count++;
                    continue;
                }
                
                // Move the file
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Insert into database
                    $insert_query = "INSERT INTO school_images (school_id, image_name, title, description, is_featured) 
                                     VALUES ('$school_id', '$new_file_name', '$title', '$description', $is_featured)";
                    
                    if ($conn->query($insert_query) === TRUE) {
                        $upload_count++;
                    } else {
                        $error_count++;
                        // Delete the file if database insert failed
                        unlink($upload_path);
                    }
                } else {
                    $error_count++;
                }
            }
            
            if ($upload_count > 0) {
                $success = "$upload_count image(s) uploaded successfully!";
                if ($error_count > 0) {
                    $success .= " ($error_count failed)";
                }
            } else {
                $error = "Failed to upload any images.";
            }
        } else {
            $error = "Please select at least one image to upload.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Images - Yedire Frewoch</title>
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
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .image-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid #ddd;
        }
        .custom-file-label::after {
            content: "Browse";
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
                        <a class="nav-link" href="manage_schools.php">
                            <i class="fas fa-school"></i> Manage Schools
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_images.php">
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
                    <h2>Add New Images</h2>
                    <a href="manage_images.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Images
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
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="school_id">Select School</label>
                                <select class="form-control" id="school_id" name="school_id" required>
                                    <option value="">-- Select a School --</option>
                                    <?php foreach ($schools as $school): ?>
                                        <option value="<?php echo $school['school_id']; ?>">
                                            <?php echo htmlspecialchars($school['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="title">Image Title (Optional)</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       placeholder="e.g., Morning Meal Distribution">
                                <small class="form-text text-muted">This title will be used for all uploaded images. You can edit individual titles later.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description (Optional)</label>
                                <textarea class="form-control" id="description" name="description" rows="3" 
                                          placeholder="Brief description of the images"></textarea>
                                <small class="form-text text-muted">This description will be used for all uploaded images. You can edit individual descriptions later.</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured">
                                    <label class="custom-control-label" for="is_featured">Set as Featured Image</label>
                                    <small class="form-text text-muted">Featured images appear prominently in the gallery.</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="images">Upload Images</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="images" name="images[]" multiple accept="image/*" required>
                                    <label class="custom-file-label" for="images">Choose files</label>
                                </div>
                                <small class="form-text text-muted">You can select multiple images at once. Allowed formats: JPG, JPEG, PNG, GIF.</small>
                            </div>
                            
                            <div class="image-preview-container" id="imagePreviewContainer"></div>
                            
                            <button type="submit" class="btn btn-primary mt-3">
                                <i class="fas fa-upload"></i> Upload Images
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
        // Update file input label with selected files
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            const fileCount = this.files.length;
            const label = this.nextElementSibling;
            
            if (fileCount > 0) {
                label.textContent = fileCount + ' files selected';
            } else {
                label.textContent = 'Choose files';
            }
            
            // Preview images
            const previewContainer = document.getElementById('imagePreviewContainer');
            previewContainer.innerHTML = '';
            
            for (let i = 0; i < fileCount; i++) {
                const file = this.files[i];
                
                // Only process image files
                if (!file.type.match('image.*')) {
                    continue;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.classList.add('image-preview');
                    img.src = e.target.result;
                    previewContainer.appendChild(img);
                }
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>