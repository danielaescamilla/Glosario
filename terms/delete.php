<?php
require_once __DIR__ . '/../config/conexion.php';
if (session_status() == PHP_SESSION_NONE) session_start();

if(!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if(!$id) die("ID no proporcionado.");

// Verificar que exista
$stmt = $pdo->prepare("SELECT * FROM terms WHERE id = ?");
$stmt->execute([$id]);
$term = $stmt->fetch();

if(!$term) die("Término no encontrado.");

// Verificar permisos
if($_SESSION['user_id'] != $term['creator_id'] && $_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'admin') {
    die("No tienes permiso para eliminar este término.");
}

// Borrar traducciones primero
$pdo->prepare("DELETE FROM term_translations WHERE term_id = ?")->execute([$id]);
// Borrar término principal
$pdo->prepare("DELETE FROM terms WHERE id = ?")->execute([$id]);

header('Location: index.php');
exit;
