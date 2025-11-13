<?php
// Listado de términos con búsqueda y filtros, estilizado con Bootstrap
require_once __DIR__ . '/../config/conexion.php';
include __DIR__ . '/../includes/header.php';

$q = trim($_GET['q'] ?? '');
$country = $_GET['country'] ?? '';

$sql = "SELECT t.*, c.name as country_name, u.name as creator
        FROM terms t
        LEFT JOIN countries c ON t.country_id = c.id
        LEFT JOIN users u ON t.creator_id = u.id
        WHERE 1=1 ";
$params = [];

if($q !== '') {
    $sql = "SELECT DISTINCT t.*, c.name as country_name, u.name as creator
            FROM terms t
            LEFT JOIN term_translations tr ON tr.term_id = t.id
            LEFT JOIN countries c ON t.country_id = c.id
            LEFT JOIN users u ON t.creator_id = u.id
            WHERE (t.canonical_term LIKE ? OR tr.translation LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    if($country) {
        $sql .= " AND t.country_id = ?";
        $params[] = $country;
    }
} else {
    if($country) {
        $sql .= " AND t.country_id = ?";
        $params[] = $country;
    }
}

$sql .= " ORDER BY t.created_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$terms = $stmt->fetchAll();

$countries = $pdo->query("SELECT * FROM countries")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="m-0">Términos</h2>
  <a class="btn btn-primary" href="create.php"><i class="fa fa-plus me-1"></i> Agregar nuevo término</a>
</div>

<form class="row gy-2 gx-2 align-items-end mb-3" method="get">
  <div class="col-12 col-md-6">
    <label class="form-label">Buscar término o traducción</label>
    <input class="form-control" name="q" placeholder="Escribe para buscar" value="<?php echo htmlspecialchars($q); ?>">
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">País</label>
    <select class="form-select" name="country">
      <option value="">Todos los países</option>
      <?php foreach($countries as $c): ?>
        <option value="<?php echo $c['id'];?>" <?php if($country==$c['id']) echo 'selected';?>><?php echo htmlspecialchars($c['name']);?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12 col-md-2">
    <button class="btn btn-outline-primary w-100" type="submit"><i class="fa fa-search me-1"></i> Buscar</button>
  </div>
  
</form>

<div class="table-responsive">
  <table class="table table-striped table-hover align-middle">
    <thead>
      <tr>
        <th>ID</th>
        <th>Término canónico</th>
        <th>País</th>
        <th>Status</th>
        <th>Creado por</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($terms as $t): ?>
      <tr>
        <td><?php echo $t['id'];?></td>
        <td><?php echo htmlspecialchars($t['canonical_term']);?></td>
        <td><?php echo htmlspecialchars($t['country_name']);?></td>
        <td><span class="badge bg-secondary"><?php echo $t['status'];?></span></td>
        <td><?php echo htmlspecialchars($t['creator']);?></td>
        <td>
          <a class="btn btn-sm btn-outline-primary" href="view.php?id=<?php echo $t['id'];?>">Ver</a>
          <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id']==$t['creator_id']): ?>
            <a class="btn btn-sm btn-outline-secondary" href="edit.php?id=<?php echo $t['id'];?>">Editar</a>
            <a class="btn btn-sm btn-outline-danger" href="delete.php?id=<?php echo $t['id'];?>" onclick="return confirm('¿Eliminar?')">Eliminar</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
