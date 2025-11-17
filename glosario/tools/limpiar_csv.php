<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

// Rutas
$in  = __DIR__ . '/../data/terminos.csv';
$out = __DIR__ . '/../data/terminos_clean.csv';

if (!file_exists($in)) {
    die("No existe el archivo: $in");
}

$fin = fopen($in, 'r');
$fout = fopen($out, 'w');

while (($line = fgets($fin)) !== false) {

    // Quitar saltos de línea
    $line = trim($line);

    // Quitar TODOS los ; extra del final
    // (si hay 3, 4, 10, los corta)
    $line = preg_replace('/;+\s*$/', '', $line);

    // Separar
    $parts = explode(';', $line);

    // Si hay más de 7 columnas → unir todas las sobrantes en la última
    if (count($parts) > 7) {
        $first6 = array_slice($parts, 0, 6);
        $rest = array_slice($parts, 6);
        $last = implode(' ', $rest);
        $parts = array_merge($first6, [$last]);
    }

    // Si tiene menos de 7 columnas → rellenar
    while(count($parts) < 7){
        $parts[] = '';
    }

    // Escribir CSV limpio
    fputcsv($fout, $parts, ';', '"');
}

fclose($fin);
fclose($fout);

echo "LISTO: archivo limpio creado → $out";

