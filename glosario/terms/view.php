<?php
// Detalle de término: presentación con Bootstrap
require_once __DIR__ . '/../config/conexion.php';
include __DIR__ . '/../includes/header.php';

$id = $_GET['id'] ?? null;
if(!$id) { echo "<p>ID no proporcionado.</p>"; include __DIR__ . '/../includes/footer.php'; exit; }

$stmt = $pdo->prepare("SELECT t.*, u.name as creator, c.name as country_name FROM terms t LEFT JOIN users u ON t.creator_id=u.id LEFT JOIN countries c ON t.country_id=c.id WHERE t.id = ?");
$stmt->execute([$id]);
$term = $stmt->fetch();
if(!$term) { echo "<p>Término no encontrado.</p>"; include __DIR__ . '/../includes/footer.php'; exit; }

$translations = $pdo->prepare("SELECT tr.*, l.name as language FROM term_translations tr JOIN languages l ON tr.language_id = l.id WHERE tr.term_id = ?");
$translations->execute([$id]);
$translations = $translations->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="m-0">Detalle del término</h2>
  <div>
    <a class="btn btn-outline-secondary btn-sm" href="index.php">Volver al listado</a>
    <?php if(isset($_SESSION['user_id']) && ($_SESSION['user_id']==$term['creator_id'] || in_array($_SESSION['role'], ['teacher','admin']))): ?>
      <a class="btn btn-primary btn-sm" href="edit.php?id=<?php echo (int)$term['id']; ?>">Editar</a>
      <a class="btn btn-danger btn-sm" href="delete.php?id=<?php echo (int)$term['id']; ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a>
    <?php endif; ?>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <div class="row g-2">
      <div class="col-12 col-md-6"><strong>Término canónico:</strong> <?php echo htmlspecialchars($term['canonical_term']);?></div>
      <div class="col-12 col-md-3"><strong>País:</strong> <?php echo htmlspecialchars($term['country_name']);?></div>
      <div class="col-12 col-md-3"><strong>Estado:</strong> <span class="badge bg-secondary"><?php echo $term['status'];?></span></div>
      <div class="col-12"><strong>Creado por:</strong> <?php echo htmlspecialchars($term['creator']);?></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h5 class="card-title">Traducciones</h5>
    <div class="list-group">
      <?php foreach($translations as $tr): ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between">
            <strong><?php echo htmlspecialchars($tr['language']); ?></strong>
            <span class="text-muted">Validado por: <?php echo $tr['validated_by'] ? htmlspecialchars($tr['validated_by']) : '—'; ?></span>
          </div>
          <div><em>Traducción:</em> <?php echo htmlspecialchars($tr['translation']); ?></div>
          <div><em>Definición:</em> <?php echo nl2br(htmlspecialchars($tr['definition'])); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
