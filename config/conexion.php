<?php
// ===============================================
// CONFIGURACIÓN DE CONEXIÓN A LA BASE DE DATOS
// ===============================================

// Datos de conexión
$host = 'localhost';        // Servidor local
$dbname = 'glosario_db';    // Nombre de tu base de datos en phpMyAdmin
$username = 'root';         // Usuario por defecto de XAMPP
$password = '';             // Contraseña (vacía en XAMPP por defecto)

try {
    // Crear conexión con PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Configurar atributos de PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // ✅ Si llega hasta aquí, la conexión fue exitosa
    // (No mostramos mensaje para no interrumpir el flujo del sistema)
} catch (PDOException $e) {
    // ❌ Si hay error, mostrar mensaje claro
    die("❌ Error en la conexión a la base de datos: " . $e->getMessage());
}
?>

