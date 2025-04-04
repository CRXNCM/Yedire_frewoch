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

// Handle status toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = $_GET['toggle'];
    
    // Get current status
    $status_query = "SELECT status FROM urgent_messages WHERE id = ?";
    $stmt = $conn->prepare($status_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row) {
        $new_status = ($row['status'] == 'active') ? 'inactive' : 'active';
        
        // If activating this message, deactivate all others
        if ($new_status == 'active') {
            $deactivate_query = "UPDATE urgent_messages SET status = 'inactive' WHERE id != ?";
            $stmt = $conn->prepare($deactivate_query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
        
        // Update status
        $update_query = "UPDATE urgent_messages SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_status, $id);
        
        if ($stmt->execute()) {
            $success_message = "Message status updated successfully.";
        } else {
            $error_message = "Error updating message status: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get image path to delete file
    $image_query = "SELECT image_path FROM urgent_messages WHERE id = ?";
    $stmt = $conn->prepare($image_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    // Delete the message
    $delete_query = "DELETE FROM urgent_messages WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete image file if exists
        if ($row && !empty($row['image_path']) && file_exists($row['image_path'])) {
            unlink($row['image_path']);
        }
        $success_message = "Message deleted successfully.";
    } else {
        $error_message = "Error deleting message: " . $conn->error;
    }
    $stmt->close();
}

// Get urgent messages list
$messages = [];
$messages_query = "SELECT * FROM urgent_messages ORDER BY created_at DESC";
$messages_result = $conn->query($messages_query);

if ($messages_result && $messages_result->num_rows > 0) {
    while ($row = $messages_result->fetch_assoc()) {
        $messages[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Urgent Messages - Yedire Frewoch</title>
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
        .message-preview {
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        .preview-image {
            max-width: 100px;
            max-height: 60px;
            object-fit: cover;
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
                    <h2>Manage Urgent Messages</h2>
                    <a href="add_urgent_message.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Message
                    </a>
                </div>

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

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-info-circle mr-1"></i> Important Note
                    </div>
                    <div class="card-body">
                        <p class="mb-0">Only one urgent message can be active at a time. When you activate a message, any previously active message will be automatically deactivated.</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <?php if (empty($messages)): ?>
                            <div class="p-4 text-center">
                                <p class="text-muted">No urgent messages added yet.</p>
                                <a href="add_urgent_message.php" class="btn btn-sm btn-primary">Add New Message</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Title</th>
                                            <th>Preview</th>
                                            <th>Image</th>
                                            <th>Urgency</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($messages as $message): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($message['title']); ?></td>
                                                <td>
                                                    <div class="message-preview">
                                                        <?php echo htmlspecialchars($message['message']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($message['image_path'])): ?>
                                                        <img src="<?php echo htmlspecialchars($message['image_path']); ?>" class="preview-image" alt="Message image">
                                                    <?php else: ?>
                                                        <span class="text-muted">No image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo strtolower($message['urgency_level']); ?>">
                                                        <?php echo $message['urgency_level']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-<?php echo $message['status']; ?>">
                                                        <i class="fas fa-<?php echo $message['status'] == 'active' ? 'check-circle' : 'times-circle'; ?>"></i>
                                                        <?php echo ucfirst($message['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($message['created_at'])); ?></td>
                                                <td>
                                                    <a href="edit_urgent_message.php?id=<?php echo $message['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="manage_urgent_messages.php?toggle=<?php echo $message['id']; ?>" class="btn btn-sm <?php echo $message['status'] == 'active' ? 'btn-warning' : 'btn-success'; ?>" title="<?php echo $message['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                        <i class="fas fa-<?php echo $message['status'] == 'active' ? 'toggle-off' : 'toggle-on'; ?>"></i>
                                                    </a>
                                                    <a href="manage_urgent_messages.php?delete=<?php echo $message['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this message?');" title="Delete">
                                                        <i class="fas fa-trash"></i>
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