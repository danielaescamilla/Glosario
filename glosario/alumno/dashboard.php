<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') { header('Location: /glosario/alumno/login.php'); exit; }
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-student container">
  <div class="logo-container text-center mb-4">
    <img src="../img/uped.png" alt="Logo UPED" class="img-fluid" style="max-width:150px;">
  </div>

  <div class="card">
    <div class="card-header bg-white border-0">
      <h2 class="text-center mb-0 text-primary">Portal del Estudiante</h2>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-3">
          <div class="list-group">
            <a href="/glosario/alumno/dashboard.php" class="list-group-item list-group-item-action active">Inicio</a>
            <a href="/glosario/terms/index.php" class="list-group-item list-group-item-action">Explorar términos</a>
            <a href="/glosario/terms/index.php" class="list-group-item list-group-item-action">Mis aportes</a>
            <a href="/glosario/auth/logout.php" class="list-group-item list-group-item-action text-danger">Cerrar Sesión</a>
          </div>
        </div>
        <div class="col-md-9">
          <div class="alert alert-info">
            <h4 class="mb-2">Bienvenido, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Estudiante'); ?></h4>
            <p>Este es tu portal en el Glosario Jurídico. Aquí podrás:</p>
            <ul class="mb-0">
              <li>Explorar términos registrados y sus traducciones</li>
              <li>Revisar los términos que has aportado</li>
              <li>Acceder rápidamente a las funciones principales</li>
            </ul>
          </div>

          <div class="card mt-4">
            <div class="card-header bg-white border-0">
              <h5 class="text-primary mb-0">Anuncios importantes</h5>
            </div>
            <div class="card-body">
              <p class="mb-0">No hay anuncios nuevos por el momento.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>