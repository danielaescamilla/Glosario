<?php
session_start();

// Eliminar todas las variables de sesión
$_SESSION = [];

// Destruir la sesión
session_destroy();

// Esperar 1 segundo y luego redirigir al login
header("Refresh: 1; url=login.php");
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Saliendo del sistema...</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #dbe8ff, #f5f9ff);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
    }
    .logout-container {
      background: #fff;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      text-align: center;
      width: 350px;
    }
    .logout-container i {
      font-size: 50px;
      color: #007bff;
      margin-bottom: 10px;
    }
    .logout-container h2 {
      color: #007bff;
      margin-bottom: 10px;
    }
    .logout-container p {
      color: #333;
      font-size: 15px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <div class="logout-container">
    <i class="fa-solid fa-right-from-bracket"></i>
    <h2>Cerrando sesión...</h2>
    <p>Gracias por usar el Glosario Jurídico.</p>
    <p><em>Serás redirigido al inicio de sesión en unos segundos.</em></p>
  </div>
</body>
</html>

