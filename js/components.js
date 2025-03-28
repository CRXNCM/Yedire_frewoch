// Header Component
class SiteHeader extends HTMLElement {
  constructor() {
    super();
  }

  connectedCallback() {
    this.innerHTML = `
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
                      <li class="rd-nav-item ${this.isActive('index.html')}"><a class="rd-nav-link" href="index.html">Home</a></li>
                      <li class="rd-nav-item ${this.isActive('about-us.html')}"><a class="rd-nav-link" href="about-us.html">About Us</a></li>
                      <li class="rd-nav-item ${this.isActive('gallery.html')}"><a class="rd-nav-link" href="gallery.html">Gallery</a></li>
                      <li class="rd-nav-item ${this.isActive('programs.html')}"><a class="rd-nav-link" href="programs.html">Programs</a></li>
                      <li class="rd-nav-item ${this.isActive('get-involved.html')}"><a class="rd-nav-link" href="get-involved.html">Get Involved</a></li>
                      <li class="rd-nav-item ${this.isActive('contacts.html')}"><a class="rd-nav-link" href="contacts.html">Contacts</a></li>
                      
                    </ul><a class="button button-primary button-sm" href="donate.html">Donate</a>
                  </div>
                </div><a class="button button-primary button-sm" href="donate.html">Donate</a>
              </div>
            </div>
          </nav>
        </div>
      </header>
    `;
    
    // Reinitialize RD Navbar after the component is connected
    setTimeout(() => {
      if (window.RDNavbar) {
        const navbars = document.querySelectorAll('.rd-navbar');
        for (let i = 0; i < navbars.length; i++) {
          new window.RDNavbar(navbars[i]);
        }
      }
    }, 100);
  }

  isActive(href) {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    return currentPage === href ? 'active' : '';
  }
}

// Footer Component
class SiteFooter extends HTMLElement {
  constructor() {
    super();
  }

  connectedCallback() {
    this.innerHTML = `
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
            <p class="rights"><span>&copy;&nbsp;</span><span class="copyright-year"></span><span>&nbsp;</span><span>Helper</span><span>.&nbsp;</span><span>All Rights Reserved.</span></p>
          </div>
        </div>
      </footer>
    `;
  }
}

// Define the custom elements
customElements.define('site-header', SiteHeader);
customElements.define('site-footer', SiteFooter);