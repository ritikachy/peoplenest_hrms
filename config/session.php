<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: dashboard.php');
        exit();
    }
}

function requireEmployee() {
    requireLogin();
    if ($_SESSION['role'] !== 'employee') {
        header('Location: admin-dashboard.php');
        exit();
    }
}

function logout() {
    // 1. Clear the $_SESSION array
    $_SESSION = array();

    // 2. Clear the session cookie from the browser
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // 3. Destroy the session on the server
    session_destroy();

    // 4. Redirect to landing page so user can browse your site publicly
    header('Location: index.php');
    exit();
}



?>


