<?php
require_once __DIR__ . '/../config/conexion.php';
if (session_status() == PHP_SESSION_NONE) session_start();

// Verificamos si el usuario está logueado y tiene permisos
if(!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../auth/login.php');
    exit;
}

include __DIR__ . '/../includes/header.php';

// Procesar validaciones enviadas por POST
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $term_id = $_POST['term_id'];

    if($action === 'validate') {
        // Cambiar estado a "validated"
        $pdo->prepare("UPDATE terms SET status = 'validated' WHERE id = ?")->execute([$term_id]);
        // Registrar quién validó
        $pdo->prepare("UPDATE term_translations SET validated_by = ?, validated_at = NOW() WHERE term_id = ?")
            ->execute([$_SESSION['user_id'], $term_id]);
    } elseif($action === 'reject') {
        // Cambiar estado a "rejected"
        $pdo->prepare("UPDATE terms SET status = 'rejected' WHERE id = ?")->execute([$term_id]);
    }

    // Redirigir para evitar reenvío de formularios
    header('Location: validate.php');
    exit;
}

// Mostrar traducciones pendientes
$sql = "SELECT tr.*, t.canonical_term, l.name as language, u.name as creator
        FROM term_translations tr
        JOIN terms t ON tr.term_id = t.id
        JOIN languages l ON tr.language_id = l.id
        JOIN users u ON t.creator_id = u.id
        WHERE t.status = 'pending'
        ORDER BY t.created_at DESC";

$rows = $pdo->query($sql)->fetchAll();
?>

<h2>Validación de Términos (Solo Docentes)</h2>

<?php if(empty($rows)): ?>
  <p>No hay términos pendientes de validación.</p>
<?php else: ?>
  <table border="1" cellpadding="6">
    <tr>
      <th>ID</th>
      <th>Término</th>
      <th>Idioma</th>
      <th>Traducción</th>
      <th>Definición</th>
      <th>Creado por</th>
      <th>Acciones</th>
    </tr>
    <?php foreach($rows as $r): ?>
    <tr>
      <td><?php echo $r['id']; ?></td>
      <td><?php echo htmlspecialchars($r['canonical_term']); ?></td>
      <td><?php echo htmlspecialchars($r['language']); ?></td>
      <td><?php echo htmlspecialchars($r['translation']); ?></td>
      <td><?php echo nl2br(htmlspecialchars($r['definition'])); ?></td>
      <td><?php echo htmlspecialchars($r['creator']); ?></td>
      <td>
        <form method="post" style="display:inline;">
          <input type="hidden" name="term_id" value="<?php echo $r['term_id']; ?>">
          <button type="submit" name="action" value="validate">✅ Validar</button>
        </form>
        <form method="post" style="display:inline;">
          <input type="hidden" name="term_id" value="<?php echo $r['term_id']; ?>">
          <button type="submit" name="action" value="reject">❌ Rechazar</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
