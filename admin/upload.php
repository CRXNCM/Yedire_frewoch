<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$school_id = $title = $description = "";
$school_id_err = $title_err = $image_err = "";
$success_msg = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate school
    if(empty(trim($_POST["school_id"]))){
        $school_id_err = "Please select a school.";
    } else{
        $school_id = trim($_POST["school_id"]);
    }
    
    // Validate title
    if(empty(trim($_POST["title"]))){
        $title_err = "Please enter a title.";
    } else{
        $title = trim($_POST["title"]);
    }
    
    // Get description (optional)
    $description = trim($_POST["description"]);
    
    // Validate image
    if(empty($_FILES["image"]["name"])){
        $image_err = "Please select an image to upload.";
    } else {
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        
        // Check file extension
        if(!in_array($file_extension, $allowed_types)){
            $image_err = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
        
        // Check file size (2MB max)
        if($_FILES["image"]["size"] > 2097152){
            $image_err = "File size must be less than 2MB.";
        }
    }
    
    // Check input errors before inserting into database
    if(empty($school_id_err) && empty($title_err) && empty($image_err)){
        
        // Create upload directory if it doesn't exist
        $upload_dir = "../images/gallery/";
        if(!is_dir($upload_dir)){
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;
        $db_path = "images/gallery/" . $filename;
        
        // Upload the file
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $upload_path)){
            
            // Prepare an insert statement
            $sql = "INSERT INTO gallery_images (school_id, title, description, image_path) VALUES (?, ?, ?, ?)";
            
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "isss", $param_school_id, $param_title, $param_description, $param_image_path);
                
                // Set parameters
                $param_school_id = $school_id;
                $param_title = $title;
                $param_description = $description;
                $param_image_path = $db_path;
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    $success_msg = "Image uploaded successfully!";
                    // Clear form data
                    $school_id = $title = $description = "";
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }
        } else {
            $image_err = "Error uploading file. Please try again.";
        }
    }
}

// Get schools for dropdown
$schools = [];
$sql = "SELECT id, name, region FROM schools ORDER BY region, name";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $schools[] = $row;
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
    <title>Upload Image - Yedire Frewoch</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .upload-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
        }
        .form-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="upload-container">
            <div class="logo">
                <img src="../images/logo-default-207x51.png" alt="Yedire Frewoch Logo">
            </div>
            <h2 class="text-center mb-4">Upload Gallery Image</h2>
            
            <?php 
            if(!empty($success_msg)){
                echo '<div class="alert alert-success">' . $success_msg . '</div>';
            }        
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="school_id">Select School</label>
                    <select name="school_id" id="school_id" class="form-control <?php echo (!empty($school_id_err)) ? 'is-invalid' : ''; ?>">
                        <option value="">-- Select School --</option>
                        <?php foreach($schools as $school): ?>
                        <option value="<?php echo $school['id']; ?>" <?php echo ($school_id == $school['id']) ? 'selected' : ''; ?>>
                            <?php echo $school['name']; ?> (<?php echo $school['region']; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="invalid-feedback"><?php echo $school_id_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label for="title">Image Title</label>
                    <input type="text" name="title" id="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
                    <span class="invalid-feedback"><?php echo $title_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"><?php echo $description; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Select Image</label>
                    <input type="file" name="image" id="image" class="form-control-file <?php echo (!empty($image_err)) ? 'is-invalid' : ''; ?>">
                    <small class="form-text text-muted">Recommended size: 1200x800 pixels. Max file size: 2MB.</small>
                    <span class="invalid-feedback"><?php echo $image_err; ?></span>
                </div>
                
                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
                    <input type="submit" class="btn btn-primary" value="Upload Image">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
