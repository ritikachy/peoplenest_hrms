<div class="sidebar">
    <div class="sidebar-header">
        <h2>PeopleNest</h2>
        <p>Employee Portal</p>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
                Dashboard
            </a>
        </div>
        
        <div class="nav-item">
            <a href="my-attendance.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'my-attendance.php' ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                My Attendance
            </a>
        </div>
        
        <div class="nav-item">
            <a href="apply-leave.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'apply-leave.php' ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Apply for Leave
            </a>
        </div>
        
        <div class="nav-item">
            <a href="my-leaves.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'my-leaves.php' ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                My Leave Requests
            </a>
        </div>
    </nav>
</div>
