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

// Handle form submissions
$message = '';
$error = '';

// Delete testimonial
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_query = "DELETE FROM testimonials WHERE id = $id";
    
    if ($conn->query($delete_query)) {
        $message = "Testimonial deleted successfully.";
    } else {
        $error = "Error deleting testimonial: " . $conn->error;
    }
}

// Toggle active status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $toggle_query = "UPDATE testimonials SET is_active = 1 - is_active WHERE id = $id";
    
    if ($conn->query($toggle_query)) {
        $message = "Testimonial status updated successfully.";
    } else {
        $error = "Error updating testimonial status: " . $conn->error;
    }
}

// Get all testimonials
$testimonials = [];
$testimonials_query = "SELECT * FROM testimonials ORDER BY created_at DESC";
$testimonials_result = $conn->query($testimonials_query);

if ($testimonials_result && $testimonials_result->num_rows > 0) {
    while ($row = $testimonials_result->fetch_assoc()) {
        $testimonials[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonials - Yedire Frewoch</title>
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
        .testimonial-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }
        .status-active {
            color: #28a745;
        }
        .status-inactive {
            color: #6c757d;
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
                    <h2>Manage Testimonials</h2>
                    <a href="add_testimonial.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Testimonial
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
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Message</th>
                                        <th>Rating</th>
                                        <th>Status</th>
                                        <th>Date Added</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($testimonials)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No testimonials found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($testimonials as $testimonial): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($testimonial['image_path'])): ?>
                                                        <img src="<?php echo htmlspecialchars($testimonial['image_path']); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="testimonial-img">
                                                    <?php else: ?>
                                                        <div class="testimonial-img bg-secondary d-flex align-items-center justify-content-center text-white">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($testimonial['name']); ?></td>
                                                <td><?php echo htmlspecialchars($testimonial['role']); ?></td>
                                                <td><?php echo mb_substr(htmlspecialchars($testimonial['message']), 0, 50) . '...'; ?></td>
                                                <td>
                                                    <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                                        <i class="fas fa-star text-warning"></i>
                                                    <?php endfor; ?>
                                                </td>
                                                <td>
                                                    <?php if ($testimonial['is_active']): ?>
                                                        <span class="status-active"><i class="fas fa-check-circle"></i> Active</span>
                                                    <?php else: ?>
                                                        <span class="status-inactive"><i class="fas fa-times-circle"></i> Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($testimonial['created_at'])); ?></td>
                                                <td>
                                                    <a href="edit_testimonial.php?id=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="manage_testimonials.php?toggle=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-toggle-on"></i>
                                                    </a>
                                                    <a href="manage_testimonials.php?delete=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this testimonial?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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