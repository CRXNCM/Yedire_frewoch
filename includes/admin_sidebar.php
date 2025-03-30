<div class="col-md-2 d-none d-md-block sidebar">
    <div class="text-center mb-4 logo-container">
        <img src="images/logo.png" alt="Yedire Frewoch Logo" class="sidebar-logo">
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> <span class="nav-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_schools.php' ? 'active' : ''; ?>" href="manage_schools.php">
                <i class="fas fa-school"></i> <span class="nav-text">Schools</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_images.php' ? 'active' : ''; ?>" href="manage_images.php">
                <i class="fas fa-images"></i> <span class="nav-text">Images</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_volunteers.php' ? 'active' : ''; ?>" href="manage_volunteers.php">
                <i class="fas fa-hands-helping"></i> <span class="nav-text">Volunteers</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_communities.php' ? 'active' : ''; ?>" href="manage_communities.php">
                <i class="fas fa-users"></i> <span class="nav-text">Communities</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_donation.php' ? 'active' : ''; ?>" href="manage_donation.php">
                <i class="fas fa-hand-holding-usd"></i> <span class="nav-text">Donations</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_social.php' ? 'active' : ''; ?>" href="manage_social.php">
                <i class="fas fa-share-alt"></i> <span class="nav-text">Social Media</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_urgent_messages.php' ? 'active' : ''; ?>" href="manage_urgent_messages.php">
                <i class="fas fa-exclamation-circle"></i> <span class="nav-text">Urgent Messages</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_testimonials.php' ? 'active' : ''; ?>" href="manage_testimonials.php">
                <i class="fas fa-quote-left"></i> <span class="nav-text">Testimonials</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_sponsors.php' ? 'active' : ''; ?>" href="manage_sponsors.php">
                <i class="fas fa-handshake"></i> <span class="nav-text">Sponsors</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="index.php" target="_blank">
                <i class="fas fa-eye"></i> <span class="nav-text">View Website</span>
            </a>
        </li>
    </ul>
    <a href="admin.php" class="btn btn-danger logout-btn">
        <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
    </a>
</div>

<style>
    .sidebar {
        min-height: 100vh;
        background-color: #2c3e50;
        padding-top: 20px;
        position: fixed;
        width: 250px;
        box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        z-index: 1000;
    }
    
    .sidebar-logo {
        max-width: 80px;
        transition: all 0.3s ease;
        filter: brightness(0) invert(1);
        opacity: 0.9;
    }
    
    .logo-container {
        margin-bottom: 30px;
    }
    
    .sidebar .nav-link {
        color: rgba(255,255,255,0.7);
        padding: 12px 20px;
        border-radius: 5px;
        margin: 2px 10px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }
    
    .sidebar .nav-link:hover {
        color: #fff;
        background-color: rgba(255,255,255,0.1);
        transform: translateX(5px);
    }
    
    .sidebar .nav-link.active {
        color: #fff;
        background-color: #3498db;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .sidebar .nav-link i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
        font-size: 16px;
    }
    
    .nav-text {
        font-size: 14px;
        font-weight: 500;
    }
    
    .logout-btn {
        position: absolute;
        bottom: 20px;
        width: calc(100% - 40px);
        left: 20px;
        border-radius: 5px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #e74c3c;
        border: none;
        padding: 10px;
    }
    
    .logout-btn:hover {
        background-color: #c0392b;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .logout-btn i {
        margin-right: 8px;
    }
    
    .nav-item {
        margin-bottom: 5px;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }
    
    .nav-item {
        animation: fadeIn 0.5s ease forwards;
        opacity: 0;
    }
    
    .nav-item:nth-child(1) { animation-delay: 0.1s; }
    .nav-item:nth-child(2) { animation-delay: 0.15s; }
    .nav-item:nth-child(3) { animation-delay: 0.2s; }
    .nav-item:nth-child(4) { animation-delay: 0.25s; }
    .nav-item:nth-child(5) { animation-delay: 0.3s; }
    .nav-item:nth-child(6) { animation-delay: 0.35s; }
    .nav-item:nth-child(7) { animation-delay: 0.4s; }
    .nav-item:nth-child(8) { animation-delay: 0.45s; }
    .nav-item:nth-child(9) { animation-delay: 0.5s; }
    .nav-item:nth-child(10) { animation-delay: 0.55s; }
    .nav-item:nth-child(11) { animation-delay: 0.6s; }
</style>
