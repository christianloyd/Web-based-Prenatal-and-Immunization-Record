<?php
// Librarian Sidebar - Global Navigation
// Make sure session is started before including this file
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-book me-2"></i>Librarian Portal</h4>
        <div class="user-info">
            <small>Welcome, <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Librarian'; ?></small>
            <div class="user-role">
                <span class="badge bg-success"><?php echo isset($_SESSION['user_role']) ? ucfirst($_SESSION['user_role']) : 'Staff'; ?></span>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link" id="nav-dashboard">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </div>
        <div class="nav-item">
            <a href="manage-surveys.php" class="nav-link" id="nav-manage-surveys">
                <i class="fas fa-cogs"></i> Manage Surveys
            </a>
        </div>
        <div class="nav-item">
            <a href="analytics.php" class="nav-link" id="nav-analytics">
                <i class="fas fa-chart-bar"></i> Analytics
            </a>
        </div>
        <div class="nav-item">
            <a href="responses.php" class="nav-link" id="nav-responses">
                <i class="fas fa-comments"></i> Responses
            </a>
        </div>
        <div class="nav-item">
            <a href="reports.php" class="nav-link" id="nav-reports">
                <i class="fas fa-file-alt"></i> Reports
            </a>
        </div>

        <hr class="sidebar-divider">

        <div class="nav-section-title">
            <small>MANAGEMENT</small>
        </div>
        <div class="nav-item">
            <a href="user-management.php" class="nav-link" id="nav-user-management">
                <i class="fas fa-users"></i> Student Management
            </a>
        </div>
        <hr class="sidebar-divider">

        <div class="nav-item">
            <a href="profile.php" class="nav-link" id="nav-profile">
                <i class="fas fa-user-edit"></i> Profile
            </a>
        </div>
        <div class="nav-item">
            <a href="about.php" class="nav-link" id="nav-about">
                <i class="fas fa-info-circle"></i> About Project
            </a>
        </div>

        <hr class="sidebar-divider">

        <div class="nav-item">
            <a href="../logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>
</div>

<style>
/* Librarian Sidebar Styles */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 260px;
    height: 100vh;
    background: white;
    border-right: 1px solid #e9ecef;
    z-index: 999;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar-header {
    padding: 1.5rem;
    background: var(--primary-green);
    color: white;
    text-align: center;
    border-bottom: 1px solid var(--medium-green);
}

.sidebar-header h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.user-info {
    opacity: 0.9;
    font-size: 0.85rem;
}

.user-role {
    margin-top: 0.5rem;
}

.user-role .badge {
    font-size: 0.7rem;
    padding: 0.3rem 0.6rem;
    background-color: var(--medium-green) !important;
}

.sidebar-nav {
    padding: 1rem 0;
}

.nav-section-title {
    padding: 0.5rem 1.5rem;
    margin-top: 1rem;
}

.nav-section-title small {
    color: #6c757d;
    font-weight: 600;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.nav-item {
    margin: 0.2rem 0;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.8rem 1.5rem;
    color: #6c757d;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-size: 0.9rem;
}

.nav-link:hover, .nav-link.active {
    background: var(--light-green);
    color: white;
    border-left: 4px solid var(--primary-green);
}

.nav-link.text-danger:hover {
    background: #dc3545;
    color: white;
    border-left: 4px solid #c82333;
}

.nav-link i {
    width: 20px;
    margin-right: 0.8rem;
    text-align: center;
}

.sidebar-divider {
    margin: 1rem 0;
    border-color: #e9ecef;
}

/* Special styling for admin functions */
.nav-section-title + .nav-item .nav-link {
    background: rgba(220, 53, 69, 0.05);
    border-left: 2px solid #dc3545;
}

.nav-section-title + .nav-item .nav-link:hover {
    background: #dc3545;
    color: white;
    border-left: 4px solid #c82333;
}

/* Color variables for consistency */
:root {
    --primary-green: #2d5a27;
    --medium-green: #4a7c59;
    --light-green: #7fb069;
}

/* Mobile Sidebar Styles */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        z-index: 1056;
        position: fixed;
        top: 0;
        left: 0;
        width: 260px;
        height: 100vh;
        box-shadow: 2px 0 10px rgba(0,0,0,0.3);
    }

    .sidebar.show {
        transform: translateX(0);
    }

    .sidebar-header h4 {
        font-size: 1rem;
    }

    .nav-link {
        font-size: 0.85rem;
        padding: 0.7rem 1rem;
    }

    .nav-link i {
        margin-right: 0.6rem;
    }
}

@media (max-width: 576px) {
    .sidebar {
        width: 100%;
        max-width: 280px;
    }

    .sidebar-header {
        padding: 1rem;
    }

    .sidebar-header h4 {
        font-size: 0.95rem;
    }

    .user-info {
        font-size: 0.8rem;
    }
}
</style>