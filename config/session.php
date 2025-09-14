<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function isAdmin() {
    return isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}
?>
