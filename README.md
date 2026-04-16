# SaaS de Conciliação Bancária (Questor) - Node.js

Versão moderna do sistema em **Node.js + Express + EJS + MySQL**, substituindo a implementação em PHP.

## Funcionalidades

- Importação de lançamentos Questor em CSV.
- Importação de extrato bancário em CSV.
- Conciliação manual com status `CONCILIADO` e `DIVERGENTE`.
- Exportação de retorno conciliado para Questor em CSV.
- Dashboard com indicadores operacionais.

## Requisitos

- Node.js 20+
- MySQL/MariaDB

## Configuração

1. Copie `.env.example` para `.env` e ajuste credenciais.
2. Execute o script SQL:
   ```sql
   SOURCE teste.sql;
   ```
3. Instale dependências:
   ```bash
   npm install
   ```
4. Inicie o app:
   ```bash
   npm run dev
   ```
5. Abra `http://localhost:3000`.

## Estruturas CSV

### Questor (importação)
`data_lancamento,documento,descricao,valor,tipo,conta`

### Extrato (importação)
`data_movimento,historico,valor,conta,doc_ref`

### Retorno Questor (exportação)
`conciliacao_id,data_conciliacao,data_lancamento,documento,descricao,valor,tipo,conta`
