<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Helper function to check if image exists and return fallback if not
function get_image_path($path) {
    if (empty($path) || !file_exists($path)) {
        return 'images/placeholder.jpg'; // Create a placeholder image in this location
    }
    return $path;
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

// Check if school_images table exists, create if it doesn't
$images_table_check = $conn->query("SHOW TABLES LIKE 'school_images'");
if ($images_table_check->num_rows == 0) {
    // Table doesn't exist, create it
    $create_images_table = "CREATE TABLE IF NOT EXISTS `school_images` (
        `image_id` int(11) NOT NULL AUTO_INCREMENT,
        `school_id` int(11) NOT NULL,
        `image_path` varchar(255) NOT NULL,
        `title` varchar(255) DEFAULT NULL,
        `description` text,
        `is_featured` tinyint(1) NOT NULL DEFAULT 0,
        `upload_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`image_id`),
        KEY `school_id` (`school_id`),
        CONSTRAINT `school_images_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($create_images_table);
}

// Check if urgent_messages table exists, create if it doesn't
$table_check = $conn->query("SHOW TABLES LIKE 'urgent_messages'");
if ($table_check->num_rows == 0) {
    // Table doesn't exist, create it
    $create_table_sql = "CREATE TABLE IF NOT EXISTS `urgent_messages` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `message` text NOT NULL,
        `image_path` varchar(255) DEFAULT NULL,
        `urgency_level` enum('Urgent','Important','Normal') NOT NULL DEFAULT 'Normal',
        `status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
        `action_link` varchar(255) DEFAULT NULL,
        `action_text` varchar(100) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($create_table_sql);
}

// Get statistics
$total_schools = 0;
$total_images = 0;
$total_featured = 0;
$children_served = 0;
$total_volunteers = 0;
$total_communities = 0;
$total_urgent_messages = 0;
$active_urgent_messages = 0;

// Count schools
$schools_result = $conn->query("SELECT COUNT(*) as count FROM schools");
if ($schools_result && $schools_result->num_rows > 0) {
    $row = $schools_result->fetch_assoc();
    $total_schools = $row['count'];
}

// Count images
$images_result = $conn->query("SELECT COUNT(*) as count FROM school_images");
if ($images_result && $images_result->num_rows > 0) {
    $row = $images_result->fetch_assoc();
    $total_images = $row['count'];
}

// Count featured images
$featured_result = $conn->query("SELECT COUNT(*) as count FROM school_images WHERE is_featured = 1");
if ($featured_result && $featured_result->num_rows > 0) {
    $row = $featured_result->fetch_assoc();
    $total_featured = $row['count'];
}

// Sum children served
$children_result = $conn->query("SELECT SUM(children_served) as total FROM schools");
if ($children_result && $children_result->num_rows > 0) {
    $row = $children_result->fetch_assoc();
    $children_served = $row['total'] ?: 0;
}

// Count volunteers
$volunteers_result = $conn->query("SELECT COUNT(*) as count FROM volunteers");
if ($volunteers_result && $volunteers_result->num_rows > 0) {
    $row = $volunteers_result->fetch_assoc();
    $total_volunteers = $row['count'];
}

// Count communities
$communities_result = $conn->query("SELECT COUNT(*) as count FROM communities");
if ($communities_result && $communities_result->num_rows > 0) {
    $row = $communities_result->fetch_assoc();
    $total_communities = $row['count'];
}

// Count urgent messages
$urgent_messages_result = $conn->query("SELECT COUNT(*) as count FROM urgent_messages");
if ($urgent_messages_result && $urgent_messages_result->num_rows > 0) {
    $row = $urgent_messages_result->fetch_assoc();
    $total_urgent_messages = $row['count'];
}

// Count active urgent messages
$active_urgent_result = $conn->query("SELECT COUNT(*) as count FROM urgent_messages WHERE status = 'active'");
if ($active_urgent_result && $active_urgent_result->num_rows > 0) {
    $row = $active_urgent_result->fetch_assoc();
    $active_urgent_messages = $row['count'];
}

// Count sponsors
$total_sponsors = 0;
$active_sponsors = 0;

// Check if sponsors table exists
$sponsors_table_check = $conn->query("SHOW TABLES LIKE 'sponsors'");
if ($sponsors_table_check->num_rows > 0) {
    // Count total sponsors
    $sponsors_result = $conn->query("SELECT COUNT(*) as count FROM sponsors");
    if ($sponsors_result && $sponsors_result->num_rows > 0) {
        $row = $sponsors_result->fetch_assoc();
        $total_sponsors = $row['count'];
    }
    
    // Count active sponsors - using is_active instead of status
    $active_sponsors_result = $conn->query("SELECT COUNT(*) as count FROM sponsors WHERE is_active = 1");
    if ($active_sponsors_result && $active_sponsors_result->num_rows > 0) {
        $row = $active_sponsors_result->fetch_assoc();
        $active_sponsors = $row['count'];
    }
}

// Get recent images
$recent_images = [];
$recent_query = "SELECT i.*, s.name as school_name 
                FROM school_images i 
                JOIN schools s ON i.school_id = s.school_id 
                ORDER BY i.upload_date DESC LIMIT 5";
$recent_result = $conn->query($recent_query);

if ($recent_result && $recent_result->num_rows > 0) {
    while ($row = $recent_result->fetch_assoc()) {
        $recent_images[] = $row;
    }
}

// Remove this debug section
// Let's debug the image structure
$image_debug = [];
if (!empty($recent_images)) {
    $image_debug = $recent_images[0]; // Get the first image to see its structure
}

// Get schools list
$schools = [];
$schools_query = "SELECT * FROM schools ORDER BY name";
$schools_result = $conn->query($schools_query);

if ($schools_result && $schools_result->num_rows > 0) {
    while ($row = $schools_result->fetch_assoc()) {
        $schools[] = $row;
    }
}

// Get recent urgent messages
$urgent_messages = [];
$urgent_query = "SELECT * FROM urgent_messages ORDER BY created_at DESC LIMIT 5";
$urgent_result = $conn->query($urgent_query);

if ($urgent_result && $urgent_result->num_rows > 0) {
    while ($row = $urgent_result->fetch_assoc()) {
        $urgent_messages[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Yedire Frewoch</title>
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
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 3rem;
            opacity: 0.8;
        }
        .recent-image {
            height: 80px;
            width: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        .badge-urgent {
            background-color: #dc3545;
            color: white;
        }
        .badge-important {
            background-color: #fd7e14;
            color: white;
        }
        .badge-normal {
            background-color: #007bff;
            color: white;
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
             <?php include 'includes/admin_sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard</h2>
                    <div>
                        <a href="add_school.php" class="btn btn-primary mr-2">
                            <i class="fas fa-plus"></i> Add School
                        </a>
                        <a href="add_image.php" class="btn btn-success mr-2">
                            <i class="fas fa-upload"></i> Upload Images
                        </a>
                        <a href="add_sponsor.php" class="btn btn-warning mr-2">
                            <i class="fas fa-handshake"></i> Add Sponsor
                        </a>
                        <a href="add_urgent_message.php" class="btn btn-danger">
                            <i class="fas fa-exclamation-circle"></i> Add Urgent Message
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Schools</h6>
                                        <h2 class="mb-0"><?php echo $total_schools; ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-school"></i>
                                    </div>
                                </div>
                                <a href="manage_schools.php" class="text-white">
                                    <small>View All <i class="fas fa-arrow-right"></i></small>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Images</h6>
                                        <h2 class="mb-0"><?php echo $total_images; ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-images"></i>
                                    </div>
                                </div>
                                <a href="manage_images.php" class="text-white">
                                    <small>View All <i class="fas fa-arrow-right"></i></small>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Featured Images</h6>
                                        <h2 class="mb-0"><?php echo $total_featured; ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                                <a href="manage_images.php?featured=1" class="text-white">
                                    <small>View Featured <i class="fas fa-arrow-right"></i></small>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Children Served</h6>
                                        <h2 class="mb-0"><?php echo number_format($children_served); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-child"></i>
                                    </div>
                                </div>
                                <small>Daily meals provided</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Statistics Cards for Volunteers, Communities, and Urgent Messages -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card bg-purple text-white" style="background-color: #7e179e;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Volunteers</h6>
                                        <h2 class="mb-0"><?php echo number_format($total_volunteers); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-hands-helping"></i>
                                    </div>
                                </div>
                                <a href="manage_volunteers.php" class="text-white">
                                    <small>Manage Volunteers <i class="fas fa-arrow-right"></i></small>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-orange text-white" style="background-color: #ff7f00;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Communities Reached</h6>
                                        <h2 class="mb-0"><?php echo number_format($total_communities); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <a href="manage_communities.php" class="text-white">
                                    <small>Manage Communities <i class="fas fa-arrow-right"></i></small>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Urgent Messages</h6>
                                        <h2 class="mb-0"><?php echo $total_urgent_messages; ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                </div>
                                <a href="manage_urgent_messages.php" class="text-white">
                                    <small>
                                        <?php if ($active_urgent_messages > 0): ?>
                                            <?php echo $active_urgent_messages; ?> Active Message<?php echo $active_urgent_messages > 1 ? 's' : ''; ?>
                                        <?php else: ?>
                                            Manage Messages
                                        <?php endif; ?>
                                        <i class="fas fa-arrow-right"></i>
                                    </small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Images -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Recent Images</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($recent_images)): ?>
                                    <div class="p-4 text-center">
                                        <p class="text-muted">No images uploaded yet.</p>
                                        <a href="add_image.php" class="btn btn-sm btn-primary">Upload Images</a>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($recent_images as $image): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                         <img src="../images/<?php echo $image['school_id']; ?>/<?php echo $image['image_name']; ?>" 
                                                         class="recent-image mr-3"  
                                             alt="<?php echo htmlspecialchars($image['title']); ?>">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($image['title'] ?? 'Untitled'); ?></h6>
                                                        <p class="mb-1 small text-muted">
                                                            School: <?php echo htmlspecialchars($image['school_name']); ?><br>
                                                            Uploaded: <?php echo date('M d, Y', strtotime($image['upload_date'])); ?>
                                                        </p>
                                                        <a href="edit_image.php?id=<?php echo $image['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="card-footer text-center">
                                        <a href="manage_images.php" class="btn btn-sm btn-outline-primary">View All Images</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Urgent Messages -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">Recent Urgent Messages</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($urgent_messages)): ?>
                                    <div class="p-4 text-center">
                                        <p class="text-muted">No urgent messages created yet.</p>
                                        <a href="add_urgent_message.php" class="btn btn-sm btn-danger">Create Urgent Message</a>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($urgent_messages as $message): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($message['title']); ?></h6>
                                                    <span class="badge badge-<?php echo strtolower($message['urgency_level']); ?>">
                                                        <?php echo $message['urgency_level']; ?>
                                                    </span>
                                                </div>
                                                <p class="mb-1 small text-truncate"><?php echo htmlspecialchars(substr($message['message'], 0, 100)); ?><?php echo strlen($message['message']) > 100 ? '...' : ''; ?></p>
                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                    <small class="text-muted">
                                                        Created: <?php echo date('M d, Y', strtotime($message['created_at'])); ?>
                                                    </small>
                                                    <div>
                                                        <span class="mr-2 status-<?php echo $message['status']; ?>">
                                                            <i class="fas fa-<?php echo $message['status'] == 'active' ? 'check-circle' : 'times-circle'; ?>"></i>
                                                            <?php echo ucfirst($message['status']); ?>
                                                        </span>
                                                        <a href="edit_urgent_message.php?id=<?php echo $message['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="card-footer text-center">
                                        <a href="manage_urgent_messages.php" class="btn btn-sm btn-outline-danger">Manage All Messages</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schools List -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Schools</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($schools)): ?>
                                    <div class="p-4 text-center">
                                        <p class="text-muted">No schools added yet.</p>
                                        <a href="add_school.php" class="btn btn-sm btn-success">Add School</a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>School Name</th>
                                                    <th>Region</th>
                                                    <th>Children</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($schools as $school): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($school['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($school['region']); ?></td>
                                                        <td><?php echo (int)$school['children_served']; ?></td>
                                                        <td>
                                                            <a href="edit_school.php?id=<?php echo $school['school_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="view_school_images.php?id=<?php echo $school['school_id']; ?>" class="btn btn-sm btn-outline-success">
                                                                <i class="fas fa-images"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer text-center">
                                        <a href="manage_schools.php" class="btn btn-sm btn-outline-success">Manage All Schools</a>
                                    </div>
                                <?php endif; ?>
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
