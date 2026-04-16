<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib_db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['arquivo'])) {
    flash('Requisição inválida para importação de extrato.', 'error');
    header('Location: /index.php');
    exit;
}

$tmpPath = $_FILES['arquivo']['tmp_name'] ?? '';
if (!is_uploaded_file($tmpPath)) {
    flash('Arquivo inválido.', 'error');
    header('Location: /index.php');
    exit;
}

$handle = fopen($tmpPath, 'rb');
if ($handle === false) {
    flash('Não foi possível abrir o arquivo.', 'error');
    header('Location: /index.php');
    exit;
}

$pdo = db();
$sql = 'INSERT INTO extrato_bancario (data_movimento, historico, valor, conta, doc_ref) VALUES (:data_movimento, :historico, :valor, :conta, :doc_ref)';
$stmt = $pdo->prepare($sql);

$header = fgetcsv($handle, 0, ',');
$importados = 0;

while (($row = fgetcsv($handle, 0, ',')) !== false) {
    if (count($row) < 5) {
        continue;
    }

    $stmt->execute([
        ':data_movimento' => trim($row[0]),
        ':historico' => trim($row[1]),
        ':valor' => (float) str_replace(',', '.', $row[2]),
        ':conta' => trim($row[3]),
        ':doc_ref' => trim($row[4]),
    ]);

    $importados++;
}

fclose($handle);
flash("Importação de extrato concluída: {$importados} movimentos importados.");
header('Location: /index.php');
