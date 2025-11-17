<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

$archivo = __DIR__ . '/../data/terminos_clean.csv';  // <-- o terminos.csv si ese estás usando

if (!file_exists($archivo)) {
    die("No existe el archivo CSV: $archivo");
}

$linea_num = 0;
$fh = fopen($archivo, 'r');

echo "<pre>";

while (($line = fgets($fh)) !== false) {
    $linea_num++;
    $line = rtrim($line, "\r\n");

    // Separar por ;
    $cols = explode(';', $line);

    $num_cols = count($cols);

    echo "Línea $linea_num → $num_cols columnas\n";

    // Mostrar la línea si está mal
    if ($num_cols != 7) {
        echo "❌ ESTA LÍNEA ESTÁ MAL:\n$line\n\n";
    }
}

echo "</pre>";
fclose($fh);
