<?php
// Edición de término: formulario Bootstrap con datos precargados
require_once __DIR__ . '/../config/conexion.php';
if (session_status() == PHP_SESSION_NONE) session_start();

if(!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if(!$id) {
    die("ID no proporcionado.");
}

// Obtenemos el término
$stmt = $pdo->prepare("SELECT * FROM terms WHERE id = ?");
$stmt->execute([$id]);
$term = $stmt->fetch();

if(!$term) {
    die("Término no encontrado.");
}

// Solo el creador o un docente/admin puede editar
if($_SESSION['user_id'] != $term['creator_id'] && $_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'admin') {
    die("No tienes permiso para editar este término.");
}

$countries = $pdo->query("SELECT * FROM countries")->fetchAll();
$languages = $pdo->query("SELECT * FROM languages")->fetchAll();

// Procesamos la edición
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $canonical = trim($_POST['canonical_term']);
    $country_id = $_POST['country_id'] ?: null;

    $stmt = $pdo->prepare("UPDATE terms SET canonical_term = ?, country_id = ? WHERE id = ?");
    $stmt->execute([$canonical, $country_id, $id]);

    // Actualizamos las traducciones
    foreach($_POST['translation'] as $lang_id => $translation) {
        $definition = trim($_POST['definition'][$lang_id]);
        $translation = trim($translation);
        $check = $pdo->prepare("SELECT id FROM term_translations WHERE term_id = ? AND language_id = ?");
        $check->execute([$id, $lang_id]);
        if($check->fetch()) {
            $upd = $pdo->prepare("UPDATE term_translations SET translation=?, definition=? WHERE term_id=? AND language_id=?");
            $upd->execute([$translation, $definition, $id, $lang_id]);
        } else {
            if($translation !== '') {
                $ins = $pdo->prepare("INSERT INTO term_translations (term_id, language_id, translation, definition) VALUES (?, ?, ?, ?)");
                $ins->execute([$id, $lang_id, $translation, $definition]);
            }
        }
    }

    header('Location: view.php?id=' . $id);
    exit;
}

// Cargar traducciones actuales
$translations = $pdo->prepare("SELECT * FROM term_translations WHERE term_id = ?");
$translations->execute([$id]);
$translations = $translations->fetchAll(PDO::FETCH_UNIQUE);

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="m-0">Editar término</h2>
  <a class="btn btn-outline-secondary" href="view.php?id=<?php echo (int)$id; ?>">Volver</a>
</div>

<form method="post" class="row g-3">
  <div class="col-12 col-md-8">
    <label class="form-label">Término canónico</label>
    <input class="form-control" name="canonical_term" value="<?php echo htmlspecialchars($term['canonical_term']); ?>" required>
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">País</label>
    <select class="form-select" name="country_id">
      <option value="">-- ninguno --</option>
      <?php foreach($countries as $c): ?>
        <option value="<?php echo $c['id']; ?>" <?php if($term['country_id'] == $c['id']) echo 'selected'; ?>>
          <?php echo htmlspecialchars($c['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-12">
    <h4 class="mt-3">Traducciones / definiciones</h4>
  </div>
  <?php foreach($languages as $lang): 
    $t = $translations[$lang['id']] ?? null;
  ?>
    <div class="col-12">
      <div class="card mb-2">
        <div class="card-body">
          <h6 class="card-title mb-3"><?php echo htmlspecialchars($lang['name']); ?></h6>
          <div class="row g-2">
            <div class="col-12 col-md-6">
              <label class="form-label">Traducción</label>
              <input class="form-control" name="translation[<?php echo $lang['id']; ?>]" value="<?php echo htmlspecialchars($t['translation'] ?? ''); ?>">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Definición</label>
              <textarea class="form-control" name="definition[<?php echo $lang['id']; ?>]" rows="2"><?php echo htmlspecialchars($t['definition'] ?? ''); ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="col-12">
    <button class="btn btn-primary" type="submit">Guardar cambios</button>
  </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
