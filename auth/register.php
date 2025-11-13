<?php
require_once __DIR__ . '/../config/conexion.php';
if (session_status() == PHP_SESSION_NONE) session_start();

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    if(!$name || !$email || !$password) {
        $errors[] = "Completa todos los campos.";
    }

    if(empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$name, $email, $hash, $role]);
            header('Location: login.php?registered=1');
            exit;
        } catch(PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
include __DIR__ . '/../includes/header.php';
?>
<h2>Registrarse</h2>
<?php if($errors): foreach($errors as $err): ?>
  <p style="color:red;"><?php echo htmlspecialchars($err); ?></p>
<?php endforeach; endif; ?>
<form method="post">
  <label>Nombre<br><input name="name" required></label><br>
  <label>Email<br><input name="email" type="email" required></label><br>
  <label>Contraseña<br><input name="password" type="password" required></label><br>
  <label>Rol<br>
    <select name="role">
      <option value="student">Estudiante</option>
      <option value="teacher">Docente</option>
    </select>
  </label><br>
  <button type="submit">Registrar</button>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
