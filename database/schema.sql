-- =====================================================================
-- schema.sql — Axiora · Site institucional + Blog + Painel
--
-- Estrutura completa e conteúdos iniciais (os mesmos textos e imagens
-- do site estático original). Não cria contas: o administrador é criado
-- pelo instalador (database/instalar.php), a partir de ADMIN_EMAIL /
-- ADMIN_SENHA, para nenhuma senha ficar escrita no repositório.
--
-- Instalação recomendada (cria tabelas + administrador):
--     php database/instalar.php
-- No Docker/Coolify isto corre sozinho no arranque do contentor.
--
-- Também pode ser importado à mão (phpMyAdmin → Importar), numa base de
-- dados já criada e vazia. Usa CREATE TABLE IF NOT EXISTS e INSERT IGNORE,
-- por isso não apaga dados de uma instalação existente.
--
-- Para alterações futuras à estrutura, criar ficheiros novos em
-- database/atualizacoes/ (AAAA-MM-DD-descricao.sql) — o instalador
-- aplica-os uma única vez, por ordem, e regista-os na tabela `migracoes`.
-- =====================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- Utilizadores do painel e permissões
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `utilizadores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `email` varchar(180) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `tipo` enum('administrador','funcionario') NOT NULL DEFAULT 'funcionario',
  `avatar` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissoes` (
  `utilizador_id` int NOT NULL,
  `modulo` varchar(40) NOT NULL,
  `ver` tinyint(1) DEFAULT 0,
  `criar` tinyint(1) DEFAULT 0,
  `editar` tinyint(1) DEFAULT 0,
  `eliminar` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`utilizador_id`,`modulo`),
  CONSTRAINT `permissoes_utilizador_fk` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tokens_recuperacao_senha` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilizador_id` int NOT NULL,
  `token` char(64) NOT NULL COMMENT 'SHA-256 do token enviado por email (nunca o token em claro)',
  `expira_em` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `utilizador_id` (`utilizador_id`),
  CONSTRAINT `tokens_utilizador_fk` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `logs_atividade` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilizador_id` int DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `detalhes` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilizador_id` (`utilizador_id`),
  CONSTRAINT `logs_utilizador_fk` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Definições do site (contactos, horário, redes sociais, mapa)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuracoes_site` (
  `chave` varchar(80) NOT NULL,
  `valor` text,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `configuracoes_site` (`chave`, `valor`) VALUES
('telefone', '+244 938 070 748'),
('whatsapp', '244938070748'),
('mensagem_whatsapp', 'Olá Axiora! Gostaria de ser atendido.'),
('email', 'geral@axiora.site'),
('endereco', 'São Paulo, edifício perto das bombas da Pumangol — Luanda, Angola'),
('endereco_curto', 'São Paulo, Luanda'),
('horario', 'Seg–Sex 08h–18h · Sáb 09h–13h'),
('horario_completo', 'Seg–Sex: 08h00–18h00 · Sábado: 09h00–13h00'),
('mapa_embed', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3942.703184909805!2d13.250733875016333!3d-8.813929991239084!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1a51f3278f06746b%3A0x50e8cdcb66729a37!2sArreiou%20S%C3%A3o%20Paulo!5e0!3m2!1spt-PT!2sao!4v1776281378282!5m2!1spt-PT!2sao'),
('facebook', 'https://facebook.com'),
('instagram', 'https://instagram.com'),
('linkedin', 'https://linkedin.com'),
('tiktok', '');

-- ---------------------------------------------------------------------
-- Conteúdo da homepage (CMS): slider, serviços e testemunhos
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `hero_slides` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rotulo` varchar(40) NOT NULL COMMENT 'Texto curto do separador por baixo do slider',
  `titulo` varchar(160) NOT NULL,
  `titulo_destaque` varchar(160) DEFAULT NULL COMMENT 'Parte final do título, mostrada a cor',
  `texto` varchar(400) DEFAULT NULL,
  `imagem` varchar(255) NOT NULL,
  `botao_texto` varchar(60) NOT NULL DEFAULT 'Falar no WhatsApp',
  `mensagem_whatsapp` varchar(255) DEFAULT NULL,
  `ordem` int NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `hero_slides` (`id`, `rotulo`, `titulo`, `titulo_destaque`, `texto`, `imagem`, `botao_texto`, `mensagem_whatsapp`, `ordem`) VALUES
(1, 'A Axiora', 'Tudo o que precisa resolver,', 'num só lugar de confiança.', 'Vistos, passaportes, gestão de viaturas, serviços técnicos, câmbio e correio. Tratamos do processo por si — com atendimento próximo e preços claros desde o início.', 'assets/images/hero/luanda.webp', 'Falar no WhatsApp', 'Olá Axiora! Gostaria de ser atendido.', 1),
(2, 'Vistos & Passaporte', 'O seu visto preparado', 'sem complicações.', 'Orientamos documentos, formulários e agendamentos junto da VFS Global e dos serviços de passaporte — do primeiro contacto até ao dia da entrega.', 'assets/images/hero/viagens.webp', 'Tratar do meu visto', 'Olá! Preciso de ajuda com o meu visto.', 2),
(3, 'Gestão de Viaturas', 'A sua viatura a render,', 'com gestão profissional.', 'Administramos viaturas ligeiras e pesadas de forma organizada, com acompanhamento regular e relatórios transparentes para o proprietário.', 'assets/images/hero/viaturas.webp', 'Pedir proposta', 'Olá! Quero saber mais sobre gestão de viaturas.', 3),
(4, 'Serviços Técnicos', 'Técnicos qualificados', 'em casa ou na empresa.', 'Instalação, manutenção e reparação de sistemas eléctricos e de canalização, residenciais e comerciais — com orçamento antes de começar.', 'assets/images/hero/tecnicos.webp', 'Pedir um técnico', 'Olá! Preciso de um técnico de canalização ou electricidade.', 4),
(5, 'Câmbio & Correio', 'Câmbio e envios', 'sem filas nem surpresas.', 'Troca de Euro e Dólar com taxas competitivas, e envio seguro de documentos e encomendas para todo o país e para o exterior.', 'assets/images/hero/correio.webp', 'Consultar taxas', 'Olá! Gostaria de informações sobre câmbio e correio.', 5);

CREATE TABLE IF NOT EXISTS `servicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(120) NOT NULL,
  `descricao` varchar(400) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `imagem_alt` varchar(160) DEFAULT NULL,
  `mensagem_whatsapp` varchar(255) DEFAULT NULL,
  `ordem` int NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `servicos` (`id`, `titulo`, `descricao`, `imagem`, `imagem_alt`, `mensagem_whatsapp`, `ordem`) VALUES
(1, 'Assessoria de Vistos VFS', 'Preparação de documentos, preenchimento de formulários e acompanhamento completo do processo de visto junto da VFS Global.', 'assets/images/servicos/vistos.webp', 'Viajante num terminal de aeroporto', 'Olá! Preciso de ajuda com visto VFS.', 1),
(2, 'Gestão de Viaturas', 'Administração profissional de viaturas ligeiras e pesadas, para gerar rendimento de forma organizada e eficiente.', 'assets/images/servicos/viaturas.webp', 'Proprietário ao lado da sua viatura', 'Olá! Quero saber mais sobre gestão de viaturas.', 2),
(3, 'Agendamento de Passaporte', 'Apoio no agendamento e na preparação de documentos para emissão e renovação de passaporte, de forma rápida.', 'assets/images/servicos/passaporte.webp', 'Atendimento personalizado com computador', 'Olá! Preciso de ajuda com agendamento de passaporte.', 3),
(4, 'Canalização & Electricidade', 'Instalação, manutenção e reparação de sistemas eléctricos e de canalização, residenciais e comerciais.', 'assets/images/servicos/tecnicos.webp', 'Técnicas com capacete de segurança', 'Olá! Preciso de serviços de canalização ou electricidade.', 4),
(5, 'Conversão Cambial', 'Troca de moeda estrangeira — Euro (€) e Dólar (USD) — com taxas competitivas e atendimento personalizado.', 'assets/images/servicos/cambio.webp', 'Cliente a segurar uma nota', 'Olá! Quero saber mais sobre conversão cambial.', 5),
(6, 'Correio Nacional & Internacional', 'Envio seguro de documentos e encomendas para as províncias e para o exterior, com confirmação de entrega.', 'assets/images/servicos/correio.webp', 'Entrega de encomendas em motorizada de carga', 'Olá! Quero enviar um documento ou encomenda.', 6);

CREATE TABLE IF NOT EXISTS `testemunhos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) NOT NULL,
  `descricao` varchar(160) DEFAULT NULL COMMENT 'Profissão e serviço usado, ex.: Empresário · Visto VFS',
  `texto` varchar(600) NOT NULL,
  `estrelas` tinyint NOT NULL DEFAULT 5,
  `ordem` int NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `testemunhos` (`id`, `nome`, `descricao`, `texto`, `estrelas`, `ordem`) VALUES
(1, 'Carlos M.', 'Empresário · Visto VFS', 'A Axiora tratou de todo o meu processo de visto com uma eficiência que não esperava. Em menos de uma semana tinha tudo resolvido.', 5, 1),
(2, 'Ana P.', 'Profissional liberal · Gestão de viaturas', 'Entreguei a minha viatura à Axiora para gestão e os resultados superaram as expectativas. Atendimento profissional e relatórios transparentes.', 5, 2),
(3, 'João F.', 'Director comercial · Correio internacional', 'Precisava de enviar documentos urgentes para o exterior e resolveram em tempo recorde. Rápido, seguro e com confirmação de entrega.', 5, 3);

-- ---------------------------------------------------------------------
-- Blog: categorias e artigos (notícias, dicas, comunicados)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `ordem` int NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `blog_categorias` (`id`, `nome`, `slug`, `descricao`, `ordem`) VALUES
(1, 'Notícias', 'noticias', 'Novidades da Axiora e do sector de serviços em Angola.', 1),
(2, 'Dicas', 'dicas', 'Guias práticos sobre vistos, passaportes, viaturas e mais.', 2),
(3, 'Comunicados', 'comunicados', 'Avisos oficiais, horários e informações importantes.', 3);

CREATE TABLE IF NOT EXISTS `blog_artigos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `categoria_id` int DEFAULT NULL,
  `autor_id` int DEFAULT NULL,
  `titulo` varchar(180) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `resumo` varchar(320) DEFAULT NULL,
  `conteudo` mediumtext NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `meta_descricao` varchar(170) DEFAULT NULL,
  `estado` enum('rascunho','publicado') NOT NULL DEFAULT 'rascunho',
  `destaque` tinyint(1) NOT NULL DEFAULT 0,
  `visualizacoes` int unsigned NOT NULL DEFAULT 0,
  `publicado_em` datetime DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `estado_publicado` (`estado`, `publicado_em`),
  KEY `categoria_id` (`categoria_id`),
  KEY `autor_id` (`autor_id`),
  CONSTRAINT `artigos_categoria_fk` FOREIGN KEY (`categoria_id`) REFERENCES `blog_categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `artigos_autor_fk` FOREIGN KEY (`autor_id`) REFERENCES `utilizadores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `blog_artigos` (`id`, `categoria_id`, `titulo`, `slug`, `resumo`, `conteudo`, `imagem`, `estado`, `destaque`, `publicado_em`) VALUES
(1, 1, 'Bem-vindo ao blog da Axiora', 'bem-vindo-ao-blog-da-axiora', 'A partir de agora vai encontrar aqui as nossas novidades, comunicados e dicas práticas sobre vistos, viaturas, câmbio e muito mais.', '<p>A Axiora tem agora um espaço próprio para partilhar novidades com os nossos clientes. Aqui vai encontrar <strong>notícias</strong> da empresa, <strong>comunicados</strong> importantes (como alterações de horário) e <strong>dicas práticas</strong> para tratar dos seus assuntos com menos burocracia.</p><h2>O que vamos publicar</h2><ul><li>Guias passo a passo para vistos VFS e passaportes</li><li>Conselhos para quem quer pôr a viatura a render</li><li>Informações sobre câmbio e envios nacionais e internacionais</li></ul><p>Tem alguma dúvida que gostaria de ver respondida aqui? Fale connosco pelo WhatsApp — teremos todo o gosto em ajudar.</p>', 'assets/images/banner.webp', 'publicado', 1, NOW());
