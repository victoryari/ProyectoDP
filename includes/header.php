<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) {
    header("Location: /casahogar/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión - Casa Hogar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #2c3e50; color: white; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover { background-color: #34495e; }
        .content { background-color: #f8f9fa; min-height: 100vh; }
        .navbar-custom { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">