<?php
$host = 'localhost';
$dbname = 'glosario_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    echo "✅ Conexión exitosa a la base de datos: $dbname";
} catch (PDOException $e) {
    echo "❌ Error en la conexión: " . $e->getMessage();
}
?>
