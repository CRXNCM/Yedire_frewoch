function showPlusSignsWhenComplete() {
    // Get all counter elements
    const studentsCounter = document.getElementById('studentsCounter');
    const schoolsCounter = document.getElementById('schoolsCounter');
    const volunteersCounter = document.getElementById('volunteersCounter');
    const communitiesCounter = document.getElementById('communitiesCounter');
    
    // Get all plus sign elements
    const studentsPlus = document.getElementById('studentsPlus');
    const schoolsPlus = document.getElementById('schoolsPlus');
    const volunteersPlus = document.getElementById('volunteersPlus');
    const communitiesPlus = document.getElementById('communitiesPlus');
    
    // Set up a mutation observer to watch for changes to the counter text
    const observerConfig = { childList: true, characterData: true, subtree: true };
    
    // Create observers for each counter
    if (studentsCounter && studentsPlus) {
      const studentsObserver = new MutationObserver(function(mutations) {
        if (studentsCounter.textContent === '2000') {
          studentsPlus.style.display = 'inline';
          studentsObserver.disconnect();
        }
      });
      studentsObserver.observe(studentsCounter, observerConfig);
    }
    
    if (schoolsCounter && schoolsPlus) {
      const schoolsObserver = new MutationObserver(function(mutations) {
        if (schoolsCounter.textContent === '20') {
          schoolsPlus.style.display = 'inline';
          schoolsObserver.disconnect();
        }
      });
      schoolsObserver.observe(schoolsCounter, observerConfig);
    }
    
    if (volunteersCounter && volunteersPlus) {
      const volunteersObserver = new MutationObserver(function(mutations) {
        if (volunteersCounter.textContent === '30') {
          volunteersPlus.style.display = 'inline';
          volunteersObserver.disconnect();
        }
      });
      volunteersObserver.observe(volunteersCounter, observerConfig);
    }
    
    if (communitiesCounter && communitiesPlus) {
      const communitiesObserver = new MutationObserver(function(mutations) {
        if (communitiesCounter.textContent === '18') {
          communitiesPlus.style.display = 'inline';
          communitiesObserver.disconnect();
        }
      });
      communitiesObserver.observe(communitiesCounter, observerConfig);
    }
  }
  
  // Run the function when the DOM is fully loaded
  document.addEventListener('DOMContentLoaded', showPlusSignsWhenComplete);
  function animateCounter(elementId, finalValue, duration) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    let startTime = null;
    const startValue = 0;
    
    function easeOutQuart(t) {
      return 1 - Math.pow(1 - t, 4); // This creates a slowing down effect
    }
    
    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      const elapsed = timestamp - startTime;
      const progress = Math.min(elapsed / duration, 1);
      
      // Apply easing function to slow down at the end
      const easedProgress = easeOutQuart(progress);
      const currentValue = Math.floor(easedProgress * (finalValue - startValue) + startValue);
      
      element.textContent = currentValue;
      
      if (progress < 1) {
        window.requestAnimationFrame(step);
      } else {
        element.textContent = finalValue;
      }
    }
    
    window.requestAnimationFrame(step);
  }
  
  // Start the counter animation when the element is in view
  document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          // Start animations with different durations for visual appeal
          animateCounter('studentsCounter', 2000, 3000);
          animateCounter('schoolsCounter', 20, 2500);
          animateCounter('volunteersCounter', 30, 2700);
          animateCounter('communitiesCounter', 18, 2300);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    
    // Observe the counter section
    const counterSection = document.querySelector('.box-counter');
    if (counterSection) {
      observer.observe(counterSection);
    }
  });
  // Counter animation function with easing
  function animateCounter(elementId, finalValue, duration) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    let startTime = null;
    const startValue = 0;
    
    function easeOutQuart(t) {
      return 1 - Math.pow(1 - t, 4); // This creates a slowing down effect
    }
    
    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      const elapsed = timestamp - startTime;
      const progress = Math.min(elapsed / duration, 1);
      
      // Apply easing function to slow down at the end
      const easedProgress = easeOutQuart(progress);
      const currentValue = Math.floor(easedProgress * (finalValue - startValue) + startValue);
      
      element.textContent = currentValue;
      
      if (progress < 1) {
        window.requestAnimationFrame(step);
      } else {
        element.textContent = finalValue;
      }
    }
    
    window.requestAnimationFrame(step);
  }
  
  // Start the counter animation when the element is visible in viewport
  document.addEventListener('DOMContentLoaded', function() {
    // Function to check if element is in viewport
    function isInViewport(element) {
      const rect = element.getBoundingClientRect();
      return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
      );
    }
    
    // Get all counter elements
    const counterElements = document.querySelectorAll('[data-counter]');
    
    // Function to start animation for visible counters
    function checkCounters() {
      counterElements.forEach(function(element) {
        if (isInViewport(element) && !element.dataset.counted) {
          // Mark as counted to prevent repeated animations
          element.dataset.counted = true;
          
          // Get the target value from data attribute
          const finalValue = parseInt(element.dataset.counter, 10);
          
          // Animate the counter
          animateCounter(element.id, finalValue, 2000);
        }
      });
    }
    
    // Check counters on scroll
    window.addEventListener('scroll', checkCounters);
    
    // Initial check
    checkCounters();
  });