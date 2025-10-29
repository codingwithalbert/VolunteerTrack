<?php
session_start();
require_once __DIR__ . '/../config/config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

function requireRole($allowedRoles) {
    requireLogin();
    
    if (!is_array($allowedRoles)) {
        $allowedRoles = array($allowedRoles);
    }
    
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        header('Location: ' . SITE_URL . '/index.php');
        exit();
    }
}

function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

function getUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function getUserFullName() {
    return isset($_SESSION['full_name']) ? $_SESSION['full_name'] : null;
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: ' . SITE_URL . '/login.php');
    exit();
}

// Check session timeout
if (isLoggedIn() && isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        logout();
    }
}

if (isLoggedIn()) {
    $_SESSION['last_activity'] = time();
}
?>