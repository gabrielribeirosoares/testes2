<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib_db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['arquivo'])) {
    flash('Requisição inválida para importação Questor.', 'error');
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
$sql = 'INSERT INTO lancamentos_empresa (data_lancamento, documento, descricao, valor, tipo, conta, status_conciliacao) VALUES (:data_lancamento, :documento, :descricao, :valor, :tipo, :conta, "PENDENTE")';
$stmt = $pdo->prepare($sql);

$header = fgetcsv($handle, 0, ',');
$importados = 0;

while (($row = fgetcsv($handle, 0, ',')) !== false) {
    if (count($row) < 6) {
        continue;
    }

    $stmt->execute([
        ':data_lancamento' => trim($row[0]),
        ':documento' => trim($row[1]),
        ':descricao' => trim($row[2]),
        ':valor' => (float) str_replace(',', '.', $row[3]),
        ':tipo' => strtoupper(trim($row[4])),
        ':conta' => trim($row[5]),
    ]);

    $importados++;
}

fclose($handle);
flash("Importação Questor concluída: {$importados} lançamentos importados.");
header('Location: /index.php');
