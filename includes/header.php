<?php
// Header común del sitio: inicia sesión y carga Bootstrap + barra de navegación
if (session_status() == PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Glosario Jurídico</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="/glosario/css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Fin CSS -->
  <style>body{background:#f8f9fb}</style>
</head>
<body>
<header class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container py-2">
    <a class="navbar-brand d-flex align-items-center" href="/glosario/index.php">
      <i class="fa-solid fa-scale-balanced text-primary me-2"></i>
      <span>Glosario Jurídico Bilingüe</span>
    </a>
    <div class="d-flex ms-auto">
      <?php if(isset($_SESSION['user_id'])): ?>
        <span class="navbar-text">👋 Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></span>
        <a href="/glosario/auth/logout.php" class="btn btn-outline-danger btn-sm ms-3">Salir</a>
      <?php else: ?>
        <a href="/glosario/auth/login.php" class="btn btn-primary btn-sm">Ingresar</a>
      <?php endif; ?>
    </div>
  </div>
  
</header>
<main class="container my-4">

