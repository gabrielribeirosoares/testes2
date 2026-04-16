<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib_db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Requisição inválida.', 'error');
    header('Location: /index.php');
    exit;
}

$lancamentoId = (int) ($_POST['lancamento_id'] ?? 0);
$extratoId = (int) ($_POST['extrato_id'] ?? 0);
$status = strtoupper(trim((string) ($_POST['status'] ?? 'DIVERGENTE')));

if ($lancamentoId <= 0 || $extratoId <= 0) {
    flash('Selecione itens válidos para conciliar.', 'error');
    header('Location: /index.php');
    exit;
}

if (!in_array($status, ['CONCILIADO', 'DIVERGENTE'], true)) {
    $status = 'DIVERGENTE';
}

$pdo = db();

$jaExiste = $pdo->prepare('SELECT id FROM conciliacoes WHERE lancamento_id = :lancamento OR extrato_id = :extrato LIMIT 1');
$jaExiste->execute([':lancamento' => $lancamentoId, ':extrato' => $extratoId]);
if ($jaExiste->fetch()) {
    flash('Um dos itens já foi conciliado anteriormente.', 'error');
    header('Location: /index.php');
    exit;
}

$pdo->beginTransaction();

$insert = $pdo->prepare('INSERT INTO conciliacoes (lancamento_id, extrato_id, status, data_conciliacao) VALUES (:lancamento, :extrato, :status, NOW())');
$insert->execute([
    ':lancamento' => $lancamentoId,
    ':extrato' => $extratoId,
    ':status' => $status,
]);

$updateLanc = $pdo->prepare('UPDATE lancamentos_empresa SET status_conciliacao = :status WHERE id = :id');
$updateLanc->execute([
    ':status' => $status,
    ':id' => $lancamentoId,
]);

$pdo->commit();

flash('Conciliação registrada com sucesso.');
header('Location: /index.php');
