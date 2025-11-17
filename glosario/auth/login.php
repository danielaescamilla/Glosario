<?php
require_once __DIR__ . '/../config/conexion.php';
if (session_status() == PHP_SESSION_NONE) session_start();

// Si ya hay sesión iniciada, redirigir acorde al rol
if (isset($_SESSION['user_id'])) {
  if (($_SESSION['role'] ?? '') === 'student') {
    header('Location: ../alumno/dashboard.php');
  } else {
    header('Location: ../index.php');
  }
  exit;
}

$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if(!$email || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$password) {
        $errors[] = "Por favor ingresa un email válido y contraseña.";
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, role, password_hash FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $verified = false;
        if ($user) {
            $hash = (string)($user['password_hash'] ?? '');
            if ($hash !== '' && password_verify($password, $hash)) {
                $verified = true;
            } else {
                $looksHashed = preg_match('/^(\$2y\$|\$argon2i\$|\$argon2id\$)/', $hash) === 1;
                if (!$looksHashed && $password !== '' && $password === $hash) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                    $upd->execute([$newHash, (int)$user['id']]);
                    $verified = true;
                    $hash = $newHash;
                }
            }
        }

        if($user && $verified) {
            if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                $upd->execute([$newHash, (int)$user['id']]);
                $hash = $newHash;
            }
            $studentMode = !empty($_POST['student_mode']);
            $role = $user['role'] ?? '';

            if ($studentMode && $role !== 'student') {
                $errors[] = "Seleccionaste acceso de estudiante pero la cuenta no es estudiante.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                session_regenerate_id(true);

                if ($role === 'student') {
                    header('Location: ../alumno/dashboard.php');
                } else {
                    header('Location: ../index.php');
                }
                exit;
            }
        } else {
            $errors[] = "Correo o contraseña incorrectos.";
        }
    }
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar sesión - Glosario Jurídico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="text-center mb-3">
              <img src="../img/uped.png" alt="Logo Universidad" class="img-fluid" style="max-width:100px;">
            </div>
            <h4 class="text-primary text-center">Glosario Jurídico</h4>
            <p class="text-muted text-center mb-4">Inicia sesión para acceder al panel principal</p>

            <?php if($errors): ?>
              <div class="alert alert-danger" role="alert">
                <?php foreach($errors as $e) echo htmlspecialchars($e)."<br>"; ?>
              </div>
            <?php endif; ?>

            <form method="post" novalidate>
              <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="ejemplo@correo.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="student_mode" name="student_mode" <?php echo !empty($_POST['student_mode']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="student_mode">Soy estudiante</label>
              </div>
              <button type="submit" class="btn btn-primary w-100">Ingresar</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>



 