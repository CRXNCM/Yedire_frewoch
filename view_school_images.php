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

// Check if school ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_schools.php");
    exit;
}

$school_id = $conn->real_escape_string($_GET['id']);

// Get school data
$school = null;
$school_query = "SELECT * FROM schools WHERE school_id = '$school_id'";
$school_result = $conn->query($school_query);

if ($school_result && $school_result->num_rows > 0) {
    $school = $school_result->fetch_assoc();
} else {
    header("Location: manage_schools.php");
    exit;
}

// Delete image if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $image_id = (int)$_GET['delete'];
    
    // Get image info before deleting
    $image_query = "SELECT * FROM school_images WHERE id = $image_id AND school_id = '$school_id'";
    $image_result = $conn->query($image_query);
    
    if ($image_result && $image_result->num_rows > 0) {
        $image_data = $image_result->fetch_assoc();
        $image_path = "../images/" . $school_id . "/" . $image_data['image_name'];
        
        // Delete the file if it exists
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        
        // Delete from database
        $conn->query("DELETE FROM school_images WHERE id = $image_id");
        
        header("Location: view_school_images.php?id=$school_id&deleted=1");
        exit;
    }
}

// Toggle featured status if requested
if (isset($_GET['feature']) && !empty($_GET['feature'])) {
    $image_id = (int)$_GET['feature'];
    
    // Get current featured status
    $feature_query = "SELECT is_featured FROM school_images WHERE id = $image_id AND school_id = '$school_id'";
    $feature_result = $conn->query($feature_query);
    
    if ($feature_result && $feature_result->num_rows > 0) {
        $feature_data = $feature_result->fetch_assoc();
        $new_status = $feature_data['is_featured'] ? 0 : 1;
        
        // Update featured status
        $conn->query("UPDATE school_images SET is_featured = $new_status WHERE id = $image_id");
        
        header("Location: view_school_images.php?id=$school_id&featured=1");
        exit;
    }
}

// Get all images for this school
$images = [];
$images_query = "SELECT * FROM school_images WHERE school_id = '$school_id' ORDER BY upload_date DESC";
$images_result = $conn->query($images_query);

if ($images_result && $images_result->num_rows > 0) {
    while ($row = $images_result->fetch_assoc()) {
        $images[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Images - <?php echo htmlspecialchars($school['name']); ?></title>
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
        .image-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .image-card:hover {
            transform: translateY(-5px);
        }
        .image-thumbnail {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .featured-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(255, 193, 7, 0.9);
            color: #000;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
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
                    <h2>Images for: <?php echo htmlspecialchars($school['name']); ?></h2>
                    <div>
                        <a href="add_image.php?school=<?php echo $school_id; ?>" class="btn btn-primary mr-2">
                            <i class="fas fa-plus"></i> Add Images
                        </a>
                        <a href="edit_school.php?id=<?php echo $school_id; ?>" class="btn btn-info mr-2">
                            <i class="fas fa-edit"></i> Edit School
                        </a>
                        <a href="manage_schools.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Schools
                        </a>
                    </div>
                </div>

                <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                    <div class="alert alert-success">Image has been successfully deleted.</div>
                <?php endif; ?>

                <?php if (isset($_GET['featured']) && $_GET['featured'] == 1): ?>
                    <div class="alert alert-success">Featured status has been updated.</div>
                <?php endif; ?>

                <?php if (empty($images)): ?>
                    <div class="alert alert-info">
                        No images found for this school. 
                        <a href="add_image.php?school=<?php echo $school_id; ?>" class="alert-link">Add some images</a>.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($images as $image): ?>
                            <div class="col-md-4">
                                <div class="card image-card">
                                    <div class="position-relative">
                                        <img src="../images/<?php echo $school_id; ?>/<?php echo $image['image_name']; ?>" 
                                             class="card-img-top image-thumbnail" 
                                             alt="<?php echo htmlspecialchars($image['title'] ?: 'School Image'); ?>">
                                        <?php if ($image['is_featured']): ?>
                                            <div class="featured-badge">Featured</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($image['title'] ?: 'Untitled'); ?></h5>
                                        <?php if ($image['description']): ?>
                                            <p class="card-text"><?php echo htmlspecialchars($image['description']); ?></p>
                                        <?php endif; ?>
                                        <p class="card-text">
                                            <small class="text-muted">Uploaded: <?php echo date('M d, Y', strtotime($image['upload_date'])); ?></small>
                                        </p>
                                        <div class="btn-group w-100">
                                            <a href="edit_image.php?id=<?php echo $image['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="view_school_images.php?id=<?php echo $school_id; ?>&feature=<?php echo $image['id']; ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-star"></i> <?php echo $image['is_featured'] ? 'Unfeature' : 'Feature'; ?>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-success view-image" 
                                               data-src="../images/<?php echo $school_id; ?>/<?php echo $image['image_name']; ?>"
                                               data-title="<?php echo htmlspecialchars($image['title'] ?: 'Untitled'); ?>">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="view_school_images.php?id=<?php echo $school_id; ?>&delete=<?php echo $image['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this image?')">
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

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imagePreviewTitle">Image Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="previewImage" src="" alt="Preview" class="img-fluid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Image preview functionality
            $('.view-image').click(function(e) {
                e.preventDefault();
                const imgSrc = $(this).data('src');
                const imgTitle = $(this).data('title');
                
                $('#previewImage').attr('src', imgSrc);
                $('#imagePreviewTitle').text(imgTitle);
                $('#imagePreviewModal').modal('show');
            });
        });
    </script>
</body>
</html>