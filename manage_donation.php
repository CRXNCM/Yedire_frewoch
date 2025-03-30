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

// Handle adding new bank account
if (isset($_POST['add_bank_info'])) {
    $bank_name = $conn->real_escape_string($_POST['bank_name']);
    $account_name = $conn->real_escape_string($_POST['account_name']);
    $account_number = $conn->real_escape_string($_POST['account_number']);
    $routing_number = $conn->real_escape_string($_POST['routing_number']);
    $swift_code = $conn->real_escape_string($_POST['swift_code']);
    $bank_address = $conn->real_escape_string($_POST['bank_address']);
    $payment_link = $conn->real_escape_string($_POST['payment_link'] ?? '');
    
    // Handle bank image upload
    $bank_image = "";
    if(isset($_FILES['bank_image']) && $_FILES['bank_image']['error'] == 0) {
        $upload_dir = "uploads/bank_images/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . $_FILES['bank_image']['name'];
        $target_file = $upload_dir . $file_name;
        
        if(move_uploaded_file($_FILES['bank_image']['tmp_name'], $target_file)) {
            $bank_image = $target_file;
        }
    }
    
    $insert_query = "INSERT INTO bank_info (bank_name, account_name, account_number, routing_number, swift_code, bank_address, bank_image, payment_link) 
                    VALUES ('$bank_name', '$account_name', '$account_number', '$routing_number', '$swift_code', '$bank_address', '$bank_image', '$payment_link')";
    
    if ($conn->query($insert_query) === TRUE) {
        $success_message = "Bank account added successfully!";
    } else {
        $error_message = "Error adding bank account: " . $conn->error;
    }
}

// Handle bank info edit
if (isset($_POST['edit_bank_info'])) {
    $bank_id = (int)$_POST['bank_id'];
    $bank_name = $conn->real_escape_string($_POST['bank_name']);
    $account_name = $conn->real_escape_string($_POST['account_name']);
    $account_number = $conn->real_escape_string($_POST['account_number']);
    $routing_number = $conn->real_escape_string($_POST['routing_number']);
    $swift_code = $conn->real_escape_string($_POST['swift_code']);
    $bank_address = $conn->real_escape_string($_POST['bank_address']);
    $payment_link = $conn->real_escape_string($_POST['payment_link'] ?? '');

    // Get current bank image
    $current_image_query = "SELECT bank_image FROM bank_info WHERE id = $bank_id";
    $current_image_result = $conn->query($current_image_query);
    $current_image = "";
    if ($current_image_result && $current_image_result->num_rows > 0) {
        $current_image = $current_image_result->fetch_assoc()['bank_image'];
    }
    
    // Handle bank image upload
    $bank_image = $current_image;
    if(isset($_FILES['bank_image']) && $_FILES['bank_image']['error'] == 0) {
        $upload_dir = "uploads/bank_images/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . $_FILES['bank_image']['name'];
        $target_file = $upload_dir . $file_name;
        
        if(move_uploaded_file($_FILES['bank_image']['tmp_name'], $target_file)) {
            // Delete old image if exists
            if (!empty($current_image) && file_exists($current_image)) {
                unlink($current_image);
            }
            $bank_image = $target_file;
        }
    }
    
    $update_query = "UPDATE bank_info SET 
                    bank_name = '$bank_name',
                    account_name = '$account_name',
                    account_number = '$account_number',
                    routing_number = '$routing_number',
                    swift_code = '$swift_code',
                    bank_address = '$bank_address',
                    bank_image = '$bank_image',
                    last_updated = NOW()
                    WHERE id = $bank_id";
    
    if ($conn->query($update_query) === TRUE) {
        $success_message = "Bank account updated successfully!";
    } else {
        $error_message = "Error updating bank account: " . $conn->error;
    }
}

// Handle bank deletion
if (isset($_GET['delete_bank']) && is_numeric($_GET['delete_bank'])) {
    $bank_id = (int)$_GET['delete_bank'];
    
    // Get confirmation
    if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        // Get bank image to delete
        $image_query = "SELECT bank_image FROM bank_info WHERE id = $bank_id";
        $image_result = $conn->query($image_query);
        if ($image_result && $image_result->num_rows > 0) {
            $bank_image = $image_result->fetch_assoc()['bank_image'];
            if (!empty($bank_image) && file_exists($bank_image)) {
                unlink($bank_image);
            }
        }
        
        $delete_query = "DELETE FROM bank_info WHERE id = $bank_id";
        
        if ($conn->query($delete_query) === TRUE) {
            $success_message = "Bank account deleted successfully!";
        } else {
            $error_message = "Error deleting bank account: " . $conn->error;
        }
    } else {
        // Show confirmation dialog via JavaScript
        echo "<script>
            if (confirm('Are you sure you want to delete this bank account? This action cannot be undone.')) {
                window.location.href = 'manage_donation.php?delete_bank=$bank_id&confirm=yes';
            } else {
                window.location.href = 'manage_donation.php';
            }
        </script>";
    }
}

// Get all bank accounts
$bank_accounts = [];
$bank_query = "SELECT * FROM bank_info ORDER BY bank_name";
$bank_result = $conn->query($bank_query);

if ($bank_result && $bank_result->num_rows > 0) {
    while ($row = $bank_result->fetch_assoc()) {
        $bank_accounts[] = $row;
    }
}
// Get current bank info
$bank_info = [];
$bank_info_query = "SELECT * FROM bank_info ORDER BY id LIMIT 1";
$bank_info_result = $conn->query($bank_info_query);

if ($bank_info_result && $bank_info_result->num_rows > 0) {
    $bank_info = $bank_info_result->fetch_assoc();
}

// Handle bank info updates
if (isset($_POST['update_bank_info'])) {
    $bank_name = $conn->real_escape_string($_POST['bank_name']);
    $account_name = $conn->real_escape_string($_POST['account_name']);
    $account_number = $conn->real_escape_string($_POST['account_number']);
    $routing_number = $conn->real_escape_string($_POST['routing_number']);
    $swift_code = $conn->real_escape_string($_POST['swift_code']);
    $bank_address = $conn->real_escape_string($_POST['bank_address']);
    
    // Check if any bank account exists
    $check_query = "SELECT COUNT(*) as count FROM bank_info";
    $result = $conn->query($check_query);
    $count = 0;
    
    if ($result && $result->num_rows > 0) {
        $count = $result->fetch_assoc()['count'];
    }
    
    if ($count > 0) {
        // Update the first bank account (for backward compatibility)
        $update_query = "UPDATE bank_info SET 
                        bank_name = '$bank_name',
                        account_name = '$account_name',
                        account_number = '$account_number',
                        routing_number = '$routing_number',
                        swift_code = '$swift_code',
                        bank_address = '$bank_address',
                        last_updated = NOW()
                        WHERE id = (SELECT MIN(id) FROM bank_info)";
        
        if ($conn->query($update_query) === TRUE) {
            $success_message = "Bank information updated successfully!";
        } else {
            $error_message = "Error updating bank information: " . $conn->error;
        }
    } else {
        // Insert a new bank account
        $insert_query = "INSERT INTO bank_info (bank_name, account_name, account_number, routing_number, swift_code, bank_address) 
                        VALUES ('$bank_name', '$account_name', '$account_number', '$routing_number', '$swift_code', '$bank_address')";
        
        if ($conn->query($insert_query) === TRUE) {
            $success_message = "Bank information added successfully!";
        } else {
            $error_message = "Error adding bank information: " . $conn->error;
        }
    }
}

// Handle donation status updates
if (isset($_POST['update_status'])) {
    $donation_id = (int)$_POST['donation_id'];
    $new_status = $conn->real_escape_string($_POST['status']);
    $admin_notes = $conn->real_escape_string($_POST['admin_notes']);
    
    $update_query = "UPDATE donations SET 
                    status = '$new_status', 
                    admin_notes = '$admin_notes',
                    last_updated = NOW()
                    WHERE id = $donation_id";
                    
    if ($conn->query($update_query) === TRUE) {
        $success_message = "Donation status updated successfully!";
    } else {
        $error_message = "Error updating status: " . $conn->error;
    }
}

// Handle donation deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $donation_id = (int)$_GET['delete'];
    
    // Get confirmation
    if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        $delete_query = "DELETE FROM donations WHERE id = $donation_id";
        
        if ($conn->query($delete_query) === TRUE) {
            $success_message = "Donation record deleted successfully!";
        } else {
            $error_message = "Error deleting record: " . $conn->error;
        }
    } else {
        // Show confirmation dialog via JavaScript
        echo "<script>
            if (confirm('Are you sure you want to delete this donation record? This action cannot be undone.')) {
                window.location.href = 'manage_donation.php?delete=$donation_id&confirm=yes';
            } else {
                window.location.href = 'manage_donation.php';
            }
        </script>";
    }
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Filtering options
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? $conn->real_escape_string($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? $conn->real_escape_string($_GET['date_to']) : '';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Build the query with filters
$where_clauses = [];
if ($status_filter) {
    $where_clauses[] = "status = '$status_filter'";
}
if ($date_from) {
    $where_clauses[] = "donation_date >= '$date_from'";
}
if ($date_to) {
    $where_clauses[] = "donation_date <= '$date_to'";
}
if ($search) {
    $where_clauses[] = "(donor_name LIKE '%$search%' OR donor_email LIKE '%$search%' OR donation_id LIKE '%$search%')";
}

$where_clause = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total records for pagination
$count_query = "SELECT COUNT(*) as total FROM donations $where_clause";
$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get donations with pagination and filters
$donations_query = "SELECT * FROM donations $where_clause 
                   ORDER BY donation_date DESC 
                   LIMIT $offset, $records_per_page";
$donations_result = $conn->query($donations_query);

// Get donation statistics
$stats_query = "SELECT 
                COUNT(*) as total_donations,
                SUM(amount) as total_amount,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_donations,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_amount,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_donations,
                AVG(amount) as average_donation
                FROM donations";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get monthly donation totals for chart
$monthly_query = "SELECT 
                  DATE_FORMAT(donation_date, '%Y-%m') as month,
                  SUM(amount) as total
                  FROM donations
                  WHERE status = 'completed'
                  GROUP BY DATE_FORMAT(donation_date, '%Y-%m')
                  ORDER BY month DESC
                  LIMIT 12";
$monthly_result = $conn->query($monthly_query);
$monthly_data = [];
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data[] = $row;
}
// Reverse for chronological order in chart
$monthly_data = array_reverse($monthly_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Donations - Yedire Frewoch</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .status-badge {
            font-size: 0.85rem;
            padding: 0.35em 0.65em;
        }
        .donation-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .filter-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
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
                    <h2><i class="fas fa-hand-holding-usd mr-2"></i> Manage Donations</h2>
                    <div>
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#bankInfoModal">
                            <i class="fas fa-university"></i> Manage Bank Accounts
                        </button>
                    </div>
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
    
    <!-- Bank Accounts Section -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-university mr-2"></i> Bank Accounts</h5>
            <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#bankInfoModal">
                <i class="fas fa-plus"></i> Add New Bank Account
            </button>
        </div>
        <div class="card-body">
            <?php if (empty($bank_accounts)): ?>
                <div class="alert alert-info">
                    No bank accounts have been added yet. Click "Add New Bank Account" to get started.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($bank_accounts as $bank): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <?php if (!empty($bank['bank_image']) && file_exists($bank['bank_image'])): ?>
                                        <img src="<?php echo $bank['bank_image']; ?>" alt="<?php echo htmlspecialchars($bank['bank_name']); ?>" class="img-fluid mb-2" style="max-height: 60px;">
                                    <?php else: ?>
                                        <i class="fas fa-university fa-3x text-primary mb-2"></i>
                                    <?php endif; ?>
                                    <h5 class="card-title"><?php echo htmlspecialchars($bank['bank_name']); ?></h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Account Name:</strong> <?php echo htmlspecialchars($bank['account_name']); ?></p>
                                    <p><strong>Account Number:</strong> <?php echo htmlspecialchars($bank['account_number']); ?></p>
                                    <?php if (!empty($bank['routing_number'])): ?>
                                        <p><strong>Routing Number:</strong> <?php echo htmlspecialchars($bank['routing_number']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($bank['swift_code'])): ?>
                                        <p><strong>SWIFT/BIC Code:</strong> <?php echo htmlspecialchars($bank['swift_code']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($bank['bank_address'])): ?>
                                        <p><strong>Bank Address:</strong> <?php echo htmlspecialchars($bank['bank_address']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($bank['payment_link'])): ?>
                                        <div class="mt-3">
                                            <a href="<?php echo htmlspecialchars($bank['payment_link']); ?>" class="btn btn-success btn-sm" target="_blank">
                                                <i class="fas fa-credit-card"></i> Pay Now
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <div class="btn-group btn-block">
                                        <button type="button" class="btn btn-sm btn-info edit-bank" 
                                                data-id="<?php echo $bank['id']; ?>"
                                                data-bank-name="<?php echo htmlspecialchars($bank['bank_name']); ?>"
                                                data-account-name="<?php echo htmlspecialchars($bank['account_name']); ?>"
                                                data-account-number="<?php echo htmlspecialchars($bank['account_number']); ?>"
                                                data-routing-number="<?php echo htmlspecialchars($bank['routing_number']); ?>"
                                                data-swift-code="<?php echo htmlspecialchars($bank['swift_code']); ?>"
                                                data-bank-address="<?php echo htmlspecialchars($bank['bank_address']); ?>"
                                                data-bank-image="<?php echo htmlspecialchars($bank['bank_image']); ?>"
                                                data-toggle="modal" data-target="#editBankModal">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="manage_donation.php?delete_bank=<?php echo $bank['id']; ?>" 
                                           class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Bank Logo</th>
                                <th>Bank Name</th>
                                <th>Account Name</th>
                                <th>Account Number</th>
                                <th>Routing Number</th>
                                <th>SWIFT Code</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bank_accounts as $bank): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if (!empty($bank['bank_image']) && file_exists($bank['bank_image'])): ?>
                                            <img src="<?php echo $bank['bank_image']; ?>" alt="<?php echo htmlspecialchars($bank['bank_name']); ?>" style="max-height: 40px; max-width: 80px;">
                                        <?php else: ?>
                                            <i class="fas fa-university fa-2x text-primary"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($bank['bank_name']); ?></td>
                                    <td><?php echo htmlspecialchars($bank['account_name']); ?></td>
                                    <td><?php echo htmlspecialchars($bank['account_number']); ?></td>
                                    <td><?php echo htmlspecialchars($bank['routing_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($bank['swift_code'] ?? 'N/A'); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info edit-bank" 
                                                data-id="<?php echo $bank['id']; ?>"
                                                data-bank-name="<?php echo htmlspecialchars($bank['bank_name']); ?>"
                                                data-account-name="<?php echo htmlspecialchars($bank['account_name']); ?>"
                                                data-account-number="<?php echo htmlspecialchars($bank['account_number']); ?>"
                                                data-routing-number="<?php echo htmlspecialchars($bank['routing_number']); ?>"
                                                data-swift-code="<?php echo htmlspecialchars($bank['swift_code']); ?>"
                                                data-bank-address="<?php echo htmlspecialchars($bank['bank_address']); ?>"
                                                data-bank-image="<?php echo htmlspecialchars($bank['bank_image']); ?>"
                                                data-toggle="modal" data-target="#editBankModal">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="manage_donation.php?delete_bank=<?php echo $bank['id']; ?>" 
                                           class="btn btn-sm btn-danger">
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

    <!-- Bank Information Modal -->
    <div class="modal fade" id="bankInfoModal" tabindex="-1" role="dialog" aria-labelledby="bankInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bankInfoModalLabel">Manage Bank Accounts</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Add Bank Form -->
                    <form method="POST" action="manage_donation.php" enctype="multipart/form-data">
                        <input type="hidden" name="add_bank_info" value="1">
                        
                        <div class="form-group">
                            <label for="bank_name">Bank Name</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="account_name">Account Holder Name</label>
                            <input type="text" class="form-control" id="account_name" name="account_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="account_number">Account Number</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="routing_number">Routing Number</label>
                            <input type="text" class="form-control" id="routing_number" name="routing_number">
                        </div>
                        
                        <div class="form-group">
                            <label for="swift_code">SWIFT/BIC Code (for international transfers)</label>
                            <input type="text" class="form-control" id="swift_code" name="swift_code">
                        </div>
                        
                        <div class="form-group">
                            <label for="bank_address">Bank Address</label>
                            <textarea class="form-control" id="bank_address" name="bank_address" rows="2"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_link">Payment Link (for PayPal or other online payment methods)</label>
                            <input type="text" class="form-control" id="payment_link" name="payment_link" placeholder="https://paypal.me/yourorganization">
                            <small class="form-text text-muted">For PayPal, use your PayPal.me link or button code.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="bank_image">Bank Logo</label>
                            <input type="file" class="form-control-file" id="bank_image" name="bank_image" accept="image/*">
                            <small class="form-text text-muted">Upload a logo or image for this bank (optional).</small>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Bank Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Bank Modal -->
    <div class="modal fade" id="editBankModal" tabindex="-1" role="dialog" aria-labelledby="editBankModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBankModalLabel">Edit Bank Account</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="manage_donation.php" enctype="multipart/form-data">
                        <input type="hidden" name="edit_bank_info" value="1">
                        <input type="hidden" name="bank_id" id="edit_bank_id">
                        
                        <div class="form-group">
                            <label for="edit_bank_name">Bank Name</label>
                            <input type="text" class="form-control" id="edit_bank_name" name="bank_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_account_name">Account Holder Name</label>
                            <input type="text" class="form-control" id="edit_account_name" name="account_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_account_number">Account Number</label>
                            <input type="text" class="form-control" id="edit_account_number" name="account_number" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_routing_number">Routing Number</label>
                            <input type="text" class="form-control" id="edit_routing_number" name="routing_number">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_swift_code">SWIFT/BIC Code (for international transfers)</label>
                            <input type="text" class="form-control" id="edit_swift_code" name="swift_code">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_bank_address">Bank Address</label>
                            <textarea class="form-control" id="edit_bank_address" name="bank_address" rows="2"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_payment_link">Payment Link (for PayPal or other online payment methods)</label>
                            <input type="text" class="form-control" id="edit_payment_link" name="payment_link" placeholder="https://paypal.me/yourorganization">
                            <small class="form-text text-muted">For PayPal, use your PayPal.me link or button code.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_bank_image">Bank Logo</label>
                            <div id="current_bank_image" class="mb-2"></div>
                            <input type="file" class="form-control-file" id="edit_bank_image" name="bank_image" accept="image/*">
                            <small class="form-text text-muted">Upload a new logo or image for this bank (optional).</small>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Bank Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    
        <script>
            $(document).ready(function() {
                // Initialize date range picker
                $('#daterange').daterangepicker({
                    opens: 'left',
                    autoUpdateInput: false,
                    locale: {
                        cancelLabel: 'Clear'
                    }
                });
                
                $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                    $('#date_from').val(picker.startDate.format('YYYY-MM-DD'));
                    $('#date_to').val(picker.endDate.format('YYYY-MM-DD'));
                });
                
                $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    $('#date_from').val('');
                    $('#date_to').val('');
                });
                
                // View donation details
                $('.view-details').click(function() {
                    var id = $(this).data('id');
                    var name = $(this).data('name');
                    var email = $(this).data('email');
                    var phone = $(this).data('phone');
                    var amount = $(this).data('amount');
                    var date = $(this).data('date');
                    var method = $(this).data('method');
                    var status = $(this).data('status');
                    var notes = $(this).data('notes');
                    var message = $(this).data('message');
                    var address = $(this).data('address');
                    var transaction = $(this).data('transaction');
                    
                    $('#modal-donation-id').val(id);
                    $('#modal-donor-name').text(name);
                    $('#modal-donor-email').text(email);
                    $('#modal-donor-phone').text(phone || 'Not provided');
                    $('#modal-donor-address').text(address || 'Not provided');
                    $('#modal-amount').text(amount);
                    $('#modal-date').text(date);
                    $('#modal-method').text(method);
                    $('#modal-transaction').text(transaction || 'Not available');
                    $('#modal-message').text(message || 'No message provided');
                    $('#modal-notes').val(notes || '');
                    $('#modal-status').val(status);
                    
                    $('#donationDetailsModal').modal('show');
                });
                
                // Bank account management
                $('#addBankBtn').click(function() {
                    $('#addBankForm').show();
                    $('#editBankForm').hide();
                });
                
                $('#cancelAddBank').click(function() {
                    $('#addBankForm').hide();
                });
                
                $('.edit-bank').click(function() {
                    var id = $(this).data('id');
                    var bankName = $(this).data('bank-name');
                    var accountName = $(this).data('account-name');
                    var accountNumber = $(this).data('account-number');
                    var routingNumber = $(this).data('routing-number');
                    var swiftCode = $(this).data('swift-code');
                    var bankAddress = $(this).data('bank-address');
                    var bankImage = $(this).data('bank-image');
                    
                    $('#edit_bank_id').val(id);
                    $('#edit_bank_name').val(bankName);
                    $('#edit_account_name').val(accountName);
                    $('#edit_account_number').val(accountNumber);
                    $('#edit_routing_number').val(routingNumber);
                    $('#edit_swift_code').val(swiftCode);
                    $('#edit_bank_address').val(bankAddress);
                    
                    // Display current bank image if available
                    if (bankImage) {
                        $('#current_bank_image').html('<img src="' + bankImage + '" alt="Current Bank Logo" style="max-height: 100px; max-width: 200px;"><p class="mt-1 mb-0 small">Current logo</p>');
                    } else {
                        $('#current_bank_image').html('<p class="text-muted">No logo currently uploaded</p>');
                    }
                });
                
                // Initialize donation chart
                var ctx = document.getElementById('donationChart').getContext('2d');
                var months = <?php echo json_encode(array_column($monthly_data, 'month')); ?>;
                var amounts = <?php echo json_encode(array_column($monthly_data, 'total')); ?>;
                
                // Format months for display (YYYY-MM to MMM YYYY)
                var formattedMonths = months.map(function(month) {
                    var parts = month.split('-');
                    var date = new Date(parts[0], parts[1] - 1, 1);
                    return date.toLocaleString('default', { month: 'short' }) + ' ' + parts[0];
                });
                
                var donationChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: formattedMonths,
                        datasets: [{
                            label: 'Donation Amount ($)',
                            data: amounts,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                            pointRadius: 4,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Total: $' + parseFloat(context.raw).toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
</body>
</html>