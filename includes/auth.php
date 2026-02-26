<?php
// includes/auth.php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit;
    }
}

function getUserRole() {
    return $_SESSION['user']['role'] ?? null;
}

function requireRole($role) {
    if (!isLoggedIn() || $_SESSION['user']['role'] !== $role) {
        header("Location: /login.php");
        exit;
    }
}
?>