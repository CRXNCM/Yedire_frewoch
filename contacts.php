<!DOCTYPE html>
<html class="wide wow-animation" lang="en">
  <head>
    <title>Contacts</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Poppins:300,300i,400,500,600,700,800,900,900i%7CRoboto:400%7CRubik:100,400,700">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css">
    <style>.ie-panel{display: none;background: #212121;padding: 10px 0;box-shadow: 3px 3px 5px 0 rgba(0,0,0,.3);clear: both;text-align:center;position: relative;z-index: 1;} html.ie-10 .ie-panel, html.lt-ie-10 .ie-panel {display: block;}</style>
  </head>
  <body>
    <!-- Page Header - Using Web Component -->
    <site-header></site-header>
    
    <section class="parallax-container" data-parallax-img="images/bg-breadcrumbs-contact.png">
      <div class="parallax-content breadcrumbs-custom context-dark">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
              <h2 class="breadcrumbs-custom-title">Contacts</h2>
              <ul class="breadcrumbs-custom-path">
                <li><a href="index">Home</a></li>
                <li class="active">Contacts</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <section class="section section-lg text-center bg-default">
      <div class="container">
        <div class="row row-50">
          <div class="col-md-6 col-lg-4">
            <div class="box-icon-classic">
              <div class="box-icon-inner decorate-triangle decorate-color-secondary"><span class="icon-xl linearicons-phone-incoming icon-gradient-1"></span></div>
              <div class="box-icon-caption">
                <h4>Telephone: <a href="tel:+251 5-73-61-64">+251 921-310-681</a></h4>
                <h4>Office Tel: <a href="tel:+0254-111-011">+0254-111-011</a></h4>

                <p>You can call us anytime</p>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="box-icon-classic">
              <div class="box-icon-inner decorate-circle decorate-color-secondary-2"><span class="icon-xl linearicons-map2 icon-gradient-2"></span></div>
              <div class="box-icon-caption">
                <h4><a href="https://maps.app.goo.gl/Gx8WwitDvCa1Q8D66">Dire Dawa, Ethiopia Dechatu </a></h4>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="box-icon-classic">
              <div class="box-icon-inner decorate-rectangle decorate-color-primary"><span class="icon-xl linearicons-paper-plane icon-gradient-3"></span></div>
              <div class="box-icon-caption">
                <h4><a href="mailto:yedirefrewoch@.com">yedirefrewoch@gmail.com</a></h4>
                <p>Feel free to email us your questions</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Contact us-->
    <section class="section section-lg bg-gray-1 text-center">
      <div class="container">
        <div class="row justify-content-md-center">
          <div class="col-md-9 col-lg-7">
            <h3>Get in Touch</h3>
            <!-- RD Mailform-->
            <form class="rd-form rd-mailform" data-form-output="form-output-global" data-form-type="contact" method="post" action="bat/rd-mailform.php">
              <div class="form-wrap">
                <input class="form-input" id="contact-name" type="text" name="name" data-constraints="@Required">
                <label class="form-label" for="contact-name">Your Name</label>
              </div>
              <div class="form-wrap">
                <input class="form-input" id="contact-email" type="email" name="email" data-constraints="@Email @Required">
                <label class="form-label" for="contact-email">E-mail</label>
              </div>
              <div class="form-wrap">
                <input class="form-input" id="contact-phone" type="text" name="phone" data-constraints="@Numeric">
                <label class="form-label" for="contact-phone">Phone</label>
              </div>
              <div class="form-wrap">
                <label class="form-label" for="contact-message"> Message</label>
                <textarea class="form-input" id="contact-message" name="message" data-constraints="@Required"></textarea>
              </div>
              <div class="row justify-content-center">
                <div class="col-12 col-sm-7 col-lg-5">
                  <button class="button button-block button-lg button-primary" type="submit">Send</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Page Footer - Using Web Component -->
    <site-footer></site-footer>
    
    <div class="snackbars" id="form-output-global"></div>
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
    <!-- Add the components script -->
    <script src="js/components.js"></script>
  </body>
</html>