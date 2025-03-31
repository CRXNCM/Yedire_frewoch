// Enhanced lightbox functionality with smooth animations
document.addEventListener('DOMContentLoaded', function() {
    // Get the modal
    const modal = document.getElementById('lightboxModal');
    const modalImg = document.getElementById('lightboxImage');
    const closeBtn = document.getElementsByClassName('close-lightbox')[0];
    
    // Get all images with class "img-lightbox"
    const images = document.querySelectorAll('.img-lightbox');
    
    // Add click event to each image
    images.forEach(function(img) {
      img.addEventListener('click', function(e) {
        e.preventDefault(); // Prevent default link behavior
        
        // Set the image source
        modalImg.src = this.href;
        
        // Display the modal with a slight delay for the animation
        modal.style.display = "block";
        
        // Force a reflow before adding the 'show' class for the animation to work
        void modal.offsetWidth;
        
        // Add the 'show' class to trigger the animations
        modal.classList.add('show');
        
        // Disable scrolling on the body
        document.body.style.overflow = 'hidden';
        
        // Disable parallax temporarily to prevent issues
        const parallaxItems = document.querySelectorAll('[data-parallax-scroll]');
        parallaxItems.forEach(function(item) {
          item.setAttribute('data-parallax-disabled', item.getAttribute('data-parallax-scroll'));
          item.removeAttribute('data-parallax-scroll');
        });
      });
    });
    
    // Function to close the modal with animation
    function closeModal() {
      // Remove the 'show' class to trigger the hide animations
      modal.classList.remove('show');
      
      // Wait for the animation to complete before hiding the modal
      setTimeout(function() {
        modal.style.display = "none";
        
        // Re-enable scrolling on the body
        document.body.style.overflow = '';
        
        // Re-enable parallax
        const parallaxItems = document.querySelectorAll('[data-parallax-disabled]');
        parallaxItems.forEach(function(item) {
          item.setAttribute('data-parallax-scroll', item.getAttribute('data-parallax-disabled'));
          item.removeAttribute('data-parallax-disabled');
        });
      }, 400); // Match this to the CSS transition duration
    }
    
    // Close the modal when clicking the × button
    closeBtn.addEventListener('click', closeModal);
    
    // Close the modal when clicking outside the image
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeModal();
      }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && modal.style.display === 'block') {
        closeModal();
      }
    });
    
    // Preload images for smoother experience
    images.forEach(function(img) {
      const preloadLink = img.href;
      if (preloadLink) {
        const preloadImage = new Image();
        preloadImage.src = preloadLink;
      }
    });
  });