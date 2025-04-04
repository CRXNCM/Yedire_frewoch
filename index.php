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
    
    // Insert some sample sponsors if the table was just created
    $sample_sponsors = [
        [
            'name' => 'Example Sponsor 1',
            'description' => 'A loyal supporter of our cause',
            'logo_path' => 'images/sponsors/sponsor1.png',
            'website_url' => 'https://example.com'
        ],
        [
            'name' => 'Example Sponsor 2',
            'description' => 'Helping us make a difference',
            'logo_path' => 'images/sponsors/sponsor2.png',
            'website_url' => 'https://example2.com'
        ],
        [
            'name' => 'Example Sponsor 3',
            'description' => 'Supporting education for all',
            'logo_path' => 'images/sponsors/sponsor3.png',
            'website_url' => 'https://example3.com'
        ],
        [
            'name' => 'Example Sponsor 4',
            'description' => 'Building a better future together',
            'logo_path' => 'images/sponsors/sponsor4.png',
            'website_url' => 'https://example4.com'
        ]
    ];
    
    // Create sponsors directory if it doesn't exist
    if (!file_exists('images/sponsors')) {
        mkdir('images/sponsors', 0777, true);
    }
    
    foreach ($sample_sponsors as $sponsor) {
        $insert_sql = "INSERT INTO sponsors (name, description, logo_path, website_url) VALUES (
            '{$sponsor['name']}', 
            '{$sponsor['description']}', 
            '{$sponsor['logo_path']}', 
            '{$sponsor['website_url']}'
        )";
        $conn->query($insert_sql);
    }
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

// Check if testimonials table exists, create if it doesn't
$testimonials_check = $conn->query("SHOW TABLES LIKE 'testimonials'");
if ($testimonials_check->num_rows == 0) {
    // Table doesn't exist, create it
    $create_testimonials_sql = "CREATE TABLE IF NOT EXISTS `testimonials` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `role` varchar(255) DEFAULT NULL,
        `message` text NOT NULL,
        `image_path` varchar(255) DEFAULT NULL,
        `rating` int(1) DEFAULT 5,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->query($create_testimonials_sql);
    
    // Insert some sample testimonials if the table was just created
    $sample_testimonials = [
        [
            'name' => 'Abebe Kebede',
            'role' => 'Volunteer',
            'message' => 'Working with Yedire Frewoch has been one of the most rewarding experiences of my life. The impact we make on children\'s lives is immeasurable.',
            'image_path' => 'images/testimonials/person1.jpg',
            'rating' => 5
        ],
        [
            'name' => 'Sara Hailu',
            'role' => 'Donor',
            'message' => 'I\'ve been supporting this organization for years and have seen firsthand how they transform communities through education and support.',
            'image_path' => 'images/testimonials/person2.jpg',
            'rating' => 5
        ],
        [
            'name' => 'Dawit Mekonnen',
            'role' => 'School Principal',
            'message' => 'The support from Yedire Frewoch has completely transformed our school. Our students now have resources they never had before.',
            'image_path' => 'images/testimonials/person3.jpg',
            'rating' => 5
        ]
    ];
    
    foreach ($sample_testimonials as $testimonial) {
        $insert_sql = "INSERT INTO testimonials (name, role, message, image_path, rating) VALUES (
            '{$testimonial['name']}', 
            '{$testimonial['role']}', 
            '{$testimonial['message']}', 
            '{$testimonial['image_path']}', 
            {$testimonial['rating']}
        )";
        $conn->query($insert_sql);
    }
}

// Get active urgent messages
$urgent_messages = [];
$urgent_query = "SELECT * FROM urgent_messages WHERE status = 'active' ORDER BY urgency_level DESC, created_at DESC";
$urgent_result = $conn->query($urgent_query);

if ($urgent_result && $urgent_result->num_rows > 0) {
    while ($row = $urgent_result->fetch_assoc()) {
        $urgent_messages[] = $row;
    }
}

// Get statistics for counters
$total_students = 0;
$total_schools = 0;
$total_volunteers = 0;
$total_communities = 0;

// Count schools and sum children served
$schools_result = $conn->query("SELECT COUNT(*) as count, SUM(children_served) as students FROM schools");
if ($schools_result && $schools_result->num_rows > 0) {
    $row = $schools_result->fetch_assoc();
    $total_schools = $row['count'];
    $total_students = $row['students'] ?: 0;
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

// Get testimonials
$testimonials = [];
$testimonials_query = "SELECT * FROM testimonials WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6";
$testimonials_result = $conn->query($testimonials_query);

if ($testimonials_result && $testimonials_result->num_rows > 0) {
    while ($row = $testimonials_result->fetch_assoc()) {
        $testimonials[] = $row;
    }
}

// Get sponsors
$sponsors = [];
$sponsors_query = "SELECT * FROM sponsors WHERE is_active = 1 ORDER BY id ASC";
$sponsors_result = $conn->query($sponsors_query);

if ($sponsors_result && $sponsors_result->num_rows > 0) {
    while ($row = $sponsors_result->fetch_assoc()) {
        $sponsors[] = $row;
    }
}

// Get statistics for counters
$total_students = 0;
$total_schools = 0;
$total_volunteers = 0;
$total_communities = 0;

// Count schools and sum children served
$schools_result = $conn->query("SELECT COUNT(*) as count, SUM(children_served) as students FROM schools");
if ($schools_result && $schools_result->num_rows > 0) {
    $row = $schools_result->fetch_assoc();
    $total_schools = $row['count'];
    $total_students = $row['students'] ?: 0;
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

$conn->close();
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">
  <head>
    <title>Home</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Poppins:300,300i,400,500,600,700,800,900,900i%7CRoboto:400%7CRubik:100,400,700">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
      /* Lightbox styles with smooth animations */
      .img-lightbox {
        cursor: pointer;
        display: block;
        position: relative;
        overflow: hidden;
      }
      
      .img-lightbox img {
        transition: transform 0.5s ease;
      }
      
      .img-lightbox:hover img {
        transform: scale(1.05);
      }
      
      .img-lightbox::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.2);
        opacity: 0;
        transition: opacity 0.3s ease;
      }
      
      .img-lightbox:hover::after {
        opacity: 1;
      }
      
      .img-lightbox::before {
        content: '+';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 30px;
        z-index: 1;
        opacity: 0;
        transition: opacity 0.3s ease, transform 0.3s ease;
      }
      
      .img-lightbox:hover::before {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1.2);
      }
      
      /* Modal/Lightbox container with smooth animations */
      .modal-lightbox {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        background-color: rgba(0, 0, 0, 0);
        transition: background-color 0.4s ease;
        opacity: 0;
      }
      
      .modal-lightbox.show {
        background-color: rgba(0, 0, 0, 0.9);
        opacity: 1;
      }
      
      .modal-content {
        margin: auto;
        display: block;
        max-width: 90%;
        max-height: 90%;
        position: relative;
        top: 50%;
        transform: translateY(-50%) scale(0.9);
        opacity: 0;
        transition: opacity 0.5s ease, transform 0.5s ease;
      }
      
      .modal-lightbox.show .modal-content {
        opacity: 1;
        transform: translateY(-50%) scale(1);
      }
      
      .close-lightbox {
        position: absolute;
        top: 20px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        opacity: 0;
        transition: color 0.3s ease, opacity 0.3s ease, transform 0.3s ease;
        cursor: pointer;
        transform: rotate(0deg);
      }
      
      .modal-lightbox.show .close-lightbox {
        opacity: 1;
      }
      
      .close-lightbox:hover,
      .close-lightbox:focus {
        color: #fff;
        text-decoration: none;
        transform: rotate(90deg);
      }
    </style>
    
    
    <style>.ie-panel{display: none;background: #212121;padding: 10px 0;box-shadow: 3px 3px 5px 0 rgba(0,0,0,.3);clear: both;text-align:center;position: relative;z-index: 1;} html.ie-10 .ie-panel, html.lt-ie-10 .ie-panel {display: block;}</style>
    
    <!-- Urgent Message Popup Styles -->
    <style>
    .urgent-popup {
      display: none;
      position: fixed;
      z-index: 10000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.7);
      opacity: 0;
      transition: opacity 0.4s ease;
    }
    
    .urgent-popup.show {
      opacity: 1;
    }
    
    .urgent-popup-content {
      position: relative;
      background-color: #fff;
      margin: 10% auto;
      padding: 0;
      width: 80%;
      max-width: 700px;
      box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
      animation: urgentPopupAnimation 0.4s;
      border-radius: 8px;
      overflow: hidden;
    }
    
    .urgent-popup-header {
      padding: 15px;
      color: white;
      font-weight: bold;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .urgent-popup-header.urgent {
      background-color: #dc3545;
    }
    
    .urgent-popup-header.important {
      background-color: #fd7e14;
    }
    
    .urgent-popup-header.normal {
      background-color: #007bff;
    }
    
    .urgent-popup-body {
      padding: 20px;
    }
    
    .urgent-popup-image {
      width: 100%;
      max-height: 400px;
      object-fit: contain;
      margin-bottom: 15px;
    }
    
    .urgent-popup-close {
      color: white;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      transition: transform 0.3s ease;
    }
    
    .urgent-popup-close:hover {
      transform: rotate(90deg);
    }
    
    .urgent-popup-footer {
      padding: 15px;
      display: flex;
      justify-content: space-between;
      border-top: 1px solid #ddd;
    }
    
    @keyframes urgentPopupAnimation {
      from {transform: scale(0.8); opacity: 0}
      to {transform: scale(1); opacity: 1}
    }
    
    .urgent-popup-btn {
      padding: 8px 16px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.3s ease;
      border: none;
    }
    
    .urgent-popup-btn.primary {
      background-color: #7e179e;
      color: white;
    }
    
    .urgent-popup-btn.primary:hover {
      background-color: #6a1084;
    }
    
    .urgent-popup-btn.secondary {
      background-color: #6c757d;
      color: white;
    }
    
    .urgent-popup-btn.secondary:hover {
      background-color: #5a6268;
    }
    
    /* Sponsors Carousel Styles */
    .sponsors-carousel-container {
      padding: 20px 0;
    }
    
    .sponsor-logo {
      filter: grayscale(100%);
      opacity: 0.7;
      transition: all 0.3s ease;
    }
    
    .sponsor-link:hover .sponsor-logo {
      filter: grayscale(0%);
      opacity: 1;
    }
    
    /* Horizontal Sliding Sponsors */
    .sponsors-slider {
      width: 100%;
      overflow: hidden;
      position: relative;
      padding: 20px 0;
    }
    
    .sponsors-track {
      display: flex;
      animation: sponsorScroll 30s linear infinite;
    }
    
    .sponsor-item {
      flex: 0 0 200px;
      margin: 0 20px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .sponsor-item img {
      max-height: 80px;
      max-width: 100%;
    }
    
    @keyframes sponsorScroll {
      0% {
        transform: translateX(0);
      }
      100% {
        transform: translateX(-50%);
      }
    }
    
    /* Pause animation on hover */
    .sponsors-slider:hover .sponsors-track {
      animation-play-state: paused;
    }
        /* Sponsor Section Styling */
    .sponsors-section .sponsors-slider {
      overflow: hidden;
      position: relative;
    }

    .sponsors-section .sponsors-track {
      display: flex;
      animation: slideSponsors 20s linear infinite;
    }

    .sponsors-section .sponsor-item {
      flex: 0 0 auto;
      margin: 0 15px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .sponsors-section .sponsor-item:hover {
      transform: scale(1.1);
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }

    .sponsors-section .sponsor-logo {
      max-width: 150px;
      height: auto;
      display: block;
    }

    /* Animation for sliding effect */
    @keyframes slideSponsors {
      0% {
        transform: translateX(0);
      }
      100% {
        transform: translateX(-50%);
      }
    }
    .gallery-container {
        margin: 0 -10px;
      }
      
      .gallery-item {
        margin-bottom: 20px;
        padding: 0 10px;
        transition: all 0.3s ease;
      }
      
      .gallery-image-wrapper {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        height: 250px;
      }
      
      .gallery-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
      }
      
      .gallery-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(126, 23, 158, 0.8);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 20px;
        opacity: 0;
        transition: opacity 0.3s ease;
      }
      
      .gallery-image-wrapper:hover .gallery-image {
        transform: scale(1.1);
      }
      
      .gallery-image-wrapper:hover .gallery-overlay {
        opacity: 1;
      }
      
      .gallery-info {
        color: white;
        text-align: left;
      }
      
      .gallery-info h5 {
        margin: 0 0 5px 0;
        font-size: 18px;
        font-weight: 600;
      }
      
      .gallery-info p {
        margin: 0;
        font-size: 14px;
        opacity: 0.9;
      }
      
      .gallery-actions {
        display: flex;
        justify-content: flex-end;
      }
      
      .gallery-action-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        color: #7e179e;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 10px;
        transition: all 0.3s ease;
      }
      
      .gallery-action-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.2);
      }
      
      @media (max-width: 767px) {
        .gallery-image-wrapper {
          height: 200px;
        }
      }
        /* Bento Grid Gallery - Enhanced Version */
  .bento-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-auto-rows: minmax(150px, auto);
    gap: 15px;
    margin: 0 auto;
    max-width: 1200px;
  }
  
  .bento-item {
    position: relative;
    overflow: hidden;
    border-radius: 12px;
    transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }
  
  .bento-item:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 30px rgba(126, 23, 158, 0.2);
    z-index: 5;
  }
  
  /* Optimized size classes for better pattern */
  .bento-item.small {
    grid-column: span 3;
    grid-row: span 1;
  }
  
  .bento-item.medium {
    grid-column: span 4;
    grid-row: span 2;
  }
  
  .bento-item.large {
    grid-column: span 6;
    grid-row: span 2;
  }
  
  .bento-item.wide {
    grid-column: span 6;
    grid-row: span 1;
  }
  
  .bento-item.tall {
    grid-column: span 3;
    grid-row: span 2;
  }
  
  /* Last row items to ensure flat bottom */
  .bento-item.last-row {
    grid-row: span 1 !important;
  }
  
  .bento-image-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
    transition: all 0.4s ease;
  }
  
  .bento-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
    filter: brightness(0.95);
  }
  
  .bento-item:hover .bento-image {
    transform: scale(1.12) rotate(1deg);
    filter: brightness(1.05);
  }
  
  .bento-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to top, rgba(126, 23, 158, 0.95), rgba(126, 23, 158, 0.6), rgba(126, 23, 158, 0.2));
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 25px;
    opacity: 0;
    transition: opacity 0.5s ease;
  }
  
  .bento-item:hover .bento-overlay {
    opacity: 1;
    animation: fadeInOverlay 0.5s forwards;
  }
  
  @keyframes fadeInOverlay {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  
  .bento-info {
    color: white;
    text-align: left;
    transform: translateY(30px);
    opacity: 0;
    transition: all 0.5s ease 0.1s;
    font-family: 'Poppins', sans-serif;
  }
  
  .bento-item:hover .bento-info {
    transform: translateY(0);
    opacity: 1;
    animation: slideUpFade 0.6s forwards;
  }
  
  @keyframes slideUpFade {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }
  
  .bento-info h5 {
    margin: 0 0 8px 0;
    font-size: 20px;
    font-weight: 600;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    letter-spacing: 0.5px;
    position: relative;
    padding-bottom: 10px;
  }
  
  .bento-info h5:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 40px;
    height: 2px;
    background: #fff;
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.5s ease 0.3s;
  }
  
  .bento-item:hover .bento-info h5:after {
    transform: scaleX(1);
  }
  
  .bento-info p {
    margin: 0;
    font-size: 15px;
    opacity: 0.95;
    font-weight: 300;
    letter-spacing: 0.3px;
  }
  
  .bento-actions {
    display: flex;
    justify-content: flex-end;
    transform: translateY(20px);
    opacity: 0;
    transition: all 0.5s ease 0.2s;
  }
  
  .bento-item:hover .bento-actions {
    transform: translateY(0);
    opacity: 1;
    animation: slideUpFadeDelayed 0.5s forwards 0.2s;
  }
  
  @keyframes slideUpFadeDelayed {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }
  
  .bento-action-btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: white;
    color: #7e179e;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 12px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
  }
  
  .bento-action-btn:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #7e179e;
    transform: scale(0);
    border-radius: 50%;
    transition: transform 0.4s ease;
    z-index: -1;
  }
  
  .bento-action-btn:hover {
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 10px 20px rgba(126, 23, 158, 0.3);
    color: white;
  }
  
  .bento-action-btn:hover:before {
    transform: scale(1);
  }
  
  .bento-action-btn i {
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
  }
  
  .bento-action-btn:hover i {
    transform: scale(1.2);
  }
  
  /* Responsive adjustments */
  @media (max-width: 991px) {
    .bento-grid {
      grid-template-columns: repeat(8, 1fr);
    }
    
    .bento-item.small {
      grid-column: span 4;
    }
    
    .bento-item.medium, 
    .bento-item.large {
      grid-column: span 4;
      grid-row: span 2;
    }
    
    .bento-item.wide {
      grid-column: span 8;
    }
    
    .bento-item.tall {
      grid-column: span 4;
      grid-row: span 2;
    }
  }
  
  @media (max-width: 767px) {
    .bento-grid {
      grid-template-columns: repeat(6, 1fr);
    }
    
    .bento-item.small,
    .bento-item.medium {
      grid-column: span 3;
      grid-row: span 1;
    }
    
    .bento-item.large,
    .bento-item.wide {
      grid-column: span 6;
      grid-row: span 1;
    }
    
    .bento-item.tall {
      grid-column: span 3;
      grid-row: span 2;
    }
  }
  
  @media (max-width: 480px) {
    .bento-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    
    .bento-item.small,
    .bento-item.medium,
    .bento-item.tall {
      grid-column: span 1;
      grid-row: span 1;
    }
    
    .bento-item.large,
    .bento-item.wide {
      grid-column: span 2;
      grid-row: span 1;
    }
  }
   /* Modern Testimonial Slider */
   .testimonial-container {
    background: white;
    padding: 40px;
    border-radius: 15px;
    width: 100%;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    position: relative;
    margin: 40px auto;
  }
  
  .testimonial-subtitle {
    color: #7e179e;
    font-size: 14px;
    margin-bottom: 5px;
    font-weight: 500;
  }
  
  .testimonial-title {
    font-size: 26px;
    font-weight: bold;
    position: relative;
    z-index: 2;
    margin-bottom: 30px;
  }
  
  .testimonial-background-text {
    font-size: 60px;
    font-weight: bold;
    color: rgba(126, 23, 158, 0.05);
    position: absolute;
    top: 50px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1;
    white-space: nowrap;
  }
  
  .testimonials-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    margin-top: 40px;
  }
  
  .testimonials-slider {
    display: flex;
    transition: transform 0.7s cubic-bezier(0.25, 0.1, 0.25, 1);
    width: 300%;
  }
  
  .testimonial-slide {
    flex: 0 0 33.33%;
    padding: 25px;
    text-align: center;
    border-radius: 10px;
    background: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.5s cubic-bezier(0.25, 0.1, 0.25, 1);
    opacity: 0.5;
    transform: scale(0.9);
    cursor: pointer;
    border: 2px solid transparent;
  }
  
  .testimonial-slide.active {
    opacity: 1;
    transform: scale(1);
    box-shadow: 0 10px 25px rgba(126, 23, 158, 0.15);
  }
  
  .testimonial-slide:hover {
    transform: translateY(-5px) scale(1.01);
    box-shadow: 0 15px 30px rgba(126, 23, 158, 0.2);
    border-color: rgba(126, 23, 158, 0.1);
  }
  
  .testimonial-slide.active:hover {
    transform: translateY(-5px) scale(1.02);
  }
  
  .testimonial-slide img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin-bottom: 15px;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 5px 15px rgba(126, 23, 158, 0.2);
    transition: all 0.5s ease;
  }
  
  .testimonial-slide:hover img {
    transform: scale(1.1);
    box-shadow: 0 8px 20px rgba(126, 23, 158, 0.3);
  }
  
  .testimonial-slide h3 {
    margin: 0 0 5px;
    font-weight: 600;
    color: #333;
    font-size: 18px;
    transition: color 0.3s ease;
  }
  
  .testimonial-slide:hover h3 {
    color: #7e179e;
  }
  
  .testimonial-slide p.role {
    margin: 0 0 15px;
    color: #7e179e;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
  }
  
  .testimonial-slide:hover p.role {
    letter-spacing: 0.5px;
  }
  
  .testimonial-slide p.message {
    font-size: 15px;
    line-height: 1.6;
    color: #555;
    margin-bottom: 15px;
    font-style: italic;
    transition: all 0.3s ease;
  }
  
  .testimonial-slide:hover p.message {
    color: #333;
  }
  
  .testimonial-stars {
    color: #FFD700;
    margin-bottom: 10px;
    transition: transform 0.3s ease;
  }
  
  .testimonial-slide:hover .testimonial-stars {
    transform: scale(1.1);
  }
  
  .testimonial-navigation {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 20px;
  }
  
  .testimonial-btn {
    background: white;
    border: none;
    font-size: 20px;
    cursor: pointer;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    z-index: 3;
    color: #7e179e;
    transition: all 0.3s ease;
    margin: 0 10px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .testimonial-btn:hover {
    background: #7e179e;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(126, 23, 158, 0.3);
  }
  
  .testimonial-btn:active {
    transform: translateY(0);
    box-shadow: 0 3px 10px rgba(126, 23, 158, 0.2);
  }
  
  .testimonial-dots {
    display: flex;
    justify-content: center;
    margin-top: 20px;
  }
  
  .testimonial-dot {
    width: 10px;
    height: 10px;
    background: #ddd;
    border-radius: 50%;
    margin: 0 5px;
    transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
    cursor: pointer;
    position: relative;
    overflow: hidden;
  }
  
  .testimonial-dot:hover {
    background: #bbb;
    transform: scale(1.2);
  }
  
  .testimonial-dot.active {
    background: #7e179e;
    transform: scale(1.3);
  }
  
  .testimonial-dot:after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(126, 23, 158, 0.5);
    border-radius: 50%;
    transform: scale(0);
    transition: transform 0.3s ease;
  }
  
  .testimonial-dot:hover:after {
    transform: scale(1);
  }
  
  @media (max-width: 767px) {
    .testimonial-container {
      padding: 30px 20px;
    }
    
    .testimonial-background-text {
      font-size: 40px;
      top: 40px;
    }
    
    .testimonial-slide {
      padding: 15px;
    }
  }
    </style>
    
  </head>
  <body>
    <script src="js/core.min.js"></script> 
    <!-- Page Header - Using Web Component -->
    <site-header></site-header>
      
    <!-- Swiper-->
    <section class="section section-lg section-main-bunner section-main-bunner-filter">
      <div class="main-bunner-img" style="background-image: url(&quot;images/bg-bunner-2.png&quot;); background-size: cover;"></div>
      <div class="main-bunner-inner">
        <div class="container">
          <div class="row row-50 justify-content-lg-center align-items-lg-center">
            <div class="col-lg-12">
              <div class="bunner-content-modern text-center">
                <h1 class="main-bunner-title">One for another!</h1>
                <p>Ye Dire Firewoch Charity Association (YDFCA)</p>
                <h1 class="main-bunner-title">አንዳችን ለአንዳችን!</h1>
                <p> የድሬ ፍሬዎች በጎ አድራጎት ድርጅት</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
<!-- Update the gallery section to use the improved bento grid -->
<section class="section section-lg bg-gray-1 our-team">
  <div class="container">
    <div class="row justify-content-center text-center mb-5">
      <div class="col-md-9 col-lg-7 wow-outer">
        <div class="wow slideInDown">
          <h3>Our Gallery</h3>
          <p>Explore moments from our work with schools and communities across Dire Dawa</p>
        </div>
      </div>
    </div>
    
    <div class="bento-grid">
  <?php
  // Create a new database connection for the gallery section
  $gallery_conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
  if ($gallery_conn->connect_error) {
      die("Gallery connection failed: " . $gallery_conn->connect_error);
  }
  
  // Fetch random gallery images from the database
  $gallery_query = "SELECT i.*, s.name as school_name 
                   FROM school_images i 
                   JOIN schools s ON i.school_id = s.school_id 
                   ORDER BY RAND() 
                   LIMIT 12";
  $gallery_result = $gallery_conn->query($gallery_query);
  
  if ($gallery_result && $gallery_result->num_rows > 0) {
    // Define a perfect pattern for a balanced grid with no empty spaces
    $pattern = [
      'large', 'small', 'small', 'medium',  // Row 1-2: 6+3+3+4 = 16 cells (spans 12 columns)
      'small', 'wide', 'small', 'tall',     // Row 3-4: 3+6+3+6 = 18 cells (spans 12 columns)
      'medium', 'small', 'small', 'small'   // Row 5: 4+3+3+2 = 12 cells (spans 12 columns)
    ];
    
    // Get total images
    $total_images = $gallery_result->num_rows;
    
    // Adjust pattern if needed to ensure flat bottom
    if ($total_images > 0) {
      $images = [];
      while ($image = $gallery_result->fetch_assoc()) {
        $images[] = $image;
      }
      
      // Calculate how many items we need for the last row
      $last_row_start = floor($total_images / 4) * 4;
      
      for ($i = 0; $i < $total_images; $i++) {
        $image = $images[$i];
        $image_title = !empty($image['title']) ? $image['title'] : $image['school_name'];
        
        // Use pattern or fallback to small for extras
        $size_class = isset($pattern[$i]) ? $pattern[$i] : 'small';
        
        // Force last row items to be same height for flat bottom
        if ($i >= $last_row_start) {
          $size_class .= ' last-row';
        }
        ?>
                <div class="bento-item <?php echo $size_class; ?>" data-aos="fade-up" data-aos-delay="<?php echo $i * 50; ?>">
          <div class="bento-image-wrapper">
          <img src="../images/<?php echo $image['school_id']; ?>/<?php echo $image['image_name']; ?>" 
       alt="<?php echo htmlspecialchars($image_title); ?>"
       class="bento-image"
       onerror="this.src='images/gallery/placeholder.jpg'"/>
                      
            <div class="bento-overlay">
              <div class="bento-info">
                <h5><?php echo htmlspecialchars($image_title); ?></h5>
                <p><?php echo htmlspecialchars($image['school_name']); ?></p>
              </div>
              <div class="bento-actions">
                <a class="bento-action-btn info-btn" 
                   href="gallery.php#school<?php echo $image['school_id']; ?>">
                  <i class="fa fa-info-circle"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php
      }
    }
  } else {
    // Fallback to static images if no database images
    $fallback_images = [
      ['src' => 'images/gallery/animate-img-1.jpg', 'title' => 'School Support'],
      ['src' => 'images/gallery/animate-img-2.jpg', 'title' => 'Community Outreach'],
      ['src' => 'images/gallery/animate-img-3.jpg', 'title' => 'Education Programs'],
      ['src' => 'images/gallery/animate-img-4.jpg', 'title' => 'Volunteer Activities'],
      ['src' => 'images/gallery/animate-img-1.jpg', 'title' => 'Classroom Activities'],
      ['src' => 'images/gallery/animate-img-2.jpg', 'title' => 'Student Engagement'],
      ['src' => 'images/gallery/animate-img-3.jpg', 'title' => 'Teacher Training'],
      ['src' => 'images/gallery/animate-img-4.jpg', 'title' => 'School Supplies'],
      ['src' => 'images/gallery/animate-img-1.jpg', 'title' => 'Community Events'],
      ['src' => 'images/gallery/animate-img-2.jpg', 'title' => 'Educational Materials'],
      ['src' => 'images/gallery/animate-img-3.jpg', 'title' => 'Student Activities'],
      ['src' => 'images/gallery/animate-img-4.jpg', 'title' => 'School Facilities']
    ];
    
    // Define a perfect pattern for 12 images
    $pattern = [
      'large', 'small', 'small', 'medium',  // Row 1-2: 6+3+3+4 = 16 cells (spans 12 columns)
      'small', 'wide', 'small', 'tall',     // Row 3-4: 3+6+3+6 = 18 cells (spans 12 columns)
      'medium', 'small', 'small', 'small'   // Row 5: 4+3+3+2 = 12 cells (spans 12 columns)
    ];
    
    // Use exactly 12 images for perfect layout
    $fallback_count = min(count($fallback_images), 12);
    
    for ($i = 0; $i < $fallback_count; $i++) {
      $image = $fallback_images[$i];
      
      // Use pattern for perfect layout
      $size_class = isset($pattern[$i]) ? $pattern[$i] : 'small';
      
      // Force last row items to be same height for flat bottom
      if ($i >= 8) {
        $size_class .= ' last-row';
      }
      ?>
<!-- For the database images section -->
<div class="bento-item <?php echo $size_class; ?>" data-aos="fade-up" data-aos-delay="<?php echo $i * 50; ?>">
  <div class="bento-image-wrapper">
  <img src="../images/<?php echo $image['school_id']; ?>/<?php echo $image['image_name']; ?>" 
       alt="<?php echo htmlspecialchars($image_title); ?>"
       class="bento-image"
       onerror="this.src='images/gallery/placeholder.jpg'"/>
  </div>
</div>

      <?php
    }
  }
  
  // Close the gallery connection
  $gallery_conn->close();
  ?>
</div>
    <div class="text-center mt-5">
      <a href="gallery.php" class="button button-primary button-winona">
        <div class="button-winona-text">View Full Gallery</div>
        <span class="button-winona-overlay"></span>
      </a>
    </div>
  </div>
</section>

<!-- Add this in the head section of your document -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <section class="section section-lg bg-default">
      <div class="container">
        <div class="row justify-content-center text-center">
          <div class="col-md-9 col-lg-7 wow-outer">
            <div class="wow slideInDown">
              <h3>Our Impact</h3>
              <p>Since our foundation, we've been committed to making a difference in the lives of children across Ethiopia. Here's how we've helped so far.</p>
            </div>
          </div>
        </div>
        <div class="row row-30 justify-content-center">
          <div class="col-sm-6 col-md-6 col-lg-3 wow-outer">
            <div class="wow fadeInUp">
              <article class="box-counter box-counter-modern">
                <div class="box-counter-main">
                  <div style="color: #7e179ef9; font-size: 3.5rem; font-weight: 700;">
                    <span id="studentsCounter">0</span><span id="studentsPlus" style="display: none;">+</span>
                  </div>
                </div>
                <p class="box-counter-title" style="font-size: 1.5rem;">Students Helped</p>
              </article>
            </div>
          </div>
          <div class="col-sm-6 col-md-6 col-lg-3 wow-outer">
            <div class="wow fadeInUp" data-wow-delay=".1s">
              <article class="box-counter box-counter-modern">
                <div class="box-counter-main">
                  <div style="color: #7e179ef9; font-size: 3.5rem; font-weight: 700;">
                    <span id="schoolsCounter">0</span><span id="schoolsPlus" style="display: none;">+</span>
                  </div>
                </div>
                <p class="box-counter-title" style="font-size: 1.5rem;">Schools Supported</p>
              </article>
            </div>
          </div>
          <div class="col-sm-6 col-md-6 col-lg-3 wow-outer">
            <div class="wow fadeInUp" data-wow-delay=".2s">
              <article class="box-counter box-counter-modern">
                <div class="box-counter-main">
                  <div style="color: #7e179ef9; font-size: 3.5rem; font-weight: 700;">
                    <span id="volunteersCounter">0</span><span id="volunteersPlus" style="display: none;">+</span>
                  </div>
                </div>
                <p class="box-counter-title" style="font-size: 1.5rem;">Volunteers</p>
              </article>
            </div>
          </div>
          <div class="col-sm-6 col-md-6 col-lg-3 wow-outer">
            <div class="wow fadeInUp" data-wow-delay=".3s">
              <article class="box-counter box-counter-modern">
                <div class="box-counter-main">
                  <div style="color: #7e179ef9; font-size: 3.5rem; font-weight: 700;">
                    <span id="communitiesCounter">0</span><span id="communitiesPlus" style="display: none;">+</span>
                  </div>
                </div>
                <p class="box-counter-title" style="font-size: 1.5rem;">Communities Reached</p>
              </article>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Add this after the main banner section -->
    <section class="section section-sm bg-default text-center">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-10 col-lg-8">
            <h3>Support Our Cause</h3>
            <p class="text-spacing-05">Your donation can make a real difference in the lives of those we serve. Every contribution helps us continue our mission.</p>
            <a class="button button-lg button-primary button-winona wow fadeInUp" href="donate.php" data-wow-delay=".2s">
              <div class="button-winona-text">Donate Now</div><span class="button-winona-overlay"></span>
            </a>
          </div>
        </div>
      </div>
    </section>
    <?php if (!empty($testimonials)): ?>
<div class="container">
    <div class="testimonial-container wow fadeInUp">
      <p class="testimonial-subtitle">What People Say</p>
      <h3 class="testimonial-title">Testimonials</h3>
      <div class="testimonial-background-text">Testimonials</div>
      
      <div class="testimonial-navigation">
        <button class="testimonial-btn testimonial-btn-left" onclick="prevTestimonial()">&#10094;</button>
        <button class="testimonial-btn testimonial-btn-right" onclick="nextTestimonial()">&#10095;</button>
      </div>
      
      <div class="testimonials-wrapper">
        <div class="testimonials-slider">
          <?php foreach ($testimonials as $index => $testimonial): ?>
            <div class="testimonial-slide <?php echo ($index === 1) ? 'active' : ''; ?>" onclick="goToTestimonial(<?php echo $index; ?>)">
              <?php if (!empty($testimonial['image_path'])): ?>
                <img src="<?php echo htmlspecialchars($testimonial['image_path']); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>">
              <?php else: ?>
                <img src="images/testimonials/default-avatar.jpg" alt="<?php echo htmlspecialchars($testimonial['name']); ?>">
              <?php endif; ?>
              
              <h3><?php echo htmlspecialchars($testimonial['name']); ?></h3>
              <?php if (!empty($testimonial['role'])): ?>
                <p class="role"><?php echo htmlspecialchars($testimonial['role']); ?></p>
              <?php endif; ?>
              
              <p class="message">"<?php echo htmlspecialchars($testimonial['message']); ?>"</p>
              
              <div class="testimonial-stars">
                <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                  ★
                <?php endfor; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="testimonial-dots">
        <?php foreach ($testimonials as $index => $testimonial): ?>
          <div class="testimonial-dot <?php echo ($index === 1) ? 'active' : ''; ?>" onclick="goToTestimonial(<?php echo $index; ?>)"></div>
        <?php endforeach; ?>
      </div>
    </div>
</div>
<?php endif; ?>

    <!-- Sponsors Section with Horizontal Sliding Effect -->
    <?php if (!empty($sponsors)): ?>
    <section class="section section-lg bg-default">
      <div class="container">
        <div class="row justify-content-center text-center">
          <div class="col-md-9 col-lg-7 wow-outer">
            <div class="wow slideInDown">
              <h3>Our Sponsors</h3>
              <p>We are grateful to these organizations for their generous support of our mission.</p>
            </div>
          </div>
        </div>
        
        <div class="sponsors-slider mt-5">
          <div class="sponsors-track">
            <?php foreach($sponsors as $sponsor): ?>
              <div class="sponsor-item">
                <a href="<?php echo htmlspecialchars($sponsor['website_url']); ?>" target="_blank" class="sponsor-link">
                  <img src="<?php echo htmlspecialchars($sponsor['logo_path']); ?>" 
                       alt="<?php echo htmlspecialchars($sponsor['name']); ?>" 
                       class="img-fluid sponsor-logo" 
                       title="<?php echo htmlspecialchars($sponsor['description']); ?>">
                </a>
              </div>
            <?php endforeach; ?>
            
            <!-- Duplicate sponsors for continuous loop effect -->
            <?php foreach($sponsors as $sponsor): ?>
              <div class="sponsor-item">
                <a href="<?php echo htmlspecialchars($sponsor['website_url']); ?>" target="_blank" class="sponsor-link">
                  <img src="<?php echo htmlspecialchars($sponsor['logo_path']); ?>" 
                       alt="<?php echo htmlspecialchars($sponsor['name']); ?>" 
                       class="img-fluid sponsor-logo" 
                       title="<?php echo htmlspecialchars($sponsor['description']); ?>">
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- Page Footer - Using Web Component -->
    <site-footer></site-footer>
    <?php include 'includes/urgent-message.php'; ?>
    
    <div class="snackbars" id="form-output-global"></div>
    <script src="js/script.js"></script>
    <!-- Add the components script -->
    <script src="js/components.js"></script>
    <script src="js/core.js"></script>

    
    <!-- Add the modal/lightbox container -->
    <div id="lightboxModal" class="modal-lightbox">
      <span class="close-lightbox">&times;</span>
      <img class="modal-content" id="lightboxImage">
    </div>

    <script src="js/lightbox.js"></script>
    
    
    <!-- Urgent Message Popup -->
    <?php if (!empty($urgent_messages)): ?>
    <div id="urgentPopup" class="urgent-popup">
      <div class="urgent-popup-content">
        <div class="urgent-popup-header <?php echo strtolower($urgent_messages[0]['urgency_level']); ?>">
          <h4><?php echo htmlspecialchars($urgent_messages[0]['title']); ?></h4>
          <span class="urgent-popup-close">&times;</span>
        </div>
        <div class="urgent-popup-body">
          <?php if (!empty($urgent_messages[0]['image_path'])): ?>
          <img src="<?php echo htmlspecialchars($urgent_messages[0]['image_path']); ?>" alt="Urgent situation" class="urgent-popup-image">
          <?php endif; ?>
          <div><?php echo nl2br(htmlspecialchars($urgent_messages[0]['message'])); ?></div>
        </div>
        <div class="urgent-popup-footer">
          <?php if (!empty($urgent_messages[0]['action_link'])): ?>
          <a href="<?php echo htmlspecialchars($urgent_messages[0]['action_link']); ?>" class="urgent-popup-btn primary"><?php echo !empty($urgent_messages[0]['action_text']) ? htmlspecialchars($urgent_messages[0]['action_text']) : 'Help Now'; ?></a>
          <?php endif; ?>
          <button class="urgent-popup-btn secondary" id="closeUrgentPopup">Close</button>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <!-- Testimonial Carousel Script -->
        <script>
          document.addEventListener('DOMContentLoaded', function() {
    // Set the counter values from PHP
    const studentsTarget = <?php echo $total_students; ?>;
    const schoolsTarget = <?php echo $total_schools; ?>;
    const volunteersTarget = <?php echo $total_volunteers; ?>;
    const communitiesTarget = <?php echo $total_communities; ?>;
    
    // Function to animate counting up
    function animateCounter(elementId, target, duration = 2000, plusSign = false) {
      const element = document.getElementById(elementId);
      const plusElement = document.getElementById(elementId + 'Plus');
      let startTime = null;
      const startValue = 0;
      
      function step(timestamp) {
        if (!startTime) startTime = timestamp;
        const progress = Math.min((timestamp - startTime) / duration, 1);
        const currentValue = Math.floor(progress * (target - startValue) + startValue);
        element.textContent = currentValue.toLocaleString();
        
        if (progress < 1) {
          window.requestAnimationFrame(step);
        } else {
          element.textContent = target.toLocaleString();
          if (plusSign && plusElement) {
            plusElement.style.display = 'inline';
          }
        }
      }
      
      window.requestAnimationFrame(step);
    }
    
    // Start animations when the section is in view
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter('studentsCounter', studentsTarget, 2000, true);
          animateCounter('schoolsCounter', schoolsTarget, 2000, true);
          animateCounter('volunteersCounter', volunteersTarget, 2000, true);
          animateCounter('communitiesCounter', communitiesTarget, 2000, true);
          observer.disconnect();
        }
      });
    }, { threshold: 0.1 });
    
    const counterSection = document.querySelector('.box-counter').closest('section');
    observer.observe(counterSection);
  });
        </script>
      </body>
    </html>