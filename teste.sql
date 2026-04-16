CREATE DATABASE IF NOT EXISTS teste CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE teste;

CREATE TABLE IF NOT EXISTS lancamentos_empresa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  data_lancamento DATE NOT NULL,
  documento VARCHAR(50) NOT NULL,
  descricao VARCHAR(255) NOT NULL,
  valor DECIMAL(12,2) NOT NULL,
  tipo ENUM('CREDITO','DEBITO') NOT NULL,
  conta VARCHAR(30) NOT NULL,
  status_conciliacao ENUM('PENDENTE','CONCILIADO','DIVERGENTE') NOT NULL DEFAULT 'PENDENTE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS extrato_bancario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  data_movimento DATE NOT NULL,
  historico VARCHAR(255) NOT NULL,
  valor DECIMAL(12,2) NOT NULL,
  conta VARCHAR(30) NOT NULL,
  doc_ref VARCHAR(50) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS conciliacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lancamento_id INT NOT NULL,
  extrato_id INT NOT NULL,
  status ENUM('CONCILIADO','DIVERGENTE') NOT NULL,
  data_conciliacao DATETIME NOT NULL,
  CONSTRAINT fk_conc_lanc FOREIGN KEY (lancamento_id) REFERENCES lancamentos_empresa(id),
  CONSTRAINT fk_conc_ext FOREIGN KEY (extrato_id) REFERENCES extrato_bancario(id),
  CONSTRAINT uk_conc_lanc UNIQUE (lancamento_id),
  CONSTRAINT uk_conc_ext UNIQUE (extrato_id)
);

INSERT INTO lancamentos_empresa (data_lancamento, documento, descricao, valor, tipo, conta)
VALUES
('2026-04-01', 'DOC001', 'Pagamento fornecedor XPTO', 1500.00, 'DEBITO', '341-001'),
('2026-04-02', 'DOC002', 'Recebimento cliente ABC', 2850.50, 'CREDITO', '341-001');

INSERT INTO extrato_bancario (data_movimento, historico, valor, conta, doc_ref)
VALUES
('2026-04-01', 'TED Fornecedor XPTO', 1500.00, '341-001', 'DOC001'),
('2026-04-02', 'PIX Cliente ABC', 2850.50, '341-001', 'DOC002');
