-- =====================================================================
-- 2026-10-08 — CMS completo da homepage + editor de texto
--
-- * secoes_site / secao_itens: textos, imagens e listas de todas as
--   secções do site (definidas em config/secoes.php). O conteúdo inicial
--   é gravado pelo instalador a partir dessa definição.
-- * Campos que passam a usar o editor de texto (guardam HTML) deixam de
--   caber em VARCHAR: passam a TEXT.
-- =====================================================================

CREATE TABLE IF NOT EXISTS `secoes_site` (
  `chave` varchar(60) NOT NULL,
  `conteudo` longtext NOT NULL COMMENT 'JSON com os campos da secção',
  `visivel` tinyint(1) NOT NULL DEFAULT 1,
  `atualizado_em` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `secao_itens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `secao` varchar(60) NOT NULL,
  `grupo` varchar(40) NOT NULL,
  `dados` longtext NOT NULL COMMENT 'JSON com os campos do item',
  `ordem` int NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `secao_grupo` (`secao`, `grupo`, `ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `hero_slides` MODIFY `texto` text DEFAULT NULL;
ALTER TABLE `servicos` MODIFY `descricao` text NOT NULL;
ALTER TABLE `testemunhos` MODIFY `texto` text NOT NULL;
