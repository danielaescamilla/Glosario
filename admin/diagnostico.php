<?php
// Consola de diagnóstico para conexión a DB y usuario admin
// Acceso recomendado solo desde localhost

if (php_sapi_name() !== 'cli') {
    $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);
    if (!$isLocal) {
        http_response_code(403);
        echo "Acceso restringido: ejecute esta consola desde localhost.";
        exit;
    }
}

require_once __DIR__ . '/../config/conexion.php';

// CSRF simple para acciones de escritura
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['diag_csrf'])) $_SESSION['diag_csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['diag_csrf'];

$info = [
    'conexion' => [
        'ok' => false,
        'mensaje' => '',
        'detalles' => []
    ],
    'estructura' => [
        'tabla_users_existe' => null,
        'conteo_users' => null,
        'conteo_admins' => null,
        'ejemplo_admin' => null,
        'errores' => []
    ],
    'verificacion' => [
        'email' => '',
        'password_ok' => null,
        'usuario_encontrado' => null,
        'errores' => []
    ],
    'reset' => [
        'ok' => null,
        'mensaje' => '',
        'errores' => []
    ]
];

// 1) Probar conexión y obtener metadatos
try {
    $stmt = $pdo->query('SELECT 1');
    $stmt->fetch();
    $info['conexion']['ok'] = true;
    $info['conexion']['mensaje'] = 'Conexión establecida y consulta básica exitosa.';
    try {
        $info['conexion']['detalles']['driver'] = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $info['conexion']['detalles']['server_version'] = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        $info['conexion']['detalles']['client_version'] = $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);
    } catch (Throwable $t) {
        $info['conexion']['detalles']['aviso'] = 'No fue posible leer atributos del servidor: ' . $t->getMessage();
    }
} catch (Throwable $e) {
    $info['conexion']['ok'] = false;
    $info['conexion']['mensaje'] = 'Fallo de conexión o consulta: ' . $e->getMessage();
}

// 2) Comprobación de estructura y usuarios
try {
    // ¿Existe la tabla users?
    $sqlExiste = "SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'users'";
    $c = (int)$pdo->query($sqlExiste)->fetchColumn();
    $info['estructura']['tabla_users_existe'] = $c > 0;

    if ($c > 0) {
        $info['estructura']['conteo_users'] = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $info['estructura']['conteo_admins'] = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

        $ej = $pdo->query("SELECT id, name, email, role FROM users WHERE role = 'admin' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($ej) {
            // No mostramos datos sensibles
            $info['estructura']['ejemplo_admin'] = [
                'id' => $ej['id'] ?? null,
                'name' => $ej['name'] ?? null,
                'email' => $ej['email'] ?? null,
                'role' => $ej['role'] ?? null,
            ];
        }
    }
} catch (Throwable $e) {
    $info['estructura']['errores'][] = $e->getMessage();
}

// 3) Acciones POST: verificación de credenciales o reset de admin
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? 'verify';

    if ($action === 'verify') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $info['verificacion']['email'] = $email;

        if ($email === '' || $password === '') {
            $info['verificacion']['errores'][] = 'Email y contraseña son requeridos.';
        } else {
            try {
                $st = $pdo->prepare('SELECT id, email, name, role, password_hash FROM users WHERE email = ? LIMIT 1');
                $st->execute([$email]);
                $u = $st->fetch(PDO::FETCH_ASSOC);
                if ($u) {
                    $info['verificacion']['usuario_encontrado'] = [
                        'id' => $u['id'],
                        'email' => $u['email'],
                        'name' => $u['name'],
                        'role' => $u['role'],
                    ];
                    $info['verificacion']['password_ok'] = password_verify($password, $u['password_hash']);
                } else {
                    $info['verificacion']['usuario_encontrado'] = null;
                }
            } catch (Throwable $e) {
                $info['verificacion']['errores'][] = $e->getMessage();
            }
        }
    } elseif ($action === 'reset_admin') {
        // Protección CSRF
        if (!isset($_POST['csrf']) || !hash_equals($csrf, (string)$_POST['csrf'])) {
            $info['reset']['ok'] = false;
            $info['reset']['errores'][] = 'Token CSRF inválido.';
        } else {
            $adminEmail = trim($_POST['admin_email'] ?? '');
            $adminName = trim($_POST['admin_name'] ?? 'Admin');
            $adminPass = $_POST['admin_password'] ?? '';

            if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                $info['reset']['errores'][] = 'Email inválido.';
            }
            if (strlen($adminPass) < 6) {
                $info['reset']['errores'][] = 'La contraseña debe tener al menos 6 caracteres.';
            }
            if (empty($info['reset']['errores'])) {
                try {
                    $hash = password_hash($adminPass, PASSWORD_DEFAULT);
                    $pdo->beginTransaction();
                    $sel = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
                    $sel->execute([$adminEmail]);
                    $row = $sel->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        $upd = $pdo->prepare('UPDATE users SET name = ?, role = "admin", password_hash = ? WHERE id = ?');
                        $upd->execute([$adminName, $hash, $row['id']]);
                        $info['reset']['mensaje'] = 'Admin actualizado correctamente.';
                    } else {
                        $ins = $pdo->prepare('INSERT INTO users (name, email, role, password_hash) VALUES (?, ?, "admin", ?)');
                        $ins->execute([$adminName, $adminEmail, $hash]);
                        $info['reset']['mensaje'] = 'Admin creado correctamente.';
                    }
                    $pdo->commit();
                    $info['reset']['ok'] = true;
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    $info['reset']['ok'] = false;
                    $info['reset']['errores'][] = $e->getMessage();
                }
            } else {
                $info['reset']['ok'] = false;
            }
        }
    }
}

function badge($cond) {
    if ($cond === true) return '<span style="color:#155724;background:#d4edda;padding:2px 6px;border-radius:4px;">OK</span>';
    if ($cond === false) return '<span style="color:#721c24;background:#f8d7da;padding:2px 6px;border-radius:4px;">FALLO</span>';
    return '<span style="color:#856404;background:#fff3cd;padding:2px 6px;border-radius:4px;">N/D</span>';
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Consola de diagnóstico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial; background:#f5f7fb; margin:0; padding:20px; }
    .card { background:#fff; border-radius:10px; padding:16px 20px; box-shadow:0 2px 12px rgba(0,0,0,.06); margin-bottom:16px; }
    h1 { margin:0 0 10px; font-size:20px; }
    h2 { margin:0 0 10px; font-size:18px; }
    .kv { display:flex; gap:10px; margin:6px 0; }
    .kv .k { width:220px; color:#555; }
    .kv .v { color:#111; }
    .err { color:#721c24; background:#f8d7da; border-radius:6px; padding:6px 8px; margin:6px 0; }
    .ok { color:#155724; background:#d4edda; border-radius:6px; padding:6px 8px; margin:6px 0; }
    form { display:flex; gap:10px; align-items:center; margin-top:8px; }
    input[type=email], input[type=password]{ padding:8px; border:1px solid #ccc; border-radius:6px; }
    button { padding:8px 12px; background:#0069d9; color:#fff; border:none; border-radius:6px; cursor:pointer; }
  </style>
  <meta name="robots" content="noindex,nofollow" />
</head>
<body>
  <div class="card">
    <h1>Consola de diagnóstico</h1>
    <div class="kv"><div class="k">Conexión</div><div class="v"><?php echo badge($info['conexion']['ok']); ?></div></div>
    <div class="kv"><div class="k">Mensaje</div><div class="v"><?php echo htmlspecialchars($info['conexion']['mensaje']); ?></div></div>
    <?php if (!empty($info['conexion']['detalles'])): ?>
      <?php foreach ($info['conexion']['detalles'] as $k=>$v): ?>
        <div class="kv"><div class="k"><?php echo htmlspecialchars($k); ?></div><div class="v"><?php echo htmlspecialchars((string)$v); ?></div></div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2>Comprobaciones de estructura</h2>
    <div class="kv"><div class="k">Tabla users existe</div><div class="v"><?php echo badge($info['estructura']['tabla_users_existe']); ?></div></div>
    <div class="kv"><div class="k">Usuarios totales</div><div class="v"><?php echo $info['estructura']['conteo_users'] !== null ? (int)$info['estructura']['conteo_users'] : 'N/D'; ?></div></div>
    <div class="kv"><div class="k">Admins totales</div><div class="v"><?php echo $info['estructura']['conteo_admins'] !== null ? (int)$info['estructura']['conteo_admins'] : 'N/D'; ?></div></div>
    <?php if (!empty($info['estructura']['ejemplo_admin'])): ?>
      <div class="kv"><div class="k">Admin ejemplo</div><div class="v"><?php echo htmlspecialchars($info['estructura']['ejemplo_admin']['email'] . ' (' . $info['estructura']['ejemplo_admin']['name'] . ')'); ?></div></div>
    <?php endif; ?>
    <?php foreach (($info['estructura']['errores'] ?? []) as $e): ?>
      <div class="err"><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <h2>Verificar credenciales</h2>
    <p>Prueba si un email y contraseña coinciden con el hash almacenado.</p>
    <form method="post" class="row g-2 align-items-end">
      <input type="hidden" name="action" value="verify">
      <div class="col-12 col-md-5"><input class="form-control" type="email" name="email" placeholder="email del admin" value="<?php echo htmlspecialchars($info['verificacion']['email']); ?>" required></div>
      <div class="col-12 col-md-5"><input class="form-control" type="password" name="password" placeholder="contraseña" required></div>
      <div class="col-12 col-md-2"><button class="btn btn-primary w-100" type="submit">Probar</button></div>
    </form>
    <?php if ($info['verificacion']['usuario_encontrado'] !== null): ?>
      <div class="ok">Usuario encontrado: <?php echo htmlspecialchars($info['verificacion']['usuario_encontrado']['email']); ?> (rol: <?php echo htmlspecialchars($info['verificacion']['usuario_encontrado']['role']); ?>)</div>
      <div class="kv"><div class="k">Contraseña válida</div><div class="v"><?php echo badge($info['verificacion']['password_ok']); ?></div></div>
    <?php elseif ($info['verificacion']['email'] !== ''): ?>
      <div class="err">No se encontró usuario con ese email.</div>
    <?php endif; ?>
    <?php foreach (($info['verificacion']['errores'] ?? []) as $e): ?>
      <div class="err"><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <h2>Crear / Restablecer admin</h2>
    <p>Actualiza o crea un usuario con rol admin. Restringido a localhost.</p>
    <form method="post" autocomplete="off" class="row g-2 align-items-end">
      <input type="hidden" name="action" value="reset_admin">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
      <div class="col-12 col-md-3"><input class="form-control" type="text" name="admin_name" placeholder="Nombre" value="Admin" required></div>
      <div class="col-12 col-md-4"><input class="form-control" type="email" name="admin_email" placeholder="email del admin" required></div>
      <div class="col-12 col-md-3"><input class="form-control" type="password" name="admin_password" placeholder="nueva contraseña" required></div>
      <div class="col-12 col-md-2"><button class="btn btn-success w-100" type="submit">Crear/Restablecer</button></div>
    </form>
    <?php if ($info['reset']['ok'] === true): ?>
      <div class="ok"><?php echo htmlspecialchars($info['reset']['mensaje'] ?: 'Operación realizada.'); ?></div>
    <?php elseif ($info['reset']['ok'] === false): ?>
      <?php if (!empty($info['reset']['mensaje'])): ?>
        <div class="err"><?php echo htmlspecialchars($info['reset']['mensaje']); ?></div>
      <?php endif; ?>
      <?php foreach (($info['reset']['errores'] ?? []) as $e): ?>
        <div class="err"><?php echo htmlspecialchars($e); ?></div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2>Consejos ante errores comunes</h2>
    <ul>
      <li>"Access denied" (SQLSTATE 28000/1045): revisa usuario/contraseña de DB.</li>
      <li>"Unknown database" (HY000/1049): revisa el nombre de la base en conexion.php.</li>
      <li>Si no hay admins: crea uno insertando en tabla users con password_hash().</li>
      <li>El login usa campo email y la columna password_hash para verificar.</li>
    </ul>
  </div>
</body>
</html>