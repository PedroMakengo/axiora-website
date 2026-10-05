# Actualizações da base de dados

Cada alteração à estrutura depois da primeira instalação vai num ficheiro novo
nesta pasta, com o nome `AAAA-MM-DD-descricao.sql` (ex.: `2026-11-02-artigos-tags.sql`).

- O `database/instalar.php` aplica os ficheiros por ordem alfabética, **uma única vez**,
  e regista-os na tabela `migracoes`.
- No Docker/Coolify isto acontece sozinho a cada deploy (arranque do contentor).
- Em XAMPP/cPanel: `php database/instalar.php`.
- Actualize também o `database/schema.sql`, para que uma instalação nova já venha
  com a estrutura final (numa instalação nova, os ficheiros desta pasta são marcados
  como aplicados sem serem corridos).
- Cada instrução tem de terminar com `;` no fim da linha.
