<?php
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=report_scelte.csv");

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/auth.php";
include_once $path;

if(!$AUTH) {
    http_response_code(401);
    echo "Non autorizzato";
    return;
}

if (!isset($_GET['settimana'])) {
    http_response_code(400);
    echo "Parametro settimana mancante";
    return;
}

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/models/scelte.php";
include_once $path;

$scelte = new Scelte();
$report = $scelte->getReport($_GET['settimana']);

if (!$report || empty($report)) {
    echo "Nessun dato disponibile";
    return;
}

// Raggruppa le scelte per iscritto
$iscritti = [];
foreach ($report as $row) {
    $key = $row['iscritto'];
    if (!isset($iscritti[$key])) {
        $iscritti[$key] = [
            'iscritto' => $row['iscritto'],
            'codice' => $row['codice'],
            'scelte' => []
        ];
    }
    $iscritti[$key]['scelte'][] = $row['laboratorio_nome'];
}

// Determina il numero massimo di scelte per l'header
$max_scelte = 0;
foreach ($iscritti as $i) {
    $max_scelte = max($max_scelte, count($i['scelte']));
}

// Output CSV
$output = fopen('php://output', 'w');

// BOM UTF-8 per Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header
$header = ['Iscritto', 'Codice'];
for ($j = 1; $j <= $max_scelte; $j++) {
    $header[] = "Scelta $j";
}
fputcsv($output, $header, ';');

// Righe dati
foreach ($iscritti as $i) {
    $row = [$i['iscritto'], $i['codice']];
    for ($j = 0; $j < $max_scelte; $j++) {
        $row[] = $i['scelte'][$j] ?? '';
    }
    fputcsv($output, $row, ';');
}

fclose($output);
