<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
set_time_limit(300); // por si es grande

// Rutas
$csvPath = __DIR__ . '/../data/terminos_clean.csv'; // asegúrate que este archivo existe
require_once __DIR__ . '/../config/conexion.php';    // usa tu conexion.php que define $pdo

if (!file_exists($csvPath)) {
    die("Error: no existe el archivo CSV en: $csvPath");
}
if (!isset($pdo)) {
    die("Error: no existe la conexión PDO (\$pdo). Revisa config/conexion.php");
}

$handle = fopen($csvPath, 'r');
if (!$handle) die("No pude abrir el archivo CSV.");

$lineNumber = 0;
$inserted = 0;
$skipped = 0;
$errors = [];

// Leer encabezado (primera fila)
$header = fgetcsv($handle, 0, ';', '"');
$lineNumber++;
if ($header === false) {
    die("CSV vacío o ilegible.");
}

// Normalizar encabezado: no lo usaremos para mapear por nombre, sino por orden.
// Pero mostramos header por control.
echo "<pre>Encabezado detectado:\n";
print_r($header);
echo "</pre>";

// Preparar sentencia (ajusta el nombre de la tabla/columnas según tu BD)
$sql = "INSERT INTO terminos 
    (correlativo, termino, pronunciacion, pais_origen, definicion, ejemplo, referencia)
    VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);

while (($row = fgetcsv($handle, 0, ';', '"')) !== false) {
    $lineNumber++;

    // Asegurarse que la fila tenga al menos 7 elementos (si tiene más unimos extras en la última)
    if (count($row) > 7) {
        $first6 = array_slice($row, 0, 6);
        $rest = array_slice($row, 6);
        $last = implode(';', $rest); // mantener ; internos
        $row = array_merge($first6, [$last]);
    } elseif (count($row) < 7) {
        // rellenar con vacíos
        while (count($row) < 7) $row[] = '';
    }

    // Trim y limpieza básica
    $row = array_map(function($v){ return $v === null ? '' : trim($v); }, $row);

    // Datos a insertar
    list($correlativo, $termino, $pronunciacion, $pais_origen, $definicion, $ejemplo, $referencia) = $row;

    // Opcional: validar que TÉRMINO no esté vacío (o cualquier regla)
    if ($termino === '') {
        $skipped++;
        $errors[] = "Línea $lineNumber: término vacío, fila saltada.";
        continue;
    }

    try {
        $stmt->execute([$correlativo, $termino, $pronunciacion, $pais_origen, $definicion, $ejemplo, $referencia]);
        $inserted++;
    } catch (Exception $e) {
        $skipped++;
        $errors[] = "Línea $lineNumber: error insertando -> " . $e->getMessage();
    }
}

fclose($handle);

echo "<h3>Resultado de la importación</h3>";
echo "<ul>";
echo "<li>Filas procesadas (excluyendo encabezado): " . ($lineNumber - 1) . "</li>";
echo "<li>Insertadas: $inserted</li>";
echo "<li>Saltadas: $skipped</li>";
echo "</ul>";

if (!empty($errors)) {
    echo "<h4>Errores / advertencias:</h4><pre>";
    foreach ($errors as $err) echo $err . "\n";
    echo "</pre>";
}

echo "<p>Listo. Comprueba ahora en <a href='/glosario/terms/index.php'>Explorar términos</a>.</p>";
