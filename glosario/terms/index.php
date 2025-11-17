<?php
require_once __DIR__ . '/../config/conexion.php';

// Obtener todos los términos
$sql = "SELECT correlativo, termino, pronunciacion, pais_origen, definicion, ejemplo, referencia 
        FROM terminos";
$stmt = $pdo->query($sql);
$terminos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h2 class="text-center mb-4">📚 Explorar Términos del Glosario</h2>

    <table class="table table-bordered table-striped">
        <thead class="table-dark text-center">
            <tr>
                <th>Correlativo</th>
                <th>Término</th>
                <th>Pronunciación</th>
                <th>País de origen</th>
                <th>Definición</th>
                <th>Ejemplo aplicativo</th>
                <th>Referencia / Bibliografía</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($terminos as $fila): ?>
            <tr>
                <td><?= htmlspecialchars($fila['correlativo']) ?></td>
                <td><?= htmlspecialchars($fila['termino']) ?></td>
                <td><?= htmlspecialchars($fila['pronunciacion']) ?></td>
                <td><?= htmlspecialchars($fila['pais_origen']) ?></td>
                <td><?= htmlspecialchars($fila['definicion']) ?></td>
                <td><?= htmlspecialchars($fila['ejemplo']) ?></td>
                <td><?= htmlspecialchars($fila['referencia']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
