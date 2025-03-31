<?php
// Include config file
require_once "config.php";

// SQL to create admin_users table
$sql_admin_users = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

// SQL to create schools table
$sql_schools = "CREATE TABLE IF NOT EXISTS schools (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    region VARCHAR(50) NOT NULL,
    address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

// SQL to create gallery_images table
$sql_gallery = "CREATE TABLE IF NOT EXISTS gallery_images (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id)
)";

// Execute the SQL statements
$success = true;
$messages = [];

if (mysqli_query($conn, $sql_admin_users)) {
    $messages[] = "Table 'admin_users' created successfully";
} else {
    $success = false;
    $messages[] = "Error creating table 'admin_users': " . mysqli_error($conn);
}

if (mysqli_query($conn, $sql_schools)) {
    $messages[] = "Table 'schools' created successfully";
} else {
    $success = false;
    $messages[] = "Error creating table 'schools': " . mysqli_error($conn);
}

if (mysqli_query($conn, $sql_gallery)) {
    $messages[] = "Table 'gallery_images' created successfully";
} else {
    $success = false;
    $messages[] = "Error creating table 'gallery_images': " . mysqli_error($conn);
}

// Check if admin user exists, if not create default admin
$sql = "SELECT id FROM admin_users WHERE username = 'admin'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    // Create default admin user
    $username = "admin";
    $password = password_hash("admin123", PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO admin_users (username, password) VALUES (?, ?)";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        
        if (mysqli_stmt_execute($stmt)) {
            $messages[] = "Default admin user created successfully. Username: admin, Password: admin123";
        } else {
            $success = false;
            $messages[] = "Error creating default admin user: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Add sample schools if none exist
$sql = "SELECT id FROM schools";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    // Sample school data
    $schools = [
        ['Addis Ababa School', 'Region 1'],
        ['Mekelle Primary School', 'Region 1'],
        ['Bahir Dar Elementary', 'Region 1'],
        ['Gondar Community School', 'Region 2'],
        ['Hawassa Primary', 'Region 2']
    ];
    
    $sql = "INSERT INTO schools (name, region) VALUES (?, ?)";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $name, $region);
        
        foreach ($schools as $school) {
            $name = $school[0];
            $region = $school[1];
            
            mysqli_stmt_execute($stmt);
        }
        
        $messages[] = "Sample schools added successfully";
        mysqli_stmt_close($stmt);
    }
}

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Yedire Frewoch</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <style>
        body {
            padding: 50px;
            background-color: #f8f9fa;
        }
        .setup-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h2 class="text-center mb-4">Yedire Frewoch - Setup</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success">Setup completed successfully!</div>
        <?php else: ?>
            <div class="alert alert-danger">There were errors during setup.</div>
        <?php endif; ?>
        
        <ul class="list-group mb-4">
            <?php foreach ($messages as $message): ?>
                <li class="list-group-item"><?php echo $message; ?></li>
            <?php endforeach; ?>
        </ul>
        
        <div class="text-center">
            <a href="login.php" class="btn btn-primary">Go to Login</a>
        </div>
    </div>
</body>
</html>
