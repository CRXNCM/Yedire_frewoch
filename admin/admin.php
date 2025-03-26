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

// Get schools for dropdown
$schools = [];
$sql = "SELECT id, name, region FROM schools ORDER BY region, name";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $schools[] = $row;
    }
}

// Get recent uploads
$recent_uploads = [];
$sql = "SELECT g.id, g.title, g.image_path, g.upload_date, s.name as school_name 
        FROM gallery_images g 
        JOIN schools s ON g.school_id = s.id 
        ORDER BY g.upload_date DESC 
        LIMIT 10";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $recent_uploads[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Yedire Frewoch</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #f8f9fa;
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .nav-link {
            font-weight: 500;
            color: #333;
        }
        .nav-link.active {
            color: #756aee;
        }
        main {
            padding-top: 48px;
        }
        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: 1rem;
            background-color: #756aee;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
        }
        .navbar .navbar-toggler {
            top: .25rem;
            right: 1rem;
        }
        .upload-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .recent-uploads {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .thumbnail {
            width: 100px;
            height: 70px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" href="#">Yedire Frewoch Admin</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <ul class="navbar-nav px-3 ml-auto">
            <li class="nav-item text-nowrap">
                <a class="nav-link" href="logout.php">Sign out</a>
            </li>
        </ul>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="sidebar-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="admin.php">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_schools.php">
                                Manage Schools
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_gallery.php">
                                Manage Gallery
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <a href="../gallery.html" target="_blank" class="btn btn-sm btn-outline-secondary">View Gallery</a>
                        </div>
                    </div>
                </div>

                <div class="upload-container">
                    <h4>Upload New Image</h4>
                    <form action="upload.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="school">Select School</label>
                            <select class="form-control" id="school" name="school_id" required>
                                <option value="">-- Select School --</option>
                                <?php foreach($schools as $school): ?>
                                <option value="<?php echo $school['id']; ?>"><?php echo $school['name']; ?> (<?php echo $school['region']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="title">Image Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="image">Select Image</label>
                            <input type="file" class="form-control-file" id="image" name="image" required>
                            <small class="form-text text-muted">Recommended size: 1200x800 pixels. Max file size: 2MB.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload Image</button>
                    </form>
                </div>

                <div class="recent-uploads">
                    <h4>Recent Uploads</h4>
                    <?php if(empty($recent_uploads)): ?>
                        <p>No images have been uploaded yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>School</th>
                                        <th>Upload Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_uploads as $image): ?>
                                    <tr>
                                        <td><img src="<?php echo '../' . $image['image_path']; ?>" class="thumbnail" alt="<?php echo $image['title']; ?>"></td>
                                        <td><?php echo $image['title']; ?></td>
                                        <td><?php echo $image['school_name']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($image['upload_date'])); ?></td>
                                        <td>
                                            <a href="edit_image.php?id=<?php echo $image['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <a href="manage_gallery.php?delete=<?php echo $image['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this image?')">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="manage_gallery.php" class="btn btn-outline-primary">View All Images</a>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="../js/core.min.js"></script>
    <script src="../js/script.js"></script>
</body>
</html>
