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

// Delete image if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $image_id = (int)$_GET['delete'];
    
    // Get image info before deleting
    $image_query = "SELECT * FROM school_images WHERE id = $image_id";
    $image_result = $conn->query($image_query);
    
    if ($image_result && $image_result->num_rows > 0) {
        $image_data = $image_result->fetch_assoc();
        $image_path = "../images/" . $image_data['school_id'] . "/" . $image_data['image_name'];
        
        // Delete the file if it exists
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        
        // Delete from database
        $conn->query("DELETE FROM school_images WHERE id = $image_id");
        
        header("Location: manage_images.php?deleted=1");
        exit;
    }
}

// Get all images with school info
$images = [];
$query = "SELECT i.*, s.name as school_name 
          FROM school_images i 
          JOIN schools s ON i.school_id = s.school_id 
          ORDER BY i.upload_date DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
}

// Get all schools for the filter dropdown
$schools = [];
$school_result = $conn->query("SELECT * FROM schools ORDER BY name");
if ($school_result && $school_result->num_rows > 0) {
    while ($row = $school_result->fetch_assoc()) {
        $schools[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Images - Yedire Frewoch</title>
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
                    <h2>Manage Images</h2>
                    <a href="add_image.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Images
                    </a>
                </div>

                <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                    <div class="alert alert-success">Image has been successfully deleted.</div>
                <?php endif; ?>

                <!-- Filter Controls -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form id="filterForm" class="form-inline">
                            <div class="form-group mr-3">
                                <label for="schoolFilter" class="mr-2">Filter by School:</label>
                                <select class="form-control" id="schoolFilter">
                                    <option value="">All Schools</option>
                                    <?php foreach ($schools as $school): ?>
                                        <option value="<?php echo htmlspecialchars($school['name']); ?>">
                                            <?php echo htmlspecialchars($school['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group mr-3">
                                <label for="featuredFilter" class="mr-2">Featured Status:</label>
                                <select class="form-control" id="featuredFilter">
                                    <option value="">All Images</option>
                                    <option value="featured">Featured Only</option>
                                    <option value="not-featured">Not Featured</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (empty($images)): ?>
                    <div class="alert alert-info">No images found. Add your first image using the button above.</div>
                <?php else: ?>
                    <div class="row" id="imageGallery">
                        <?php foreach ($images as $image): ?>
                            <div class="col-md-4 image-item" 
                                 data-school="<?php echo htmlspecialchars($image['school_name']); ?>"
                                 data-featured="<?php echo $image['is_featured'] ? 'featured' : 'not-featured'; ?>">
                                <div class="card image-card">
                                    <div class="position-relative">
                                        <img src="../images/<?php echo $image['school_id']; ?>/<?php echo $image['image_name']; ?>" 
                                             class="card-img-top image-thumbnail" 
                                             alt="<?php echo htmlspecialchars($image['title']); ?>">
                                        <?php if ($image['is_featured']): ?>
                                            <div class="featured-badge">Featured</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($image['title'] ?: 'Untitled'); ?></h5>
                                        <p class="card-text">
                                            <small class="text-muted">School: <?php echo htmlspecialchars($image['school_name']); ?></small>
                                        </p>
                                        <p class="card-text">
                                            <small class="text-muted">Uploaded: <?php echo date('M d, Y', strtotime($image['upload_date'])); ?></small>
                                        </p>
                                        <div class="btn-group w-100">
                                            <a href="edit_image.php?id=<?php echo $image['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="#" class="btn btn-sm btn-success view-image" 
                                               data-src="../images/<?php echo $image['school_id']; ?>/<?php echo $image['image_name']; ?>"
                                               data-title="<?php echo htmlspecialchars($image['title'] ?: 'Untitled'); ?>">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="manage_images.php?delete=<?php echo $image['id']; ?>" class="btn btn-sm btn-danger" 
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
            
            // Filtering functionality
            $('#schoolFilter, #featuredFilter').change(function() {
                filterImages();
            });
            
            function filterImages() {
                const schoolFilter = $('#schoolFilter').val();
                const featuredFilter = $('#featuredFilter').val();
                
                $('.image-item').each(function() {
                    const $item = $(this);
                    const school = $item.data('school');
                    const featured = $item.data('featured');
                    
                    let showItem = true;
                    
                    if (schoolFilter && school !== schoolFilter) {
                        showItem = false;
                    }
                    
                    if (featuredFilter && featured !== featuredFilter) {
                        showItem = false;
                    }
                    
                    if (showItem) {
                        $item.show();
                    } else {
                        $item.hide();
                    }
                });
            }
        });
    </script>
</body>
</html>