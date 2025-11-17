<?php
require_once __DIR__ . '/../config/conexion.php';

// Guardar los datos cuando el formulario se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correlativo = $_POST['correlativo'];
    $termino = $_POST['termino'];
    $pronunciacion = $_POST['pronunciacion'];
    $pais = $_POST['pais'];
    $definicion = $_POST['definicion'];
    $ejemplo = $_POST['ejemplo'];
    $referencia = $_POST['referencia'];

    $stmt = $conexion->prepare("
        INSERT INTO terminos 
        (correlativo, termino, pronunciacion, pais_origen, definicion, ejemplo_aplicativo, referencia)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issssss", $correlativo, $termino, $pronunciacion, $pais, $definicion, $ejemplo, $referencia);
    $stmt->execute();

    header("Location: index.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Término</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<div class="container">
    <h2 class="mb-4">Agregar Nuevo Término</h2>

    <form action="" method="POST">

        <div class="mb-3">
            <label class="form-label">Correlativo</label>
            <input type="number" name="correlativo" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Término</label>
            <input type="text" name="termino" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Pronunciación</label>
            <input type="text" name="pronunciacion" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">País de origen</label>
            <input type="text" name="pais" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Definición</label>
            <textarea name="definicion" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Ejemplo aplicativo</label>
            <textarea name="ejemplo" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Referencia / Bibliografía</label>
            <textarea name="referencia" class="form-control" rows="2"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>

    </form>
</div>

</body>
</html>

