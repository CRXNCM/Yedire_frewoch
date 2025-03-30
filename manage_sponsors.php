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

// Handle sponsor deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get logo path before deleting
    $stmt = $conn->prepare("SELECT logo_path FROM sponsors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $sponsor = $result->fetch_assoc();
        $logo_path = $sponsor['logo_path'];
        
        // Delete from database
        $delete_stmt = $conn->prepare("DELETE FROM sponsors WHERE id = ?");
        $delete_stmt->bind_param("i", $id);
        
        if ($delete_stmt->execute()) {
            // Delete logo file if it exists
            if (file_exists($logo_path)) {
                unlink($logo_path);
            }
            
            $success_message = "Sponsor deleted successfully!";
        } else {
            $error_message = "Error deleting sponsor: " . $conn->error;
        }
        
        $delete_stmt->close();
    }
    
    $stmt->close();
}

// Handle status toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    
    // Get current status
    $stmt = $conn->prepare("SELECT is_active FROM sponsors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $sponsor = $result->fetch_assoc();
        $new_status = $sponsor['is_active'] ? 0 : 1;
        
        // Update status
        $update_stmt = $conn->prepare("UPDATE sponsors SET is_active = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $new_status, $id);
        
        if ($update_stmt->execute()) {
            $status_message = "Sponsor status updated successfully!";
        } else {
            $error_message = "Error updating sponsor status: " . $conn->error;
        }
        
        $update_stmt->close();
    }
    
    $stmt->close();
}

// Get all sponsors
$sponsors = [];
$sponsors_query = "SELECT * FROM sponsors ORDER BY created_at DESC";
$sponsors_result = $conn->query($sponsors_query);

if ($sponsors_result && $sponsors_result->num_rows > 0) {
    while ($row = $sponsors_result->fetch_assoc()) {
        $sponsors[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sponsors - Yedire Frewoch</title>
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
        .sponsor-logo {
            max-width: 100px;
            max-height: 60px;
            object-fit: contain;
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
                    <h2>Manage Sponsors</h2>
                    <a href="add_sponsor.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Sponsor
                    </a>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if (isset($status_message)): ?>
                    <div class="alert alert-info"><?php echo $status_message; ?></div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">All Sponsors</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($sponsors)): ?>
                            <div class="text-center py-4">
                                <p class="text-muted mb-0">No sponsors found. Click the button above to add a new sponsor.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Logo</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Website</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sponsors as $sponsor): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <?php if (!empty($sponsor['logo_path']) && file_exists($sponsor['logo_path'])): ?>
                                                        <img src="<?php echo htmlspecialchars($sponsor['logo_path']); ?>" alt="<?php echo htmlspecialchars($sponsor['name']); ?>" class="sponsor-logo">
                                                    <?php else: ?>
                                                        <span class="text-muted"><i class="fas fa-image"></i> No logo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($sponsor['name']); ?></td>
                                                <td>
                                                    <?php 
                                                    if (!empty($sponsor['description'])) {
                                                        echo htmlspecialchars(substr($sponsor['description'], 0, 100));
                                                        echo (strlen($sponsor['description']) > 100) ? '...' : '';
                                                    } else {
                                                        echo '<span class="text-muted">No description</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($sponsor['website_url'])): ?>
                                                        <a href="<?php echo htmlspecialchars($sponsor['website_url']); ?>" target="_blank">
                                                            <?php echo htmlspecialchars(parse_url($sponsor['website_url'], PHP_URL_HOST)); ?>
                                                            <i class="fas fa-external-link-alt ml-1 small"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">None</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="status-<?php echo $sponsor['is_active'] ? 'active' : 'inactive'; ?>">
                                                        <i class="fas fa-<?php echo $sponsor['is_active'] ? 'check-circle' : 'times-circle'; ?>"></i>
                                                        <?php echo $sponsor['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($sponsor['created_at'])); ?></td>
                                                <td>
                                                    <a href="edit_sponsor.php?id=<?php echo $sponsor['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="manage_sponsors.php?toggle=<?php echo $sponsor['id']; ?>" class="btn btn-sm btn-<?php echo $sponsor['is_active'] ? 'warning' : 'success'; ?>">
                                                        <i class="fas fa-<?php echo $sponsor['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                        <?php echo $sponsor['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                    </a>
                                                    <a href="manage_sponsors.php?delete=<?php echo $sponsor['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this sponsor? This action cannot be undone.')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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