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
$image = null;

// Check if image ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_images.php");
    exit;
}

$image_id = (int)$_GET['id'];

// Get image data
$query = "SELECT i.*, s.name as school_name 
          FROM school_images i 
          JOIN schools s ON i.school_id = s.school_id 
          WHERE i.id = $image_id";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $image = $result->fetch_assoc();
} else {
    header("Location: manage_images.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Update image
    $update_query = "UPDATE school_images SET 
                    title = '$title', 
                    description = '$description', 
                    is_featured = $is_featured 
                    WHERE id = $image_id";
    
    if ($conn->query($update_query) === TRUE) {
        $success = "Image updated successfully!";
        // Refresh image data
        $result = $conn->query($query);
        $image = $result->fetch_assoc();
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
    <title>Edit Image - Yedire Frewoch</title>
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
        .image-preview {
            max-height: 300px;
            object-fit: contain;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
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
                    <h2>Edit Image</h2>
                    <div>
                        <a href="view_school_images.php?id=<?php echo $image['school_id']; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to School Images
                        </a>
                    </div>
                </div>

                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card form-card">
                            <div class="card-body">
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="school">School</label>
                                        <input type="text" class="form-control" id="school" value="<?php echo htmlspecialchars($image['school_name']); ?>" readonly>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="title">Image Title</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($image['title']); ?>" 
                                               placeholder="e.g., Morning Meal Distribution">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4" 
                                                  placeholder="Brief description of the image"><?php echo htmlspecialchars($image['description']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" 
                                                   <?php echo $image['is_featured'] ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="is_featured">Set as Featured Image</label>
                                            <small class="form-text text-muted">Featured images appear prominently in the gallery.</small>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Image
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Image Preview</h5>
                            </div>
                            <div class="card-body text-center">
                                <img src="../images/<?php echo $image['school_id']; ?>/<?php echo $image['image_name']; ?>" 
                                     class="img-fluid image-preview" 
                                     alt="<?php echo htmlspecialchars($image['title'] ?: 'Image Preview'); ?>">
                                
                                <div class="mt-3">
                                    <p><strong>File Name:</strong> <?php echo htmlspecialchars($image['image_name']); ?></p>
                                    <p><strong>Uploaded:</strong> <?php echo date('F d, Y', strtotime($image['upload_date'])); ?></p>
                                    <p><strong>Status:</strong> 
                                        <?php if($image['is_featured']): ?>
                                            <span class="badge badge-warning">Featured</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Regular</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="view_school_images.php?id=<?php echo $image['school_id']; ?>&feature=<?php echo $image_id; ?>" 
                                       class="btn btn-warning">
                                        <i class="fas fa-star"></i> <?php echo $image['is_featured'] ? 'Remove from Featured' : 'Mark as Featured'; ?>
                                    </a>
                                    <a href="view_school_images.php?id=<?php echo $image['school_id']; ?>&delete=<?php echo $image_id; ?>" 
                                       class="btn btn-danger ml-2" 
                                       onclick="return confirm('Are you sure you want to delete this image? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i> Delete Image
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Image Details Card -->
                        <div class="card mt-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">Image Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 30%">Image ID</th>
                                        <td><?php echo $image_id; ?></td>
                                    </tr>
                                    <tr>
                                        <th>School</th>
                                        <td><?php echo htmlspecialchars($image['school_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>School ID</th>
                                        <td><?php echo htmlspecialchars($image['school_id']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Upload Date</th>
                                        <td><?php echo date('F d, Y', strtotime($image['upload_date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>File Path</th>
                                        <td><small class="text-muted">../images/<?php echo $image['school_id']; ?>/<?php echo $image['image_name']; ?></small></td>
                                    </tr>
                                </table>
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