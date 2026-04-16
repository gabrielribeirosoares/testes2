<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib_db.php';

$pdo = db();
$sql = 'SELECT c.id AS conciliacao_id, c.data_conciliacao, l.data_lancamento, l.documento, l.descricao, l.valor, l.tipo, l.conta
        FROM conciliacoes c
        INNER JOIN lancamentos_empresa l ON l.id = c.lancamento_id
        WHERE c.status = "CONCILIADO"
        ORDER BY c.data_conciliacao DESC';

$rows = $pdo->query($sql)->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=retorno_questor_' . date('Ymd_His') . '.csv');

$out = fopen('php://output', 'wb');
fputcsv($out, ['conciliacao_id', 'data_conciliacao', 'data_lancamento', 'documento', 'descricao', 'valor', 'tipo', 'conta']);

foreach ($rows as $row) {
    fputcsv($out, [
        $row['conciliacao_id'],
        $row['data_conciliacao'],
        $row['data_lancamento'],
        $row['documento'],
        $row['descricao'],
        $row['valor'],
        $row['tipo'],
        $row['conta'],
    ]);
}

fclose($out);
