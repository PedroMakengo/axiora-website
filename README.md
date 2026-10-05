# Axiora — Site institucional + Blog + Painel

Site da **Axiora — Comércio Geral e Prestação de Serviços LDA**, com blog de notícias
e painel administrativo para gerir os conteúdos. Usa a mesma base MVC do projecto
do Centro de Formação: Router central, PDO, CSRF, formulários AJAX, sem Composer e
sem pasta `/public`.

## Estrutura

```
/app
  /Controllers  → Home, Blog, Sitemap, Auth, Admin, AdminConteudo, AdminBlog
  /Models       → Artigo, CategoriaBlog, HeroSlide, Servico, Testemunho, ConfiguracaoSite, Utilizador
  /Views        → layouts/ (site, admin, auth), home/, blog/, admin/, auth/, paginas/ (404, 403)
/config         → configuração do site e ligação à base de dados (lê o .env)
/core           → Router, Controller, Model, Auth, Csrf, Permissoes, RateLimiter, Upload, Html, Mailer...
/assets         → css (main.css = site · admin.css = painel), js, imagens, uploads/
/database       → schema.sql, instalar.php e atualizacoes/
/docker         → configuração Apache/PHP e entrypoint do contentor
/src            → CSS fonte do Tailwind do painel (só para recompilar)
/storage        → rate limit e sessões (privado)
index.php       → front controller (todas as rotas)
.htaccess       → reescrita de URLs + bloqueio das pastas internas
Dockerfile, docker-compose.yml → deploy no Coolify
```

## O que se gere no painel (`/admin`)

| Módulo | O quê |
|---|---|
| **Dashboard** | Artigos publicados/rascunhos, leituras, mais lidos, actividade recente |
| **Blog › Artigos** | Criar, editar, agendar e remover artigos — editor visual (Quill) com imagens, vídeos YouTube/Vimeo, capa, resumo e descrição para o Google |
| **Blog › Categorias** | Notícias, Dicas, Comunicados... (activar/desactivar, ordem) |
| **Conteúdo › Slider** | Slides do topo da homepage (imagem, título, texto, botão e mensagem do WhatsApp) |
| **Conteúdo › Serviços** | Cartões de serviços (e lista do rodapé) |
| **Conteúdo › Testemunhos** | Opiniões de clientes |
| **Utilizadores** | Contas do painel: *Administrador* (tudo) ou *Funcionário* (permissões por módulo: ver/criar/editar/eliminar) |
| **Definições** | Telefone, WhatsApp, email, morada, horário, mapa e redes sociais |

O site público tem `/`, `/blog`, `/blog/categoria/{slug}`, `/blog/{slug}` e `/sitemap.xml`.

Segurança herdada do Centro de Formação: CSRF em todos os POST, sessões endurecidas,
bloqueio de força bruta no login, recuperação de senha com token de uso único (hash na BD),
uploads validados pelo conteúdo e pastas internas bloqueadas. O HTML dos artigos passa
por uma lista branca (`Core\Html::limpar`) antes de ser gravado.

## Instalar em local (XAMPP)

1. O projecto pode ficar na raiz ou numa subpasta (`htdocs/axiora-website`) — a subpasta é detectada sozinha.
2. Criar uma base de dados vazia (ex.: `axiora`, utf8mb4).
3. Copiar `.env.example` para `.env` e preencher `DB_*` (e `ADMIN_EMAIL`/`ADMIN_SENHA`, se quiser escolher a senha).
4. Instalar a BD (tabelas, conteúdos iniciais do site e administrador):
   ```
   C:\xampp\php\php.exe database\instalar.php
   ```
   Sem `ADMIN_SENHA`, a senha do administrador é gerada e mostrada no terminal.
5. Abrir `http://localhost/axiora-website` · painel em `/admin`.

Sem Apache: `servidor.bat` (ou `npm run serve`) arranca em `http://localhost:8000`.

O CSS do painel já vem compilado (`assets/css/admin.css`). Só para alterar estilos do painel:
```
npm install
npm run build:css
```

## Deploy no Coolify

1. No Coolify: **+ New Resource → Public/Private Repository** e escolher este repositório.
2. **Build Pack: Docker Compose**, ficheiro `docker-compose.yml`.
3. Em **Environment Variables**, definir pelo menos:
   - `ADMIN_EMAIL` — email do primeiro administrador
   - `ADMIN_SENHA` — senha inicial (se ficar vazia, é gerada e aparece nos *Logs* do serviço `app`)
   - opcional: `MAIL_HOST`, `MAIL_PORT`, `MAIL_ENCRIPTACAO`, `MAIL_UTILIZADOR`, `MAIL_SENHA`, `MAIL_DE` (recuperação de senha)

   As credenciais da BD (`SERVICE_USER_MYSQL`, `SERVICE_PASSWORD_MYSQL`...) e o domínio
   (`SERVICE_FQDN_APP`) são gerados pelo Coolify.
4. No serviço **app**, definir o domínio (ex.: `https://www.axiora.site`) e fazer **Deploy**.
5. No primeiro arranque o contentor cria as tabelas e o administrador. Em cada deploy seguinte
   aplica só os ficheiros novos de `database/atualizacoes/` — nunca apaga dados.

Persistência (volumes): `axiora-db` (MariaDB), `axiora-uploads` (imagens enviadas no painel)
e `axiora-storage` (sessões e rate limit). Faça backup de `axiora-db` e `axiora-uploads`.

O HTTPS é tratado pelo proxy do Coolify; o contentor confia no `X-Forwarded-For` da rede
interna para registar o IP real (limite de tentativas de login).

### Testar a stack Docker localmente

```
cp .env.docker.example .env.docker
docker compose -f docker-compose.yml -f docker-compose.local.yml --env-file .env.docker up --build
```
Site em `http://localhost:8080`.

## Alterações à base de dados

Ver `database/atualizacoes/LEIA-ME.md`: um ficheiro `.sql` novo por alteração; o instalador
aplica-o uma única vez (local: `php database/instalar.php`; Coolify: automático no deploy).
