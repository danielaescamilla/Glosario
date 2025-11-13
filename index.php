<?php
// Página principal: migra a Bootstrap tarjetas de navegación
include __DIR__ . '/includes/header.php';
?>

<div class="text-center mb-4">
  <img src="img/logo_universidad.png" alt="Logo Universidad" class="img-fluid" style="max-width:120px;">
  <h2 class="mt-3 text-primary">Panel Principal – Glosario Jurídico Bilingüe</h2>
  <p class="text-muted">Selecciona una opción para continuar:</p>
  
</div>

<div class="row g-3">
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card h-100 shadow-sm">
      <div class="card-body text-center">
        <i class="fa-solid fa-book-open text-primary fs-2 mb-2"></i>
        <h5 class="card-title">Explorar términos</h5>
        <p class="card-text text-muted">Consulta los términos registrados en el sistema.</p>
        <a class="btn btn-primary" href="terms/index.php">Ir</a>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card h-100 shadow-sm">
      <div class="card-body text-center">
        <i class="fa-solid fa-plus-circle text-primary fs-2 mb-2"></i>
        <h5 class="card-title">Agregar término</h5>
        <p class="card-text text-muted">Registra un nuevo término con sus traducciones.</p>
        <a class="btn btn-primary" href="terms/create.php">Ir</a>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card h-100 shadow-sm">
      <div class="card-body text-center">
        <i class="fa-solid fa-pen-to-square text-primary fs-2 mb-2"></i>
        <h5 class="card-title">Editar / Eliminar</h5>
        <p class="card-text text-muted">Gestiona los términos creados o existentes.</p>
        <a class="btn btn-primary" href="terms/index.php">Ir</a>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card h-100 shadow-sm">
      <div class="card-body text-center">
        <i class="fa-solid fa-right-from-bracket text-danger fs-2 mb-2"></i>
        <h5 class="card-title">Cerrar sesión</h5>
        <p class="card-text text-muted">Salir del sistema de manera segura.</p>
        <a class="btn btn-outline-danger" href="auth/logout.php">Salir</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>


