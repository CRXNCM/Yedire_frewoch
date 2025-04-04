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
                  <div class="rd-navbar-brand"><a href="index"><img class="brand-logo-light" src="images/logo-main.png" alt=""  height="71"/></a></div>
                </div>
                <div class="rd-navbar-main-element">
                  <div class="rd-navbar-nav-wrap">
                    <!-- RD Navbar Nav-->
                    <ul class="rd-navbar-nav">
                      <li class="rd-nav-item ${this.isActive('index')}"><a class="rd-nav-link" href="index">Home</a></li>
                      <li class="rd-nav-item ${this.isActive('about-us')}"><a class="rd-nav-link" href="about-us">About Us</a></li>
                      <li class="rd-nav-item ${this.isActive('gallery')}"><a class="rd-nav-link" href="gallery">Gallery</a></li>
                      <li class="rd-nav-item ${this.isActive('contacts')}"><a class="rd-nav-link" href="contacts">Contacts</a></li>
                      
                    </ul><a class="button button-primary button-sm" href="donate">Donate</a>
                  </div>
                </div><a class="button button-primary button-sm" href="donate">Donate</a>
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
    // Update this method to work with clean URLs
    const currentPage = window.location.pathname.split('/').pop() || 'index';
    // Remove .php extension if it exists in the URL
    const cleanCurrentPage = currentPage.replace('.php', '');
    return cleanCurrentPage === href ? 'active' : '';
  }
}

// Footer Component
class SiteFooter extends HTMLElement {
  constructor() {
    super();
    this.footerLinks = [];
    this.socialLinks = [];
  }

  async connectedCallback() {
    // Initial footer rendering with loading state
    this.renderFooter();
    
    // Fetch footer links and social links from the database
    try {
      // Fetch footer links
      const footerResponse = await fetch('api/get_footer_links.php');
      if (!footerResponse.ok) {
        throw new Error(`HTTP error! status: ${footerResponse.status}`);
      }
      this.footerLinks = await footerResponse.json();
      
      // Fetch social links
      const socialResponse = await fetch('api/get_social_links.php');
      if (!socialResponse.ok) {
        throw new Error(`HTTP error! status: ${socialResponse.status}`);
      }
      this.socialLinks = await socialResponse.json();
      
      // Re-render footer with the fetched links
      this.renderFooter();
    } catch (error) {
      console.error('Error fetching links:', error);
      // Use default links if fetch fails
      this.footerLinks = [
        { title: 'About Us', url: 'about-us.php' },
        { title: 'Causes', url: '#' },
        { title: 'Gallery', url: 'gallery.php' },
        { title: 'Team', url: '#' },
        { title: 'Contacts', url: 'contacts.php' }
      ];
      
      this.socialLinks = [
        { platform: 'facebook', url: '#', icon_class: 'fa-facebook' },
        { platform: 'instagram', url: '#', icon_class: 'fa-instagram' },
        { platform: 'twitter', url: '#', icon_class: 'fa-twitter' },
        { platform: 'youtube', url: '#', icon_class: 'fa-youtube-play' }
      ];
      
      this.renderFooter();
    }
  }

  renderFooter() {
    // Generate the footer links HTML
    let linksHtml = '';
    
    if (this.footerLinks.length > 0) {
      this.footerLinks.forEach(link => {
        // Remove .php extension from URLs
        const cleanUrl = link.url.replace('.php', '');
        linksHtml += `<li><a href="${cleanUrl}">${link.title}</a></li>`;
      });
    } else {
      // Default links while loading or if no links found
      linksHtml = `
        <li><a href="about-us">About Us</a></li>
        <li><a href="#">Causes</a></li>
        <li><a href="gallery">Gallery</a></li>
        <li><a href="#">Team</a></li>
        <li><a href="contacts">Contacts</a></li>
      `;
    }
    
    // Generate the social links HTML
    let socialHtml = '';
    
    if (this.socialLinks.length > 0) {
      this.socialLinks.forEach(link => {
        // Extract the icon class (fa-facebook, fa-instagram, etc.)
        const iconClass = link.icon_class.includes('fa-') ? link.icon_class : `fa-${link.platform}`;
        socialHtml += `<li><a class="icon icon-sm icon-circle icon-circle-md icon-bg-white ${iconClass}" href="${link.url}" title="${link.platform}"></a></li>`;
      });
    } else {
      // Default social links while loading or if no links found
      socialHtml = `
        <li><a class="icon icon-sm icon-circle icon-circle-md icon-bg-white fa-facebook" href="#"></a></li>
        <li><a class="icon icon-sm icon-circle icon-circle-md icon-bg-white fa-instagram" href="#"></a></li>
        <li><a class="icon icon-sm icon-circle icon-circle-md icon-bg-white fa-twitter" href="#"></a></li>
        <li><a class="icon icon-sm icon-circle icon-circle-md icon-bg-white fa-youtube-play" href="#"></a></li>
      `;
    }

    this.innerHTML = `
      <footer class="section footer-minimal context-dark">
        <div class="container wow-outer">
          <div class="wow fadeIn">
            <div class="row row-50 row-lg-60">
              <div class="col-12"><a href="index.php"><img src="images/logo-main.png" alt="" width="207" height="51"/></a></div>
              <div class="col-12">
                <ul class="footer-minimal-nav">
                  ${linksHtml}
                </ul>
              </div>
              <div class="col-12">
                <ul class="social-list">
                  ${socialHtml}
                </ul>
              </div>
            </div>
          </div>
        </div>
      </footer>
    `;
  }
}

// Define the custom elements
customElements.define('site-header', SiteHeader);
customElements.define('site-footer', SiteFooter);