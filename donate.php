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

// Get all bank accounts
$bank_accounts = [];
$bank_query = "SELECT * FROM bank_info ORDER BY bank_name";
$bank_result = $conn->query($bank_query);

if ($bank_result && $bank_result->num_rows > 0) {
    while ($row = $bank_result->fetch_assoc()) {
        $bank_accounts[] = $row;
    }
}
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">
  <head>
    <title>Donate - Helper Charity</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Poppins:300,300i,400,500,600,700,800,900,900i%7CRoboto:400%7CRubik:100,400,700">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body>
    <!-- Header Section -->
    <header class="section page-header">
      <!-- RD Navbar-->
      <div class="rd-navbar-wrap">
        <nav class="rd-navbar rd-navbar-classic" data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed" data-md-layout="rd-navbar-fixed" data-md-device-layout="rd-navbar-fixed" data-lg-layout="rd-navbar-static" data-lg-device-layout="rd-navbar-static" data-xl-layout="rd-navbar-static" data-xl-device-layout="rd-navbar-static" data-xxl-layout="rd-navbar-static" data-xxl-device-layout="rd-navbar-static" data-lg-stick-up-offset="46px" data-xl-stick-up-offset="46px" data-xxl-stick-up-offset="46px" data-lg-stick-up="true" data-xl-stick-up="true" data-xxl-stick-up="true">
          <div class="rd-navbar-main-outer">
            <div class="rd-navbar-main">
              <!-- RD Navbar Panel-->
              <div class="rd-navbar-panel">
                <!-- RD Navbar Toggle-->
                <button class="rd-navbar-toggle" data-rd-navbar-toggle=".rd-navbar-nav-wrap"><span></span></button>
                <!-- RD Navbar Brand-->
                <div class="rd-navbar-brand"><a href="index.html"><img class="brand-logo-light" src="images/logo-white.png" alt="" width="207" height="51"/></a></div>
              </div>
              <div class="rd-navbar-main-element">
                <div class="rd-navbar-nav-wrap">
                  <!-- RD Navbar Nav-->
                  <ul class="rd-navbar-nav">
                    <li class="rd-nav-item"><a class="rd-nav-link" href="index.html">Home</a></li>
                    <li class="rd-nav-item"><a class="rd-nav-link" href="gallery.html">Gallery</a></li>
                    <li class="rd-nav-item active"><a class="rd-nav-link" href="programs.html">Programs</a></li>
                        <li class="rd-nav-item"><a class="rd-nav-link" href="get-involved.html">Get Involved</a></li>
                    <li class="rd-nav-item"><a class="rd-nav-link" href="about-us.html">About Us</a></li>
                    <li class="rd-nav-item"><a class="rd-nav-link" href="contacts.html">Contacts</a></li>
                  </ul><a class="button button-primary button-sm" href="donate.html">Donate</a>
                </div>
              </div><a class="button button-primary button-sm" href="donate.html">Donate</a>
            </div>
          </div>
        </nav>
      </div>
    </header>
<!-- Bank Account Information Section -->
<section class="section section-lg bg-default">
            <div class="container">
            <div class="row justify-content-center text-center">
          <div class="col-md-9 col-lg-7 wow-outer">
            <div class="wow slideInDown">
              <h3>Make a Donation</h3>
              <p>Your contribution helps us continue our mission to help children in need around the world.</p>
            </div>
          </div>
        </div>
              <div class="row justify-content-center">
                <div class="col-lg-12 wow-outer">
                  <div class="wow fadeInUp">
                    <h3 class="text-center mb-5">Our Bank Accounts</h3>
                    
                    <div class="row">
                      <?php if (!empty($bank_accounts)): ?>
                        <?php foreach ($bank_accounts as $bank): ?>
                          <div class="col-md-6 mb-5">
                            <div class="card donation-card border-0 shadow-lg h-100">
                              <div class="card-header text-center border-0 pt-4 pb-0 bg-transparent">
                                <div class="bank-icon-wrapper mb-3">
                                  <?php if (!empty($bank['bank_image']) && file_exists($bank['bank_image'])): ?>
                                    <img src="<?php echo $bank['bank_image']; ?>" alt="<?php echo htmlspecialchars($bank['bank_name']); ?>" width="60">
                                  <?php else: ?>
                                    <img src="images/icons/local-bank.png" alt="Bank" width="60">
                                  <?php endif; ?>
                                </div>
                                <h4 class="text-primary"><?php echo htmlspecialchars($bank['bank_name']); ?></h4>
                              </div>
                              <div class="card-body text-center">
                                <div class="bank-details">
                                  <div class="bank-detail-item">
                                    <span class="detail-label">Account Name</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($bank['account_name']); ?></span>
                                  </div>
                                  <div class="bank-detail-item">
                                    <span class="detail-label">Account Number</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($bank['account_number']); ?></span>
                                  </div>
                                  <?php if (!empty($bank['routing_number'])): ?>
                                  <div class="bank-detail-item">
                                    <span class="detail-label">Routing Number</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($bank['routing_number']); ?></span>
                                  </div>
                                  <?php endif; ?>
                                  <?php if (!empty($bank['swift_code'])): ?>
                                  <div class="bank-detail-item">
                                    <span class="detail-label">Swift Code</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($bank['swift_code']); ?></span>
                                  </div>
                                  <?php endif; ?>
                                  <?php if (!empty($bank['bank_address'])): ?>
                                  <div class="bank-detail-item">
                                    <span class="detail-label">Bank Address</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($bank['bank_address']); ?></span>
                                  </div>
                                  <?php endif; ?>
                                </div>
                                <div class="mt-4">
                                  <a href="#" class="button button-primary button-sm copy-details" data-bank="<?php echo htmlspecialchars($bank['bank_name']); ?>">Copy Details</a>
                                </div>
                              </div>
                              <div class="card-footer bg-transparent border-0 text-center pb-4">
                                <span class="badge badge-primary">Bank Transfer</span>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <div class="col-12">
                          <div class="donation-note text-center p-4 shadow-sm rounded">
                            <div class="row align-items-center">
                              <div class="col-md-2 d-none d-md-block">
                                <img src="images/icons/info.png" alt="Information" width="80">
                              </div>
                              <div class="col-md-10">
                                <h5>No Bank Accounts Available</h5>
                                <p class="mb-0">Please use other donation methods or contact us directly at <a href="mailto:donations@helper.org">donations@helper.org</a></p>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>
                    
                    <!-- Additional Information -->

                  </div>
                </div>
              </div>
            </div>
          </section>
                
      
          </div>

          

    <!-- Footer Section -->
    <footer class="section footer-minimal context-dark">
      <div class="container wow-outer">
        <div class="wow fadeIn">
          <div class="row row-50 row-lg-60">
            <div class="col-12"><a href="index.html"><img src="images/logo-white.png" alt="" width="207" height="51"/></a></div>
            <div class="col-12">
              <ul class="footer-minimal-nav">
                <li><a href="about-us.html">About Us</a></li>
                <li><a href="#">Causes</a></li>
                <li><a href="gallery.html">Gallery</a></li>
                <li><a href="#">Team</a></li>
                <li><a href="contacts.html">Contacts</a></li>
              </ul>
            </div>
            <div class="col-12">
              <ul class="social-list">
                <li><a class="icon icon-sm icon-circle icon-circle-md icon-bg-white fa-facebook" href="#"></a></li>
                <li><a class="icon icon-sm icon-circle icon-circle-md icon-bg-white fa-instagram" href="#"></a></li>
                <li><a class="icon icon-sm icon-circle icon-circle-md icon-bg-white fa-twitter" href="#"></a></li>
                <li><a class="icon icon-sm icon-circle icon-circle-md icon-bg-white fa-youtube-play" href="#"></a></li>
                <li><a class="icon icon-sm icon-circle icon-circle-md icon-bg-white fa-pinterest-p" href="#"></a></li>
              </ul>
            </div>
          </div>
          <p class="rights"><span>© </span><span class="copyright-year"></span><span> </span><span>Helper</span><span>. </span><span>All Rights Reserved.</span></p>
        </div>
      </div>
    </footer>

    <div class="snackbars" id="form-output-global"></div>
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
  </body>
</html>
