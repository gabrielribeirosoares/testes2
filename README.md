# SaaS de Conciliação Bancária (Questor)

Aplicação PHP simples para conciliação bancária com foco em importação/exportação de CSV para integração com Questor Sistemas.

## Funcionalidades

- Importação de lançamentos do ERP Questor via CSV.
- Importação de extrato bancário via CSV.
- Conciliação manual entre lançamento e movimento bancário.
- Exportação de retorno conciliado para Questor (CSV).
- Indicadores de pendência/conciliação no dashboard.

## Estrutura CSV

### Questor (importação)
Cabeçalho esperado:

`data_lancamento,documento,descricao,valor,tipo,conta`

### Extrato bancário (importação)
Cabeçalho esperado:

`data_movimento,historico,valor,conta,doc_ref`

### Retorno Questor (exportação)
Colunas geradas:

`conciliacao_id,data_conciliacao,data_lancamento,documento,descricao,valor,tipo,conta`

## Banco de dados

Rode o script `teste.sql` para criar as tabelas:

- `lancamentos_empresa`
- `extrato_bancario`
- `conciliacoes`

## Execução local

1. Ajuste credenciais em `config.php`.
2. Importe `teste.sql` no MySQL/MariaDB.
3. Sirva a pasta em um servidor com PHP 8+.
4. Acesse `index.php`.
