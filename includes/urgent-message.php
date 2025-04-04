<?php
// Get active urgent messages if not already fetched
if (!isset($urgent_messages)) {
    // Database connection
    if (!isset($conn) || $conn->connect_error) {
        $db_host = 'localhost';
        $db_user = 'root';
        $db_pass = '';
        $db_name = 'yedire_frewoch';
        
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
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
    
    // Close connection if we opened it in this file
    if (!isset($GLOBALS['conn'])) {
        $conn->close();
    }
}
?>

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
</style>

<!-- Urgent Message Popup HTML -->
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

<!-- Urgent Message Popup Script -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Urgent popup functionality
    const urgentPopup = document.getElementById('urgentPopup');
    if (urgentPopup) {
      // Check if we've already shown this popup in this session
      const popupShown = document.cookie.indexOf('urgentPopupShown=true') !== -1;
      
      if (!popupShown) {
        // Show popup after a short delay
        setTimeout(function() {
          urgentPopup.style.display = 'block';
          setTimeout(function() {
            urgentPopup.classList.add('show');
          }, 10);
        }, 2000);
      }
      
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
</script>
<?php endif; ?>