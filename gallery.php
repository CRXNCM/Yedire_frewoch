<?php
// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'yedire_frewoch';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all images with school information
$images_query = "SELECT i.*, s.name as school_name 
                FROM school_images i 
                JOIN schools s ON i.school_id = s.school_id 
                ORDER BY i.upload_date DESC";
$images_result = $conn->query($images_query);

$gallery_images = [];
if ($images_result && $images_result->num_rows > 0) {
    while ($row = $images_result->fetch_assoc()) {
        $gallery_images[] = $row;
    }
}

// Fetch all schools
$schools_query = "SELECT * FROM schools ORDER BY name ASC";
$schools_result = $conn->query($schools_query);

$schools = [];
if ($schools_result && $schools_result->num_rows > 0) {
    while ($row = $schools_result->fetch_assoc()) {
        $schools[] = $row;
    }
}
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">
  <head>
    <title>Gallery - Yedire Frewoch</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Poppins:300,300i,400,500,600,700,800,900,900i%7CRoboto:400%7CRubik:100,400,700">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
      .location-tabs {
        margin-bottom: 30px;
      }
      .location-tabs .nav-link {
        border-radius: 0;
        padding: 10px 15px;
        font-weight: 500;
        transition: all 0.3s ease;
      }
      .location-tabs .nav-link.active {
        background-color: #756aee;
        color: #fff;
      }
      
      /* Gallery layout styles */
      .gallery-section {
        padding: 60px 0;
      }
      
      .gallery-card {
        margin-bottom: 30px;
        transition: all 0.3s ease;
        height: 100%;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      }
      
      .gallery-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
      }
      
      .thumbnail-classic-figure {
        overflow: hidden;
        position: relative;
        padding-bottom: 66.67%; /* 3:2 aspect ratio for landscape images */
        height: 0;
      }
      
      .thumbnail-classic-figure img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
      }
      
      .gallery-card:hover .thumbnail-classic-figure img {
        transform: scale(1.05);
      }
      
      .card-body {
        padding: 15px;
      }
      
      .card-title {
        font-size: 18px;
        margin-bottom: 8px;
        font-weight: 600;
      }
      
      .card-text {
        font-size: 14px;
        margin-bottom: 15px;
        color: #6c757d;
      }
      
      /* School sidebar styles - Updated with donate button color theme */
      .schools-sidebar {
        background-color: #ffffff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        position: sticky;
        top: 100px;
        transition: all 0.3s ease;
        border-left: 4px solid #756aee;
      }
      
      .schools-sidebar h4 {
        font-size: 20px;
        color: #756aee;
        margin-bottom: 20px;
        font-weight: 600;
        position: relative;
        padding-bottom: 12px;
      }
      
      .schools-sidebar h4:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 40px;
        height: 3px;
        background-color: #756aee;
        transition: width 0.3s ease;
      }
      
      .schools-sidebar:hover h4:after {
        width: 60px;
      }
      
      .school-list {
        list-style: none;
        padding: 0;
        margin: 0;
        max-height: 500px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #756aee #f8f9fa;
      }
      
      .school-list::-webkit-scrollbar {
        width: 5px;
      }
      
      .school-list::-webkit-scrollbar-track {
        background: #f8f9fa;
        border-radius: 10px;
      }
      
      .school-list::-webkit-scrollbar-thumb {
        background-color: #756aee;
        border-radius: 10px;
      }
      
      .school-list li {
        margin-bottom: 8px;
      }
      
      .school-list li:last-child {
        margin-bottom: 0;
      }
      
      .school-list a {
        color: #555;
        display: block;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        position: relative;
        overflow: hidden;
        z-index: 1;
        background-color: #f8f9fa;
        border-left: 2px solid transparent;
      }
      
      .school-list a:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 3px;
        height: 100%;
        background-color: #756aee;
        transition: width 0.3s ease;
        z-index: -1;
      }
      
      .school-list a:hover:before, .school-list a.active:before {
        width: 100%;
      }
      
      .school-list a:hover, .school-list a.active {
        color: #fff;
        text-decoration: none;
        transform: translateX(5px);
        box-shadow: 0 4px 10px rgba(117, 106, 238, 0.2);
      }
      
      .school-list li {
        padding: 4px 0;
        border-bottom: 1px solid #e9ecef;
      }
      
      .school-list li:last-child {
        border-bottom: none;
      }
      
      .school-list a {
        color: #495057;
        display: block;
        transition: all 0.3s ease;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 15px;
      }
      
      .school-list a:hover, .school-list a.active {
        background-color: #756aee;
        color: #fff;
        text-decoration: none;
      }
      
      /* School gallery display */
      .school-gallery {
        display: none;
        animation: fadeIn 0.5s ease;
      }
      
      .school-gallery.active {
        display: block;
      }
      
      .gallery-category-title {
        margin: 0 0 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #756aee;
        font-weight: 600;
        color: #333;
      }
      
      .school-description {
        margin-bottom: 25px;
        font-size: 16px;
        line-height: 1.6;
      }
      
      /* Gallery grid layout for 5+ images */
      .gallery-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-gap: 20px;
      }
      
      /* Responsive adjustments */
      @media (max-width: 1199.98px) {
        .gallery-grid {
          grid-template-columns: repeat(2, 1fr);
        }
      }
      
      @media (max-width: 767.98px) {
        .gallery-grid {
          grid-template-columns: 1fr;
        }
        
        .schools-sidebar {
          position: relative;
          top: 0;
          margin-bottom: 30px;
        }
        
        .school-list {
          max-height: 300px;
        }
      }
      
      /* Animation */
      @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
      }
      
      /* Lightbox improvements */
      .modal-xl {
        max-width: 90%;
      }
      
      #lightboxImage {
        max-height: 80vh;
        object-fit: contain;
      }

    </style>
  </head>
  <body>
    <site-header></site-header>

<!-- Breadcrumbs -->
<section class="parallax-container" style="background-image: url('images/bg-breadcrumbs-gallery.png'); background-size: cover; background-position: center;">
  <div class="parallax-content breadcrumbs-custom context-dark">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
          <h2 class="breadcrumbs-custom-title">Gallery</h2>
          <ul class="breadcrumbs-custom-path">
            <li><a href="index.html">Home</a></li>
            <li class="active">Gallery</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

    <!-- Gallery Section with Sidebar -->
    <section class="section section-lg bg-default">
      <div class="container">
        <div class="row row-30 justify-content-center">
          <div class="col-md-10 col-lg-6 col-xl-5">
            <h3 class="wow-outer"><span class="wow slideInDown">Morning Meal Program</span></h3>
          </div>
          <div class="col-md-10 col-lg-6 col-xl-7">
            <div class="wow-outer">
              <p class="wow slideInDown">Our gallery showcases the impact of our morning voluntary feeding program across different schools, providing nutritious meals to children in need.</p>
            </div>
          </div>
        </div>

        <div class="row mt-5">
          <!-- Sidebar with School Names - Modernized -->
          <div class="col-lg-3">
            <div class="schools-sidebar">
              <h4>Our Schools</h4>
              <ul class="school-list" id="schoolList">
                <?php foreach ($schools as $index => $school): ?>
                <li>
                  <a href="#" class="school-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                     data-school="school<?php echo $school['school_id']; ?>">
                    <?php echo htmlspecialchars($school['name']); ?>
                  </a>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
          
          <!-- Gallery Content Area -->
          <div class="col-lg-9">
            <?php foreach ($schools as $index => $school): 
              // Get all images for this school
              $images_query = "SELECT * FROM school_images WHERE school_id = '{$school['school_id']}' ORDER BY is_featured DESC, upload_date DESC";
              $images_result = $conn->query($images_query);
              $images = [];
              
              if ($images_result && $images_result->num_rows > 0) {
                  while ($row = $images_result->fetch_assoc()) {
                      $images[] = $row;
                  }
              }
            ?>
            <div id="school<?php echo $school['school_id']; ?>" class="school-gallery <?php echo $index === 0 ? 'active' : ''; ?>">
              <h4 class="gallery-category-title"><?php echo htmlspecialchars($school['name']); ?></h4>
              <p class="school-description"><?php echo htmlspecialchars($school['description']); ?></p>
              
              <?php if (empty($images)): ?>
                <div class="text-center py-5">
                  <p>No images available for this school.</p>
                </div>
              <?php else: ?>
                <div class="row gallery-images">
                  <?php foreach ($images as $image): ?>
                    <div class="col-sm-6 col-lg-4">
                      <div class="card gallery-card shadow-sm">
                        <div class="thumbnail-classic-figure">
                          <img src="../images/<?php echo $school['school_id']; ?>/<?php echo $image['image_name']; ?>" 
                               alt="<?php echo htmlspecialchars($image['title'] ?: $school['name']); ?>" 
                               class="card-img-top" width="370" height="250"
                               onerror="this.src='../images/placeholder.jpg'">
                        </div>
                        <div class="card-body">
                          <h5 class="card-title"><?php echo htmlspecialchars($image['title'] ?: 'School Image'); ?></h5>
                          <a class="button button-sm button-primary gallery-image" 
                             href="../images/<?php echo $school['school_id']; ?>/<?php echo $image['image_name']; ?>" 
                             data-title="<?php echo htmlspecialchars($school['name'] . ' - ' . ($image['title'] ?: 'School Image')); ?>">
                             View Larger
                          </a>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <!-- Image Lightbox -->
    <div class="modal fade" id="imageLightbox" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="lightboxTitle">Image Preview</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body text-center">
            <img id="lightboxImage" src="" alt="Lightbox Image" class="img-fluid">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <a id="downloadImageBtn" href="#" class="btn btn-primary" download>Download Image</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Page Footer - Using Web Component -->
    <site-footer></site-footer>

    <div class="snackbars" id="form-output-global"></div>
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
    <!-- Add the components script -->
    <script src="js/components.js"></script>
    
    <!-- Gallery Functionality Script -->
    <script>
      $(document).ready(function() {
        // School selection functionality with enhanced animations
        $('.school-link').on('click', function(e) {
          e.preventDefault();
          
          // Remove active class from all links and hide all galleries
          $('.school-link').removeClass('active');
          $('.school-gallery').removeClass('active').fadeOut(300);
          
          // Add active class to clicked link with animation
          $(this).addClass('active');
          
          // Show the corresponding gallery with fade in animation
          const schoolId = $(this).data('school');
          setTimeout(function() {
            $('#' + schoolId).addClass('active').fadeIn(400);
          }, 300);
          
          // Scroll to gallery on mobile
          if ($(window).width() < 992) {
            $('html, body').animate({
              scrollTop: $('#' + schoolId).offset().top - 100
            }, 500);
          }
        });
        
        // Image lightbox functionality
        $('.gallery-image').on('click', function(e) {
          e.preventDefault();
          
          const imgSrc = $(this).attr('href');
          const imgTitle = $(this).data('title');
          
          $('#lightboxImage').attr('src', imgSrc);
          $('#lightboxTitle').text(imgTitle);
          $('#downloadImageBtn').attr('href', imgSrc);
          
          $('#imageLightbox').modal('show');
        });
        
        // Add hover effect to school list items
        $('.school-list li').hover(
          function() {
            $(this).siblings().css('opacity', '0.7');
          },
          function() {
            $(this).siblings().css('opacity', '1');
          }
        );
      });
    </script>
  </body>
</html>
<?php
// Close database connection
$conn->close();
?>