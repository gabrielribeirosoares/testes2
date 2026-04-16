require('dotenv').config();

const express = require('express');
const multer = require('multer');
const { parse } = require('csv-parse/sync');
const { pool } = require('./db');

const app = express();
const upload = multer({ storage: multer.memoryStorage() });

app.set('view engine', 'ejs');
app.set('views', __dirname + '/views');
app.use('/assets', express.static(__dirname + '/public'));
app.use(express.urlencoded({ extended: true }));

function toNumber(value) {
  return Number(String(value || '').replace(',', '.'));
}

app.get('/', async (req, res) => {
  const [[{ pendentes }]] = await pool.query(
    "SELECT COUNT(*) AS pendentes FROM lancamentos_empresa WHERE status_conciliacao = 'PENDENTE'"
  );
  const [[{ conciliados }]] = await pool.query('SELECT COUNT(*) AS conciliados FROM conciliacoes');
  const [[{ extratoSemMatch }]] = await pool.query(
    'SELECT COUNT(*) AS extratoSemMatch FROM extrato_bancario e LEFT JOIN conciliacoes c ON c.extrato_id = e.id WHERE c.id IS NULL'
  );

  const [lancamentos] = await pool.query(
    "SELECT * FROM lancamentos_empresa WHERE status_conciliacao = 'PENDENTE' ORDER BY data_lancamento ASC LIMIT 100"
  );
  const [extratos] = await pool.query(
    'SELECT e.* FROM extrato_bancario e LEFT JOIN conciliacoes c ON c.extrato_id = e.id WHERE c.id IS NULL ORDER BY e.data_movimento ASC LIMIT 100'
  );

  res.render('index', {
    pendentes,
    conciliados,
    extratoSemMatch,
    lancamentos,
    extratos,
    message: req.query.message || null,
    type: req.query.type || 'success'
  });
});

app.post('/import/questor', upload.single('arquivo'), async (req, res) => {
  if (!req.file) {
    return res.redirect('/?type=error&message=Arquivo+Questor+invalido');
  }

  const records = parse(req.file.buffer.toString('utf-8'), {
    columns: true,
    skip_empty_lines: true,
    trim: true
  });

  let total = 0;
  for (const row of records) {
    await pool.query(
      `INSERT INTO lancamentos_empresa
      (data_lancamento, documento, descricao, valor, tipo, conta, status_conciliacao)
      VALUES (?, ?, ?, ?, ?, ?, 'PENDENTE')`,
      [row.data_lancamento, row.documento, row.descricao, toNumber(row.valor), String(row.tipo || '').toUpperCase(), row.conta]
    );
    total += 1;
  }

  return res.redirect(`/?message=Importacao+Questor+concluida:+${total}+lancamentos`);
});

app.post('/import/extrato', upload.single('arquivo'), async (req, res) => {
  if (!req.file) {
    return res.redirect('/?type=error&message=Arquivo+de+extrato+invalido');
  }

  const records = parse(req.file.buffer.toString('utf-8'), {
    columns: true,
    skip_empty_lines: true,
    trim: true
  });

  let total = 0;
  for (const row of records) {
    await pool.query(
      `INSERT INTO extrato_bancario
      (data_movimento, historico, valor, conta, doc_ref)
      VALUES (?, ?, ?, ?, ?)`,
      [row.data_movimento, row.historico, toNumber(row.valor), row.conta, row.doc_ref || null]
    );
    total += 1;
  }

  return res.redirect(`/?message=Importacao+de+extrato+concluida:+${total}+movimentos`);
});

app.post('/conciliar', async (req, res) => {
  const lancamentoId = Number(req.body.lancamento_id || 0);
  const extratoId = Number(req.body.extrato_id || 0);
  const status = ['CONCILIADO', 'DIVERGENTE'].includes(req.body.status)
    ? req.body.status
    : 'DIVERGENTE';

  if (!lancamentoId || !extratoId) {
    return res.redirect('/?type=error&message=Selecione+lancamento+e+extrato+validos');
  }

  const conn = await pool.getConnection();
  try {
    await conn.beginTransaction();
    const [existing] = await conn.query(
      'SELECT id FROM conciliacoes WHERE lancamento_id = ? OR extrato_id = ? LIMIT 1',
      [lancamentoId, extratoId]
    );

    if (existing.length > 0) {
      await conn.rollback();
      return res.redirect('/?type=error&message=Item+ja+conciliado');
    }

    await conn.query(
      'INSERT INTO conciliacoes (lancamento_id, extrato_id, status, data_conciliacao) VALUES (?, ?, ?, NOW())',
      [lancamentoId, extratoId, status]
    );
    await conn.query('UPDATE lancamentos_empresa SET status_conciliacao = ? WHERE id = ?', [
      status,
      lancamentoId
    ]);
    await conn.commit();

    return res.redirect('/?message=Conciliacao+registrada+com+sucesso');
  } catch (error) {
    await conn.rollback();
    return res.redirect('/?type=error&message=Erro+ao+conciliar');
  } finally {
    conn.release();
  }
});

app.get('/export/questor', async (_req, res) => {
  const [rows] = await pool.query(`
    SELECT c.id AS conciliacao_id, c.data_conciliacao, l.data_lancamento, l.documento, l.descricao, l.valor, l.tipo, l.conta
    FROM conciliacoes c
    INNER JOIN lancamentos_empresa l ON l.id = c.lancamento_id
    WHERE c.status = 'CONCILIADO'
    ORDER BY c.data_conciliacao DESC
  `);

  const headers = [
    'conciliacao_id',
    'data_conciliacao',
    'data_lancamento',
    'documento',
    'descricao',
    'valor',
    'tipo',
    'conta'
  ];

  const content = [
    headers.join(','),
    ...rows.map((row) => headers.map((key) => row[key]).join(','))
  ].join('\n');

  res.setHeader('Content-Type', 'text/csv; charset=utf-8');
  res.setHeader('Content-Disposition', `attachment; filename=retorno_questor_${Date.now()}.csv`);
  res.send(content);
});

app.use((error, _req, res, _next) => {
  console.error(error);
  res.status(500).send('Erro interno.');
});

const port = Number(process.env.PORT || 3000);
app.listen(port, () => {
  console.log(`Servidor iniciado em http://localhost:${port}`);
});
