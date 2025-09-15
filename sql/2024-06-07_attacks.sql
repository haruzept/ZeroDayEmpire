-- Migration for attacks tables and seed data

-- Stammdaten aller Angriffsaktionen
CREATE TABLE IF NOT EXISTS attack_actions (
  code            VARCHAR(32)  PRIMARY KEY,
  name            VARCHAR(120) NOT NULL,
  descr           VARCHAR(512) NOT NULL,
  base_cost       INT NOT NULL,
  base_payout     INT NOT NULL,
  base_time_min   INT NOT NULL,
  base_risk_pct   TINYINT NOT NULL,
  cost_mult       DECIMAL(6,3) NOT NULL DEFAULT 1.600,
  payout_mult     DECIMAL(6,3) NOT NULL DEFAULT 1.700,
  time_mult       DECIMAL(6,3) NOT NULL DEFAULT 1.250,
  max_level       TINYINT NOT NULL DEFAULT 5,
  tick_minutes    INT NOT NULL DEFAULT 0,
  cooldown_min    INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Spieler-/PC-spezifischer Fortschritt je Aktion
CREATE TABLE IF NOT EXISTS attack_state (
  pc              INT NOT NULL,
  code            VARCHAR(32) NOT NULL,
  level           TINYINT NOT NULL DEFAULT 1,
  xp              INT NOT NULL DEFAULT 0,
  PRIMARY KEY(pc, code),
  CONSTRAINT fk_attack_state_action FOREIGN KEY (code) REFERENCES attack_actions(code)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lauf-Log einzelner Ausführungen
CREATE TABLE IF NOT EXISTS attack_runs (
  id              BIGINT AUTO_INCREMENT PRIMARY KEY,
  pc              INT NOT NULL,
  code            VARCHAR(32) NOT NULL,
  level_snapshot  TINYINT NOT NULL,
  cost            INT NOT NULL,
  payout_expected INT NOT NULL,
  payout_final    INT DEFAULT NULL,
  risk_roll       DECIMAL(6,3) DEFAULT NULL,
  status          ENUM('running','success','fail','cancelled') NOT NULL,
  started_at      DATETIME NOT NULL,
  ends_at         DATETIME NOT NULL,
  finished_at     DATETIME DEFAULT NULL,
  cooldown_until  DATETIME DEFAULT NULL,
  notes           VARCHAR(512) DEFAULT NULL,
  INDEX ix_pc_status (pc, status),
  INDEX ix_pc_code (pc, code),
  CONSTRAINT fk_attack_runs_action FOREIGN KEY (code) REFERENCES attack_actions(code)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Abhängigkeiten: Unlocks und Level-Gates
CREATE TABLE IF NOT EXISTS attack_deps (
  code        VARCHAR(32) NOT NULL,
  dep_type    ENUM('unlock','level_gate') NOT NULL,
  gate_level  TINYINT NOT NULL DEFAULT 1,
  req_kind    ENUM('research','hardware') NOT NULL,
  req_key     VARCHAR(32) NOT NULL,
  req_level   INT NOT NULL DEFAULT 1,
  PRIMARY KEY (code, dep_type, gate_level, req_kind, req_key),
  CONSTRAINT fk_attack_deps_action FOREIGN KEY (code) REFERENCES attack_actions(code)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed data for attack_actions
REPLACE INTO attack_actions
(code, name, descr, base_cost, base_payout, base_time_min, base_risk_pct, cost_mult, payout_mult, time_mult, max_level, tick_minutes, cooldown_min)
VALUES
('a_phish',   'Phishing-Kampagne (E-Mail)', 'Einstieg in Social Engineering: Massenmail mit steigender Conversion.', 10,  50,  3, 12, 1.60, 1.70, 1.25, 5, 0, 0),
('a_combo',   'Credential-Stuffing',        'Automatisiertes Testen geleakter Logins, Trefferquote steigt mit Level.', 15,  45,  4, 14, 1.60, 1.70, 1.25, 5, 0, 0),
('a_adfraud', 'Ad-Fraud/Traffic',           'Traffic monetarisieren, skaliert über MoneyMarket.',                    20,  60,  5, 10, 1.60, 1.70, 1.25, 5, 0, 0),
('a_lpage',   'Phish-Kit & Landing-Page',   'Glaubwürdige Fake-Sites erhöhen Konversion.',                           30, 120,  8, 15, 1.60, 1.70, 1.25, 5, 0, 0),
('a_keylog',  'Keylogger-Dropper',          'Persistente Kleingeld-Abflüsse; liefert Tick-Ertrag.',                  40, 150, 12, 18, 1.60, 1.70, 1.25, 5, 30, 0),
('a_miner',   'Cryptojacking-Deployment',   'CPU-gebundene Mining-Erträge über Zeit.',                               60, 220, 15, 20, 1.60, 1.70, 1.25, 5, 60, 0),
('a_data',    'Datenhehlerei',              'Abfluss und Verkauf von Datensätzen mit hoher Varianz.',                80, 260, 18, 22, 1.60, 1.70, 1.25, 5, 0, 0),
('a_botrent', 'Botnet-Vermietung',          'Leistung vermieten; stark skalierend mit Netzwerk.',                    120,420, 25, 25, 1.60, 1.70, 1.25, 5, 0, 0),
('a_rans',    'Ransomware-Kampagne',        'Hoher Ertrag, hohes Risiko, anschließender Cooldown.',                 200,900, 35, 28, 1.60, 1.70, 1.25, 5, 0, 30),
('a_0day',    '0-Day-Entwicklung & Broker', 'Lange Laufzeit, mittleres Risiko, einmalige große Auszahlung.',        250,1000, 45, 18, 1.60, 1.70, 1.25, 5, 0, 0);

-- Dependencies
-- a_phish
REPLACE INTO attack_deps VALUES
('a_phish','unlock',1,'research','r_se',1),
('a_phish','unlock',1,'hardware','lan',1),
('a_phish','level_gate',3,'research','r_se',2),
('a_phish','level_gate',4,'research','r_veil',2),
('a_phish','level_gate',5,'research','r_c2',2);

-- a_combo
REPLACE INTO attack_deps VALUES
('a_combo','unlock',1,'research','r_ana',1),
('a_combo','unlock',1,'hardware','sdk',1),
('a_combo','unlock',1,'hardware','lan',2),
('a_combo','level_gate',3,'research','r_lab',2),
('a_combo','level_gate',4,'research','r_bauk',3),
('a_combo','level_gate',5,'research','r_veil',3);

-- a_adfraud
REPLACE INTO attack_deps VALUES
('a_adfraud','unlock',1,'hardware','mm',1),
('a_adfraud','unlock',1,'hardware','cpu',1),
('a_adfraud','level_gate',3,'hardware','mm',3),
('a_adfraud','level_gate',4,'hardware','mm',5),
('a_adfraud','level_gate',5,'hardware','mm',8),
('a_adfraud','level_gate',4,'research','r_veil',2);

-- a_lpage
REPLACE INTO attack_deps VALUES
('a_lpage','unlock',1,'hardware','mk',2),
('a_lpage','unlock',1,'research','r_bauk',2),
('a_lpage','unlock',1,'research','r_se',2),
('a_lpage','level_gate',4,'research','r_veil',3),
('a_lpage','level_gate',5,'research','r_c2',3);

-- a_keylog
REPLACE INTO attack_deps VALUES
('a_keylog','unlock',1,'hardware','mk',4),
('a_keylog','unlock',1,'hardware','trojan',2),
('a_keylog','unlock',1,'research','r_pers',2),
('a_keylog','level_gate',3,'research','r_veil',2),
('a_keylog','level_gate',4,'research','r_data',2),
('a_keylog','level_gate',5,'research','r_pers',3);

-- a_miner
REPLACE INTO attack_deps VALUES
('a_miner','unlock',1,'hardware','mk',5),
('a_miner','unlock',1,'hardware','trojan',3),
('a_miner','unlock',1,'research','r_pers',3),
('a_miner','level_gate',4,'research','r_c2',3),
('a_miner','level_gate',5,'research','r_veil',4);

-- a_data
REPLACE INTO attack_deps VALUES
('a_data','unlock',1,'research','r_data',3),
('a_data','unlock',1,'hardware','mk',3),
('a_data','level_gate',4,'research','r_lab',3),
('a_data','level_gate',5,'research','r_veil',4);

-- a_botrent
REPLACE INTO attack_deps VALUES
('a_botrent','unlock',1,'research','r_c2',3),
('a_botrent','unlock',1,'hardware','lan',4),
('a_botrent','level_gate',4,'research','r_pers',3),
('a_botrent','level_gate',5,'research','r_veil',4);

-- a_rans
REPLACE INTO attack_deps VALUES
('a_rans','unlock',1,'research','r_rans',3),
('a_rans','unlock',1,'hardware','trojan',4),
('a_rans','unlock',1,'hardware','mk',6),
('a_rans','level_gate',4,'research','r_pers',4),
('a_rans','level_gate',5,'research','r_c2',4),
('a_rans','level_gate',5,'research','r_data',4),
('a_rans','level_gate',5,'research','r_veil',4);

-- a_0day
REPLACE INTO attack_deps VALUES
('a_0day','unlock',1,'research','r_lab',4),
('a_0day','unlock',1,'research','r_bauk',4),
('a_0day','level_gate',5,'research','r_veil',4),
('a_0day','level_gate',5,'research','r_c2',4);

