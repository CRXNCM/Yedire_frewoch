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
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $role = $conn->real_escape_string($_POST['role']);
    $testimonial_message = $conn->real_escape_string($_POST['message']);
    $rating = intval($_POST['rating']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'images/testimonials/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Check if image file is an actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            // Try to upload file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        } else {
            $error = "File is not an image.";
        }
    }
    
    if (empty($error)) {
        $insert_query = "INSERT INTO testimonials (name, role, message, image_path, rating, is_active) 
                         VALUES ('$name', '$role', '$testimonial_message', '$image_path', $rating, $is_active)";
        
        if ($conn->query($insert_query)) {
            $message = "Testimonial added successfully!";
            // Clear form data
            $_POST = array();
        } else {
            $error = "Error: " . $conn->error;
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
    <title>Add Testimonial - Yedire Frewoch</title>
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
        .rating-container {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .rating-container input {
            display: none;
        }
        .rating-container label {
            cursor: pointer;
            font-size: 25px;
            color: #ddd;
            padding: 5px;
        }
        .rating-container label:hover,
        .rating-container label:hover ~ label,
        .rating-container input:checked ~ label {
            color: #ffc107;
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
                        <a class="nav-link active" href="manage_testimonials.php">
                            <i class="fas fa-quote-left"></i> Testimonials
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
                    <h2>Add New Testimonial</h2>
                    <a href="manage_testimonials.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Testimonials
                    </a>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form action="add_testimonial.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="name">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Role/Position</label>
                                <input type="text" class="form-control" id="role" name="role" value="<?php echo isset($_POST['role']) ? htmlspecialchars($_POST['role']) : ''; ?>">
                                <small class="text-muted">E.g., Volunteer, Donor, School Principal, etc.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Testimonial Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Rating</label>
                                <div class="rating-container">
                                    <input type="radio" id="star5" name="rating" value="5" <?php echo (!isset($_POST['rating']) || $_POST['rating'] == 5) ? 'checked' : ''; ?>>
                                    <label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" id="star4" name="rating" value="4" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 4) ? 'checked' : ''; ?>>
                                    <label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" id="star3" name="rating" value="3" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 3) ? 'checked' : ''; ?>>
                                    <label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" id="star2" name="rating" value="2" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 2) ? 'checked' : ''; ?>>
                                    <label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                                    
                                    <input type="radio" id="star1" name="rating" value="1" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 1) ? 'checked' : ''; ?>>
                                    <label for="star1" title="1 star"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="image">Person Image</label>
                                <input type="file" class="form-control-file" id="image" name="image">
                                <small class="text-muted">Recommended size: 200x200 pixels. Square images work best.</small>
                            </div>
                            
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" <?php echo (!isset($_POST['is_active']) || $_POST['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Active (display on website)</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Add Testimonial</button>
                        </form>
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