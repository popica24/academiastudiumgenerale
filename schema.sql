-- Cele două tabele ale blogului. Se aplică o singură dată, de admin/setup.php
-- pe server și de mână local. Colația e utf8mb4_unicode_ci fiindcă titlurile
-- și textele sunt în română: cu latin1, „învățare" se strică la scriere.

CREATE TABLE IF NOT EXISTS administratori (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(190)  NOT NULL UNIQUE,
  parola_hash   VARCHAR(255)  NOT NULL,
  esecuri       INT           NOT NULL DEFAULT 0,
  blocat_pana   DATETIME      NULL,
  creat_la      DATETIME      NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS articole (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  slug            VARCHAR(190)  NOT NULL UNIQUE,
  titlu           VARCHAR(255)  NOT NULL,
  eticheta        VARCHAR(60)   NOT NULL,
  rezumat         VARCHAR(400)  NOT NULL,
  text            MEDIUMTEXT    NOT NULL,
  data_publicare  DATE          NOT NULL,
  stare           ENUM('ciorna','publicat') NOT NULL DEFAULT 'ciorna',
  creat_la        DATETIME      NOT NULL,
  actualizat_la   DATETIME      NOT NULL,
  INDEX lista (stare, data_publicare)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
