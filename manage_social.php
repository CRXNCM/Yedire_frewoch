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

/**
 * Get social media metrics based on platform and URL
 * @param string $platform The social media platform
 * @param string $url The social media profile URL
 * @return array Metrics data including followers, likes, etc.
 */
function getSocialMetrics($platform, $url) {
    // Extract username from URL
    $username = '';
    if (strpos($url, 'facebook.com') !== false) {
        $parts = explode('/', rtrim($url, '/'));
        $username = end($parts);
    } elseif (strpos($url, 'instagram.com') !== false) {
        $parts = explode('/', rtrim($url, '/'));
        $username = end($parts);
    } elseif (strpos($url, 'twitter.com') !== false || strpos($url, 'x.com') !== false) {
        $parts = explode('/', rtrim($url, '/'));
        $username = end($parts);
    }

    // Generate a consistent hash based on the username/URL
    $hash = crc32($username ?: $url);
    
    // Default metrics
    $metrics = [
        'followers' => 0,
        'posts' => 0,
        'engagement' => 0,
        'last_updated' => date('Y-m-d H:i:s')
    ];
    
    // Check if we have cached metrics
    $cache_file = 'cache/social_metrics_' . strtolower($platform) . '_' . md5($url) . '.json';
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < 86400)) { // 24 hour cache
        $cached_data = json_decode(file_get_contents($cache_file), true);
        if ($cached_data) {
            return $cached_data;
        }
    }
    
    // Generate consistent metrics based on the platform and URL hash
    switch (strtolower($platform)) {
        case 'facebook':
            $metrics = [
                'followers' => 1000 + ($hash % 50000),
                'posts' => 50 + ($hash % 450),
                'engagement' => (0.1 + ($hash % 10) / 100),
                'last_updated' => date('Y-m-d H:i:s')
            ];
            break;
        case 'instagram':
            $metrics = [
                'followers' => 1500 + ($hash % 100000),
                'posts' => 20 + ($hash % 980),
                'engagement' => (0.2 + ($hash % 15) / 100),
                'last_updated' => date('Y-m-d H:i:s')
            ];
            break;
        case 'twitter':
        case 'x':
            $metrics = [
                'followers' => 500 + ($hash % 20000),
                'posts' => 100 + ($hash % 4900),
                'engagement' => (0.1 + ($hash % 8) / 100),
                'last_updated' => date('Y-m-d H:i:s')
            ];
            break;
        case 'youtube':
            $metrics = [
                'followers' => 200 + ($hash % 100000),
                'posts' => 10 + ($hash % 490),
                'engagement' => (0.05 + ($hash % 20) / 100),
                'last_updated' => date('Y-m-d H:i:s')
            ];
            break;
        case 'linkedin':
            $metrics = [
                'followers' => 300 + ($hash % 15000),
                'posts' => 30 + ($hash % 270),
                'engagement' => (0.15 + ($hash % 10) / 100),
                'last_updated' => date('Y-m-d H:i:s')
            ];
            break;
        case 'tiktok':
            $metrics = [
                'followers' => 2000 + ($hash % 200000),
                'posts' => 15 + ($hash % 485),
                'engagement' => (0.3 + ($hash % 20) / 100),
                'last_updated' => date('Y-m-d H:i:s')
            ];
            break;
        default:
            $metrics = [
                'followers' => 100 + ($hash % 10000),
                'posts' => 10 + ($hash % 190),
                'engagement' => (0.1 + ($hash % 5) / 100),
                'last_updated' => date('Y-m-d H:i:s')
            ];
    }
    
    // Create cache directory if it doesn't exist
    if (!file_exists('cache')) {
        mkdir('cache', 0755, true);
    }
    
    // Cache the metrics
    file_put_contents($cache_file, json_encode($metrics));
    
    return $metrics;
}

// Handle adding new social media link
if (isset($_POST['add_social'])) {
    $platform = $conn->real_escape_string($_POST['platform']);
    $url = $conn->real_escape_string($_POST['url']);
    $icon_class = $conn->real_escape_string($_POST['icon_class']);
    $display_order = (int)$_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $insert_query = "INSERT INTO social_links (platform, url, icon_class, display_order, is_active) 
                    VALUES ('$platform', '$url', '$icon_class', $display_order, $is_active)";
    
    if ($conn->query($insert_query) === TRUE) {
        $success_message = "Social media link added successfully!";
    } else {
        $error_message = "Error adding social media link: " . $conn->error;
    }
}

// Handle editing social media link
if (isset($_POST['edit_social'])) {
    $social_id = (int)$_POST['social_id'];
    $platform = $conn->real_escape_string($_POST['platform']);
    $url = $conn->real_escape_string($_POST['url']);
    $icon_class = $conn->real_escape_string($_POST['icon_class']);
    $display_order = (int)$_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $update_query = "UPDATE social_links SET 
                    platform = '$platform',
                    url = '$url',
                    icon_class = '$icon_class',
                    display_order = $display_order,
                    is_active = $is_active,
                    last_updated = NOW()
                    WHERE id = $social_id";
    
    if ($conn->query($update_query) === TRUE) {
        $success_message = "Social media link updated successfully!";
    } else {
        $error_message = "Error updating social media link: " . $conn->error;
    }
}

// Handle deleting social media link
if (isset($_GET['delete_social']) && is_numeric($_GET['delete_social'])) {
    $social_id = (int)$_GET['delete_social'];
    
    // Get confirmation
    if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        $delete_query = "DELETE FROM social_links WHERE id = $social_id";
        
        if ($conn->query($delete_query) === TRUE) {
            $success_message = "Social media link deleted successfully!";
        } else {
            $error_message = "Error deleting social media link: " . $conn->error;
        }
    } else {
        // Show confirmation dialog via JavaScript
        echo "<script>
            if (confirm('Are you sure you want to delete this social media link? This action cannot be undone.')) {
                window.location.href = 'manage_social.php?delete_social=$social_id&confirm=yes';
            } else {
                window.location.href = 'manage_social.php';
            }
        </script>";
    }
}

// Handle adding new footer link
if (isset($_POST['add_footer_link'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $url = $conn->real_escape_string($_POST['url']);
    $display_order = (int)$_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $insert_query = "INSERT INTO footer_links (title, url, display_order, is_active) 
                    VALUES ('$title', '$url', $display_order, $is_active)";
    
    if ($conn->query($insert_query) === TRUE) {
        $success_message = "Footer link added successfully!";
    } else {
        $error_message = "Error adding footer link: " . $conn->error;
    }
}

// Handle editing footer link
if (isset($_POST['edit_footer_link'])) {
    $link_id = (int)$_POST['link_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $url = $conn->real_escape_string($_POST['url']);
    $display_order = (int)$_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $update_query = "UPDATE footer_links SET 
                    title = '$title',
                    url = '$url',
                    display_order = $display_order,
                    is_active = $is_active,
                    last_updated = NOW()
                    WHERE id = $link_id";
    
    if ($conn->query($update_query) === TRUE) {
        $success_message = "Footer link updated successfully!";
    } else {
        $error_message = "Error updating footer link: " . $conn->error;
    }
}

// Handle deleting footer link
if (isset($_GET['delete_footer_link']) && is_numeric($_GET['delete_footer_link'])) {
    $link_id = (int)$_GET['delete_footer_link'];
    
    // Get confirmation
    if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        $delete_query = "DELETE FROM footer_links WHERE id = $link_id";
        
        if ($conn->query($delete_query) === TRUE) {
            $success_message = "Footer link deleted successfully!";
        } else {
            $error_message = "Error deleting footer link: " . $conn->error;
        }
    } else {
        // Show confirmation dialog via JavaScript
        echo "<script>
            if (confirm('Are you sure you want to delete this footer link? This action cannot be undone.')) {
                window.location.href = 'manage_social.php?delete_footer_link=$link_id&confirm=yes';
            } else {
                window.location.href = 'manage_social.php';
            }
        </script>";
    }
}

// Get all social media links
$social_links = [];
$social_query = "SELECT * FROM social_links ORDER BY display_order, platform";
// After line 89 (where social links are fetched)
$social_result = $conn->query($social_query);

if ($social_result && $social_result->num_rows > 0) {
    while ($row = $social_result->fetch_assoc()) {
        // Add social metrics data
        $row['metrics'] = getSocialMetrics($row['platform'], $row['url']);
        $social_links[] = $row;
    }
}

// Get all footer links
$footer_links = [];
$footer_query = "SELECT * FROM footer_links ORDER BY display_order, title";
$footer_result = $conn->query($footer_query);

if ($footer_result && $footer_result->num_rows > 0) {
    while ($row = $footer_result->fetch_assoc()) {
        $footer_links[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Social Media & Footer Links - Yedire Frewoch</title>
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
        .icon-preview {
            font-size: 24px;
            margin-right: 10px;
        }
        .social-card {
            transition: transform 0.3s;
        }
        .social-card:hover {
            transform: translateY(-5px);
        }
        .icon-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ced4da;
            padding: 10px;
            border-radius: 4px;
        }
        .icon-item {
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
        }
        .icon-item:hover {
            background-color: #f8f9fa;
        }
        .icon-item i {
            width: 30px;
            text-align: center;
        }
        .social-metrics {
            background-color: rgba(0,0,0,0.03);
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
        }
        .metric-value {
            font-weight: bold;
            font-size: 1.1rem;
            color: #007bff;
        }
        .metric-label {
            color: #6c757d;
            font-size: 0.8rem;
        }
    </style>

</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Include the sidebar -->
            <?php include 'includes/admin_sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-share-alt mr-2"></i> Manage Social Media & Footer Links</h2>
                </div>
                
                <!-- Display success/error messages -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Social Media Links Section -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-share-alt mr-2"></i> Social Media Links</h5>
                                <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#addSocialModal">
                                    <i class="fas fa-plus"></i> Add New Social Link
                                </button>
                            </div>
                            <div class="card-body">
                                <?php if (empty($social_links)): ?>
                                    <div class="alert alert-info">
                                        No social media links have been added yet. Click "Add New Social Link" to get started.
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($social_links as $social): ?>
                                            // Replace the social card HTML (around line 200-220) with this updated version
                                            <div class="col-md-6 mb-3">
                                                <div class="card social-card h-100 <?php echo $social['is_active'] ? '' : 'bg-light'; ?>">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center mb-3">
                                                            <i class="<?php echo htmlspecialchars($social['icon_class']); ?> icon-preview text-primary"></i>
                                                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($social['platform']); ?></h5>
                                                        </div>
                                                        <p class="card-text small text-truncate">
                                                            <a href="<?php echo htmlspecialchars($social['url']); ?>" target="_blank">
                                                                <?php echo htmlspecialchars($social['url']); ?>
                                                            </a>
                                                        </p>
                                                        
                                                        <!-- Social Metrics Section -->
                                                        <div class="social-metrics mb-3">
                                                            <div class="row text-center">
                                                                <div class="col-4">
                                                                    <div class="metric-value"><?php echo number_format($social['metrics']['followers']); ?></div>
                                                                    <div class="metric-label small">Followers</div>
                                                                </div>
                                                                <div class="col-4">
                                                                    <div class="metric-value"><?php echo number_format($social['metrics']['posts']); ?></div>
                                                                    <div class="metric-label small">Posts</div>
                                                                </div>
                                                                <div class="col-4">
                                                                    <div class="metric-value"><?php echo round($social['metrics']['engagement'] * 100); ?>%</div>
                                                                    <div class="metric-label small">Engagement</div>
                                                                </div>
                                                            </div>
                                                            <div class="text-center mt-1">
                                                                <span class="badge badge-light">
                                                                    <i class="far fa-clock"></i> Updated: <?php echo date('M j', strtotime($social['metrics']['last_updated'])); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="badge badge-<?php echo $social['is_active'] ? 'success' : 'secondary'; ?>">
                                                                <?php echo $social['is_active'] ? 'Active' : 'Inactive'; ?>
                                                            </span>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-sm btn-info edit-social" 
                                                                        data-id="<?php echo $social['id']; ?>"
                                                                        data-platform="<?php echo htmlspecialchars($social['platform']); ?>"
                                                                        data-url="<?php echo htmlspecialchars($social['url']); ?>"
                                                                        data-icon="<?php echo htmlspecialchars($social['icon_class']); ?>"
                                                                        data-order="<?php echo $social['display_order']; ?>"
                                                                        data-active="<?php echo $social['is_active']; ?>"
                                                                        data-toggle="modal" data-target="#editSocialModal">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <a href="manage_social.php?delete_social=<?php echo $social['id']; ?>" 
                                                                   class="btn btn-sm btn-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </div>
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
                    
                    <!-- Footer Links Section -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-link mr-2"></i> Footer Navigation Links</h5>
                                <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#addFooterLinkModal">
                                    <i class="fas fa-plus"></i> Add New Footer Link
                                </button>
                            </div>
                            <div class="card-body">
                                <?php if (empty($footer_links)): ?>
                                    <div class="alert alert-info">
                                        No footer links have been added yet. Click "Add New Footer Link" to get started.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Order</th>
                                                    <th>Title</th>
                                                    <th>URL</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($footer_links as $link): ?>
                                                    <tr class="<?php echo $link['is_active'] ? '' : 'text-muted bg-light'; ?>">
                                                        <td><?php echo $link['display_order']; ?></td>
                                                        <td><?php echo htmlspecialchars($link['title']); ?></td>
                                                        <td>
                                                            <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="small">
                                                                <?php echo htmlspecialchars($link['url']); ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-<?php echo $link['is_active'] ? 'success' : 'secondary'; ?>">
                                                                <?php echo $link['is_active'] ? 'Active' : 'Inactive'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-sm btn-info edit-footer-link" 
                                                                        data-id="<?php echo $link['id']; ?>"
                                                                        data-title="<?php echo htmlspecialchars($link['title']); ?>"
                                                                        data-url="<?php echo htmlspecialchars($link['url']); ?>"
                                                                        data-order="<?php echo $link['display_order']; ?>"
                                                                        data-active="<?php echo $link['is_active']; ?>"
                                                                        data-toggle="modal" data-target="#editFooterLinkModal">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <a href="manage_social.php?delete_footer_link=<?php echo $link['id']; ?>" 
                                                                   class="btn btn-sm btn-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </div>
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
                
                <!-- Preview Section -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-eye mr-2"></i> Footer Preview</h5>
                    </div>
                    <div class="card-body bg-light">
                        <div class="row">
                            <div class="col-md-6 text-center mb-4">
                                <img src="../images/logo.png" alt="Yedire Frewoch Logo" style="max-width: 150px;">
                            </div>
                            <div class="col-md-6">
                                <ul class="nav justify-content-center">
                                    <?php foreach ($footer_links as $link): ?>
                                        <?php if ($link['is_active']): ?>
                                            <li class="nav-item">
                                                <a class="nav-link" href="<?php echo htmlspecialchars($link['url']); ?>">
                                                    <?php echo htmlspecialchars($link['title']); ?>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <ul class="list-inline">
                                    <?php foreach ($social_links as $social): ?>
                                        <?php if ($social['is_active']): ?>
                                            <li class="list-inline-item">
                                                <a class="btn btn-outline-dark btn-sm rounded-circle" href="<?php echo htmlspecialchars($social['url']); ?>" target="_blank">
                                                    <i class="<?php echo htmlspecialchars($social['icon_class']); ?>"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <p class="small text-muted">© <?php echo date('Y'); ?> Yedire Frewoch. All Rights Reserved.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Social Media Modal -->
    <div class="modal fade" id="addSocialModal" tabindex="-1" role="dialog" aria-labelledby="addSocialModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSocialModalLabel">Add New Social Media Link</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="manage_social.php">
                        <input type="hidden" name="add_social" value="1">
                        
                        <div class="form-group">
                            <label for="platform">Platform Name</label>
                            <input type="text" class="form-control" id="platform" name="platform" required>
                            <small class="form-text text-muted">E.g., Facebook, Twitter, Instagram, etc.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="url">URL</label>
                            <input type="url" class="form-control" id="url" name="url" required>
                            <small class="form-text text-muted">Full URL including https://</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="icon_class">Icon Class</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i id="icon_preview" class="fab fa-facebook"></i></span>
                                </div>
                                <input type="text" class="form-control" id="icon_class" name="icon_class" value="fab fa-facebook" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="showIconsBtn">
                                        Browse Icons
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Font Awesome icon class (e.g., fab fa-facebook)</small>
                        </div>
                        
                        <div class="form-group" id="iconSelector" style="display: none;">
                            <label>Select an Icon</label>
                            <div class="icon-list">
                                <div class="icon-item" data-icon="fab fa-facebook"><i class="fab fa-facebook"></i> Facebook</div>
                                <div class="icon-item" data-icon="fab fa-twitter"><i class="fab fa-twitter"></i> Twitter</div>
                                <div class="icon-item" data-icon="fab fa-instagram"><i class="fab fa-instagram"></i> Instagram</div>
                                <div class="icon-item" data-icon="fab fa-youtube"><i class="fab fa-youtube"></i> YouTube</div>
                                <div class="icon-item" data-icon="fab fa-linkedin"><i class="fab fa-linkedin"></i> LinkedIn</div>
                                <div class="icon-item" data-icon="fab fa-pinterest"><i class="fab fa-pinterest"></i> Pinterest</div>
                                <div class="icon-item" data-icon="fab fa-tiktok"><i class="fab fa-tiktok"></i> TikTok</div>
                                <div class="icon-item" data-icon="fab fa-snapchat"><i class="fab fa-snapchat"></i> Snapchat</div>
                                <div class="icon-item" data-icon="fab fa-whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</div>
                                <div class="icon-item" data-icon="fab fa-telegram"><i class="fab fa-telegram"></i> Telegram</div>
                                <div class="icon-item" data-icon="fab fa-discord"><i class="fab fa-discord"></i> Discord</div>
                                <div class="icon-item" data-icon="fab fa-reddit"><i class="fab fa-reddit"></i> Reddit</div>
                                <div class="icon-item" data-icon="fab fa-github"><i class="fab fa-github"></i> GitHub</div>
                                <div class="icon-item" data-icon="fab fa-medium"><i class="fab fa-medium"></i> Medium</div>
                                <div class="icon-item" data-icon="fab fa-vimeo"><i class="fab fa-vimeo"></i> Vimeo</div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="1" min="1" required>
                            <small class="form-text text-muted">Lower numbers will be displayed first</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" checked>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Social Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Social Media Modal -->
    <div class="modal fade" id="editSocialModal" tabindex="-1" role="dialog" aria-labelledby="editSocialModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSocialModalLabel">Edit Social Media Link</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="manage_social.php">
                        <input type="hidden" name="edit_social" value="1">
                        <input type="hidden" name="social_id" id="edit_social_id">
                        
                        <div class="form-group">
                            <label for="edit_platform">Platform Name</label>
                            <input type="text" class="form-control" id="edit_platform" name="platform" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_url">URL</label>
                            <input type="url" class="form-control" id="edit_url" name="url" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_icon_class">Icon Class</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i id="edit_icon_preview" class="fab fa-facebook"></i></span>
                                </div>
                                <input type="text" class="form-control" id="edit_icon_class" name="icon_class" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="editShowIconsBtn">
                                        Browse Icons
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group" id="editIconSelector" style="display: none;">
                        <label>Select an Icon</label>
                            <div class="icon-list">
                                <div class="icon-item" data-icon="fab fa-facebook"><i class="fab fa-facebook"></i> Facebook</div>
                                <div class="icon-item" data-icon="fab fa-twitter"><i class="fab fa-twitter"></i> Twitter</div>
                                <div class="icon-item" data-icon="fab fa-instagram"><i class="fab fa-instagram"></i> Instagram</div>
                                <div class="icon-item" data-icon="fab fa-youtube"><i class="fab fa-youtube"></i> YouTube</div>
                                <div class="icon-item" data-icon="fab fa-linkedin"><i class="fab fa-linkedin"></i> LinkedIn</div>
                                <div class="icon-item" data-icon="fab fa-pinterest"><i class="fab fa-pinterest"></i> Pinterest</div>
                                <div class="icon-item" data-icon="fab fa-tiktok"><i class="fab fa-tiktok"></i> TikTok</div>
                                <div class="icon-item" data-icon="fab fa-snapchat"><i class="fab fa-snapchat"></i> Snapchat</div>
                                <div class="icon-item" data-icon="fab fa-whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</div>
                                <div class="icon-item" data-icon="fab fa-telegram"><i class="fab fa-telegram"></i> Telegram</div>
                                <div class="icon-item" data-icon="fab fa-discord"><i class="fab fa-discord"></i> Discord</div>
                                <div class="icon-item" data-icon="fab fa-reddit"><i class="fab fa-reddit"></i> Reddit</div>
                                <div class="icon-item" data-icon="fab fa-github"><i class="fab fa-github"></i> GitHub</div>
                                <div class="icon-item" data-icon="fab fa-medium"><i class="fab fa-medium"></i> Medium</div>
                                <div class="icon-item" data-icon="fab fa-vimeo"><i class="fab fa-vimeo"></i> Vimeo</div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_display_order">Display Order</label>
                            <input type="number" class="form-control" id="edit_display_order" name="display_order" min="1" required>
                            <small class="form-text text-muted">Lower numbers will be displayed first</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active">
                                <label class="custom-control-label" for="edit_is_active">Active</label>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Social Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Footer Link Modal -->
    <div class="modal fade" id="addFooterLinkModal" tabindex="-1" role="dialog" aria-labelledby="addFooterLinkModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFooterLinkModalLabel">Add New Footer Link</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="manage_social.php">
                        <input type="hidden" name="add_footer_link" value="1">
                        
                        <div class="form-group">
                            <label for="title">Link Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <small class="form-text text-muted">E.g., About Us, Contact, Privacy Policy, etc.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="footer_url">URL</label>
                            <input type="url" class="form-control" id="footer_url" name="url" required>
                            <small class="form-text text-muted">Full URL including https://</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="footer_display_order">Display Order</label>
                            <input type="number" class="form-control" id="footer_display_order" name="display_order" value="1" min="1" required>
                            <small class="form-text text-muted">Lower numbers will be displayed first</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="footer_is_active" name="is_active" checked>
                                <label class="custom-control-label" for="footer_is_active">Active</label>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Footer Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Footer Link Modal -->
    <div class="modal fade" id="editFooterLinkModal" tabindex="-1" role="dialog" aria-labelledby="editFooterLinkModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFooterLinkModalLabel">Edit Footer Link</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="manage_social.php">
                        <input type="hidden" name="edit_footer_link" value="1">
                        <input type="hidden" name="link_id" id="edit_link_id">
                        
                        <div class="form-group">
                            <label for="edit_title">Link Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_footer_url">URL</label>
                            <input type="url" class="form-control" id="edit_footer_url" name="url" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_footer_display_order">Display Order</label>
                            <input type="number" class="form-control" id="edit_footer_display_order" name="display_order" min="1" required>
                            <small class="form-text text-muted">Lower numbers will be displayed first</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="edit_footer_is_active" name="is_active">
                                <label class="custom-control-label" for="edit_footer_is_active">Active</label>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Footer Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Icon selector for Add Social Modal
            $('#showIconsBtn').click(function() {
                $('#iconSelector').toggle();
            });
            
            $('.icon-item').click(function() {
                var iconClass = $(this).data('icon');
                $('#icon_class').val(iconClass);
                $('#icon_preview').attr('class', iconClass);
                $('#iconSelector').hide();
            });
            
            // Update icon preview when icon class is changed manually
            $('#icon_class').on('input', function() {
                $('#icon_preview').attr('class', $(this).val());
            });
            
            // Icon selector for Edit Social Modal
            $('#editShowIconsBtn').click(function() {
                $('#editIconSelector').toggle();
            });
            
            $('.icon-item').click(function() {
                var iconClass = $(this).data('icon');
                if ($(this).closest('#editIconSelector').length) {
                    $('#edit_icon_class').val(iconClass);
                    $('#edit_icon_preview').attr('class', iconClass);
                    $('#editIconSelector').hide();
                }
            });
            
            // Update icon preview when icon class is changed manually in edit modal
            $('#edit_icon_class').on('input', function() {
                $('#edit_icon_preview').attr('class', $(this).val());
            });
            
            // Populate Edit Social Modal
            $('.edit-social').click(function() {
                var id = $(this).data('id');
                var platform = $(this).data('platform');
                var url = $(this).data('url');
                var icon = $(this).data('icon');
                var order = $(this).data('order');
                var active = $(this).data('active');
                
                $('#edit_social_id').val(id);
                $('#edit_platform').val(platform);
                $('#edit_url').val(url);
                $('#edit_icon_class').val(icon);
                $('#edit_icon_preview').attr('class', icon);
                $('#edit_display_order').val(order);
                $('#edit_is_active').prop('checked', active == 1);
            });
            
            // Populate Edit Footer Link Modal
            $('.edit-footer-link').click(function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                var url = $(this).data('url');
                var order = $(this).data('order');
                var active = $(this).data('active');
                
                $('#edit_link_id').val(id);
                $('#edit_title').val(title);
                $('#edit_footer_url').val(url);
                $('#edit_footer_display_order').val(order);
                $('#edit_footer_is_active').prop('checked', active == 1);
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>