<div class="sidebar">
    <div class="sidebar-header">
        <h2>PeopleNest</h2>
        <p>Admin Panel</p>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="admin-dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'admin-dashboard.php' ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
                Dashboard
            </a>
        </div>
        
        <div class="nav-item">
            <a href="employee-management.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'employee-management.php' ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Employees
            </a>
        </div>
        
        <div class="nav-item">
            <a href="attendance-management.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'attendance-management.php' ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Attendance
            </a>
        </div>
        
        <div class="nav-item">
            <a href="leave-management.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'leave-management.php' ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Leave Management
            </a>
        </div>
        <div class="nav-item">
            <a href="job_postings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'job_postings.php' ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Manage Vacancies
            </a>
        </div>
        
        <div class="nav-item">
            <a href="recruitment.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'recruitment.php' ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20a3 3 0 01-3-3v-1a3 3 0 01.879-2.121M7 20v-2c0-.656.126-1.283.356-1.857m0 0a3 3 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Recruitment
            </a>
        </div>
    </nav>
</div>
