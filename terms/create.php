<?php
// Crear término: formulario con Bootstrap
require_once __DIR__ . '/../config/conexion.php';
if (session_status() == PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
include __DIR__ . '/../includes/header.php';

$countries = $pdo->query("SELECT * FROM countries")->fetchAll();
$languages = $pdo->query("SELECT * FROM languages")->fetchAll();

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $canonical = trim($_POST['canonical_term'] ?? '');
    $country_id = $_POST['country_id'] ?: null;
    if(!$canonical) $errors[] = "El término canónico es obligatorio.";

    if(empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO terms (creator_id, country_id, canonical_term) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $country_id, $canonical]);
        $term_id = $pdo->lastInsertId();

        foreach($_POST['translation'] as $lang_id => $translation) {
            $translation = trim($translation);
            $definition = trim($_POST['definition'][$lang_id] ?? '');
            if($translation !== '') {
                $ins = $pdo->prepare("INSERT INTO term_translations (term_id, language_id, translation, definition) VALUES (?, ?, ?, ?)");
                $ins->execute([$term_id, $lang_id, $translation, $definition]);
            }
        }
        header('Location: index.php');
        exit;
    }
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="m-0">Agregar término</h2>
  <a class="btn btn-outline-secondary" href="index.php">Volver</a>
</div>

<?php if($errors): ?>
  <div class="alert alert-danger"><?php foreach($errors as $err) echo htmlspecialchars($err)."<br>"; ?></div>
<?php endif; ?>

<form method="post" class="row g-3">
  <div class="col-12 col-md-8">
    <label class="form-label">Término canónico</label>
    <input class="form-control" name="canonical_term" required>
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">País (aplicabilidad)</label>
    <select class="form-select" name="country_id">
      <option value="">-- ninguno --</option>
      <?php foreach($countries as $c): ?>
        <option value="<?php echo $c['id'];?>"><?php echo htmlspecialchars($c['name']);?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-12">
    <h4 class="mt-3">Traducciones / definiciones</h4>
  </div>
  <?php foreach($languages as $lang): ?>
    <div class="col-12">
      <div class="card mb-2">
        <div class="card-body">
          <h6 class="card-title mb-3"><?php echo htmlspecialchars($lang['name']);?></h6>
          <div class="row g-2">
            <div class="col-12 col-md-6">
              <label class="form-label">Traducción</label>
              <input class="form-control" name="translation[<?php echo $lang['id'];?>]">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Definición / contexto</label>
              <textarea class="form-control" rows="2" name="definition[<?php echo $lang['id'];?>]"></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="col-12">
    <button type="submit" class="btn btn-primary">Guardar</button>
  </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
