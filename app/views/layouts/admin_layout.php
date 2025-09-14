<?php
require_once 'includes/auth.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin - Mini Chic</title>
    
    <!-- CSS Modular -->
    <link rel="stylesheet" href="../public/css/base.css">
    <link rel="stylesheet" href="../public/css/header.css">
    <link rel="stylesheet" href="../public/css/admin.css">
    <link rel="stylesheet" href="../public/css/forms.css">
    <link rel="stylesheet" href="../public/css/responsive.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header admin-header">
        <div class="container">
            <div class="nav-brand">
                <h1><i class="fas fa-baby"></i> Mini Chic Admin</h1>
                <p>Panel de Administración</p>
            </div>
            <nav class="nav-menu">
                <a href="/admin">Dashboard</a>
                <a href="/admin/productos">Productos</a>
                <a href="/admin/pedidos">Pedidos</a>
                <a href="/admin/mensajes">Mensajes</a>
                <a href="/">Ver Sitio</a>
                <a href="/logout">Salir</a>
            </nav>
        </div>
    </header>
