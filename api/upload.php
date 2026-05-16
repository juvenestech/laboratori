<?php
header("Access-Control-Allow-Origin: https://laboratori.juvenes.it");
header("Content-Type: application/json; charset=UTF-8");

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/auth.php";
include_once $path;

if (!$AUTH) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorizzato"]);
    return;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Metodo non consentito"]);
    return;
}

if (!isset($_FILES['gif']) || !is_array($_FILES['gif'])) {
    http_response_code(400);
    echo json_encode(["error" => "Nessun file ricevuto. Usa il campo 'gif'."]);
    return;
}

$file = $_FILES['gif'];

// Errori di upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errMap = [
        UPLOAD_ERR_INI_SIZE   => "File troppo grande (limite server)",
        UPLOAD_ERR_FORM_SIZE  => "File troppo grande (limite form)",
        UPLOAD_ERR_PARTIAL    => "Upload incompleto",
        UPLOAD_ERR_NO_FILE    => "Nessun file caricato",
        UPLOAD_ERR_NO_TMP_DIR => "Directory temporanea mancante",
        UPLOAD_ERR_CANT_WRITE => "Impossibile scrivere su disco",
        UPLOAD_ERR_EXTENSION  => "Estensione PHP bloccante",
    ];
    $msg = $errMap[$file['error']] ?? "Errore di upload (codice " . $file['error'] . ")";
    http_response_code(400);
    echo json_encode(["error" => $msg]);
    return;
}

// Validazione dimensione (max 5MB)
$maxBytes = 5 * 1024 * 1024;
if ($file['size'] > $maxBytes) {
    http_response_code(400);
    echo json_encode(["error" => "File troppo grande. Limite: 5MB."]);
    return;
}

// Validazione estensione
$allowedExts = ['gif', 'jpg', 'jpeg', 'png', 'webp'];
$origName = $file['name'];
$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExts, true)) {
    http_response_code(400);
    echo json_encode(["error" => "Estensione non consentita. Ammesse: " . implode(', ', $allowedExts)]);
    return;
}

// Validazione MIME type effettivo
$allowedMimes = [
    'image/gif',
    'image/jpeg',
    'image/png',
    'image/webp'
];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
if ($finfo) finfo_close($finfo);
if ($mime && !in_array($mime, $allowedMimes, true)) {
    http_response_code(400);
    echo json_encode(["error" => "Tipo file non riconosciuto come immagine valida ($mime)."]);
    return;
}

// Genera nome sicuro: slug del nome originale + timestamp
$base = pathinfo($origName, PATHINFO_FILENAME);
$slug = strtolower(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $base));
$slug = trim($slug, '-');
if ($slug === '') $slug = 'image';
// Tronca a 60 caratteri
$slug = substr($slug, 0, 60);
$filename = $slug . '-' . time() . '.' . $ext;

// Directory destinazione
$relDir = 'assets/img/gif/';
$destDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $relDir;
if (!is_dir($destDir)) {
    if (!mkdir($destDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(["error" => "Impossibile creare la directory di destinazione."]);
        return;
    }
}

$destPath = $destDir . $filename;

// Evita collisioni
$counter = 1;
while (file_exists($destPath)) {
    $filename = $slug . '-' . time() . '-' . $counter . '.' . $ext;
    $destPath = $destDir . $filename;
    $counter++;
}

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    http_response_code(500);
    echo json_encode(["error" => "Errore nel salvataggio del file."]);
    return;
}

// Imposta permessi leggibili dal webserver
@chmod($destPath, 0644);

echo json_encode([
    "success" => true,
    "path" => $relDir . $filename,
    "filename" => $filename,
    "size" => $file['size'],
    "mime" => $mime
]);
