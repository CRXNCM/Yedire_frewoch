
    document.addEventListener('DOMContentLoaded', function() {
    // Urgent popup functionality
    const urgentPopup = document.getElementById('urgentPopup');
    if (urgentPopup) {
      // Show popup after a short delay
      setTimeout(function() {
        urgentPopup.style.display = 'block';
        setTimeout(function() {
          urgentPopup.classList.add('show');
        }, 10);
      }, 2000);
      
      // Close popup when clicking the close button
      const closeBtn = document.querySelector('.urgent-popup-close');
      const closePopupBtn = document.getElementById('closeUrgentPopup');
      
      function closeUrgentPopup() {
        urgentPopup.classList.remove('show');
        setTimeout(function() {
          urgentPopup.style.display = 'none';
        }, 400);
        
        // Set cookie to prevent showing again in this session
        document.cookie = "urgentPopupShown=true; path=/";
      }
      
      if (closeBtn) {
        closeBtn.addEventListener('click', closeUrgentPopup);
      }
      
      if (closePopupBtn) {
        closePopupBtn.addEventListener('click', closeUrgentPopup);
      }
      
      // Close when clicking outside the popup content
      urgentPopup.addEventListener('click', function(event) {
        if (event.target === urgentPopup) {
          closeUrgentPopup();
        }
      });
    }
  });
      $(document).ready(function() {
        // Initialize the carousel with a 5-second interval
        $('#testimonialCarousel').carousel({
          interval: 5000,
          pause: 'hover'
        });
        
        // Initialize the sponsors carousel with a 4-second interval
        $('#sponsorsCarousel').carousel({
          interval: 4000,
          pause: 'hover'
        });
      });
      document.addEventListener('DOMContentLoaded', function() {
// Testimonial slider functionality
window.testimonialIndex = 1; // Start at the center slide (if available)

window.updateTestimonial = function() {
  const slides = document.querySelectorAll(".testimonial-slide");
  const dots = document.querySelectorAll(".testimonial-dot");
  
  if (slides.length === 0) return;
  
  document.querySelector(".testimonials-slider").style.transform = 
    `translateX(-${window.testimonialIndex * 33.33}%)`;
  
  slides.forEach((slide, i) => {
    slide.classList.remove("active");
    slide.style.opacity = i === window.testimonialIndex ? "1" : "0.5";
    slide.style.transform = i === window.testimonialIndex ? "scale(1)" : "scale(0.9)";
  });
  
  slides[window.testimonialIndex].classList.add("active");
  
  dots.forEach(dot => dot.classList.remove("active"));
  dots[window.testimonialIndex].classList.add("active");
};

window.nextTestimonial = function() {
  const slides = document.querySelectorAll(".testimonial-slide");
  window.testimonialIndex = (window.testimonialIndex + 1) % slides.length;
  window.updateTestimonial();
};

window.prevTestimonial = function() {
  const slides = document.querySelectorAll(".testimonial-slide");
  window.testimonialIndex = (window.testimonialIndex - 1 + slides.length) % slides.length;
  window.updateTestimonial();
};

window.goToTestimonial = function(index) {
  window.testimonialIndex = index;
  window.updateTestimonial();
};

// Auto-rotate testimonials
let testimonialInterval = setInterval(window.nextTestimonial, 5000);

// Pause auto-rotation when hovering over testimonials
const testimonialsWrapper = document.querySelector('.testimonials-wrapper');
if (testimonialsWrapper) {
  testimonialsWrapper.addEventListener('mouseenter', function() {
    clearInterval(testimonialInterval);
  });
  
  testimonialsWrapper.addEventListener('mouseleave', function() {
    testimonialInterval = setInterval(window.nextTestimonial, 5000);
  });
}

// Add touch swipe functionality for mobile
let touchStartX = 0;
let touchEndX = 0;

const slider = document.querySelector('.testimonials-slider');
if (slider) {
  slider.addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
  }, false);
  
  slider.addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
  }, false);
}

function handleSwipe() {
  if (touchEndX < touchStartX) {
    // Swipe left, go to next
    window.nextTestimonial();
  } else if (touchEndX > touchStartX) {
    // Swipe right, go to previous
    window.prevTestimonial();
  }
}
});
document.addEventListener('DOMContentLoaded', function() {
// Initialize AOS animation library
AOS.init({
  duration: 800,
  easing: 'ease-out',
  once: true
});
});