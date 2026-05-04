<?php
// src/controllers/invoice.php — serves invoice files (auth gated by router)

if (!$id) { http_response_code(404); echo 'Missing id'; return; }

$stmt = $pdo->prepare('SELECT invoice_file FROM maintenance_records WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row || !$row['invoice_file']) {
    http_response_code(404); echo 'No invoice'; return;
}

$base = realpath(uploads_dir());
$file = realpath($base . '/' . $row['invoice_file']);

if (!$file || !$base || !str_starts_with($file, $base) || !is_file($file)) {
    http_response_code(404); echo 'File not found'; return;
}

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mime = match($ext) {
    'pdf'  => 'application/pdf',
    'jpg', 'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
    default => 'application/octet-stream',
};
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($file));
header('Content-Disposition: inline; filename="invoice.' . $ext . '"');
readfile($file);
exit;
