<?php

declare(strict_types=1);

require_once __DIR__ . '/lib_db.php';

$pdo = db();
$flash = pullFlash();

$pendentes = (int) $pdo->query("SELECT COUNT(*) FROM lancamentos_empresa WHERE status_conciliacao = 'PENDENTE'")->fetchColumn();
$conciliados = (int) $pdo->query("SELECT COUNT(*) FROM conciliacoes")->fetchColumn();
$extratoSemMatch = (int) $pdo->query("SELECT COUNT(*) FROM extrato_bancario e LEFT JOIN conciliacoes c ON c.extrato_id = e.id WHERE c.id IS NULL")->fetchColumn();

$lancamentos = $pdo->query("SELECT * FROM lancamentos_empresa WHERE status_conciliacao = 'PENDENTE' ORDER BY data_lancamento ASC LIMIT 50")->fetchAll();
$extratos = $pdo->query("SELECT e.* FROM extrato_bancario e LEFT JOIN conciliacoes c ON c.extrato_id = e.id WHERE c.id IS NULL ORDER BY e.data_movimento ASC LIMIT 50")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Conciliação Bancária - Questor</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f3f5f8; margin:0; }
        .wrap { max-width: 1200px; margin: 20px auto; padding: 0 16px; }
        .card { background:#fff; border-radius:10px; padding:16px; box-shadow:0 2px 8px rgba(0,0,0,.07); margin-bottom:16px; }
        h1,h2 { margin-top:0; }
        .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px,1fr)); gap:12px; }
        .metric strong { display:block; font-size:28px; margin-top:6px; }
        .flash-success{ background:#e9f8ef; border:1px solid #9fd7af; color:#125c2a; padding:10px; border-radius:8px; }
        .flash-error{ background:#fdeceb; border:1px solid #f1a8a3; color:#7d1711; padding:10px; border-radius:8px; }
        form { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        input, select, button { padding:8px; border-radius:6px; border:1px solid #cfd5dc; }
        button { background:#155eef; color:#fff; border:none; cursor:pointer; }
        table { width:100%; border-collapse:collapse; font-size:14px; }
        th, td { border-bottom:1px solid #eceff3; padding:8px; text-align:left; }
        .columns { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        @media(max-width:900px){ .columns { grid-template-columns:1fr; } }
        .muted { color:#667085; font-size:13px; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>SaaS de Conciliação Bancária (Questor)</h1>
    <p class="muted">Importe arquivos CSV do Questor, importe extrato bancário e faça conciliação manual com exportação para retorno.</p>

    <?php if ($flash): ?>
        <div class="<?= $flash['type'] === 'error' ? 'flash-error' : 'flash-success' ?> card">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="grid">
        <div class="card metric">Lançamentos pendentes<strong><?= $pendentes ?></strong></div>
        <div class="card metric">Conciliações realizadas<strong><?= $conciliados ?></strong></div>
        <div class="card metric">Extrato sem match<strong><?= $extratoSemMatch ?></strong></div>
    </div>

    <div class="card">
        <h2>Importações</h2>
        <div class="grid">
            <div>
                <h3>Importar lançamentos Questor</h3>
                <p class="muted">Formato esperado CSV: data_lancamento,documento,descricao,valor,tipo,conta</p>
                <form action="/public/import_questor.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="arquivo" accept=".csv" required>
                    <button type="submit">Importar Questor</button>
                </form>
            </div>
            <div>
                <h3>Importar extrato bancário</h3>
                <p class="muted">Formato esperado CSV: data_movimento,historico,valor,conta,doc_ref</p>
                <form action="/public/import_bank.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="arquivo" accept=".csv" required>
                    <button type="submit">Importar Extrato</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Conciliação manual</h2>
        <form action="/public/conciliar.php" method="post">
            <label>Lançamento Questor:
                <select name="lancamento_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($lancamentos as $l): ?>
                        <option value="<?= (int)$l['id'] ?>">
                            #<?= (int)$l['id'] ?> | <?= htmlspecialchars($l['data_lancamento']) ?> | <?= htmlspecialchars($l['descricao']) ?> | R$ <?= number_format((float)$l['valor'], 2, ',', '.') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Extrato:
                <select name="extrato_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($extratos as $e): ?>
                        <option value="<?= (int)$e['id'] ?>">
                            #<?= (int)$e['id'] ?> | <?= htmlspecialchars($e['data_movimento']) ?> | <?= htmlspecialchars($e['historico']) ?> | R$ <?= number_format((float)$e['valor'], 2, ',', '.') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Status:
                <select name="status" required>
                    <option value="CONCILIADO">CONCILIADO</option>
                    <option value="DIVERGENTE">DIVERGENTE</option>
                </select>
            </label>

            <button type="submit">Salvar Conciliação</button>
        </form>
    </div>

    <div class="card">
        <h2>Exportar retorno para Questor</h2>
        <p class="muted">Exporta somente conciliações com status CONCILIADO.</p>
        <form action="/public/export_questor.php" method="get">
            <button type="submit">Baixar CSV</button>
        </form>
    </div>
</div>
</body>
</html>
