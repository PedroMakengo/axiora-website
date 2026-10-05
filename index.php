<?php

// =====================================================================
// index.php — Front controller. O projecto abre directamente na raiz
// do domínio (sem pasta /public), tal como no Centro de Formação.
// =====================================================================

require __DIR__ . '/core/polyfills.php';
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/db.php';
require __DIR__ . '/core/autoload.php';

use Core\Router;
use App\Controllers\HomeController;
use App\Controllers\BlogController;
use App\Controllers\SitemapController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\AdminConteudoController;
use App\Controllers\AdminBlogController;

$router = new Router();

// Site institucional
$router->get('/', [HomeController::class, 'index']);
$router->get('/saude', [HomeController::class, 'saude']);

// Blog / notícias
$router->get('/blog', [BlogController::class, 'index']);
$router->get('/blog/categoria/{slug}', [BlogController::class, 'categoria']);
$router->get('/blog/{slug}', [BlogController::class, 'mostrar']);

// Autenticação do painel
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/recuperar-senha', [AuthController::class, 'recuperarForm']);
$router->post('/recuperar-senha', [AuthController::class, 'recuperar']);
$router->get('/redefinir-senha/{token}', [AuthController::class, 'redefinirForm']);
$router->post('/redefinir-senha', [AuthController::class, 'redefinir']);

// Painel administrativo — dashboard, perfil e definições
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->post('/admin/perfil/atualizar', [AdminController::class, 'perfilAtualizar']);
$router->get('/admin/definicoes', [AdminController::class, 'definicoes']);
$router->post('/admin/definicoes/guardar', [AdminController::class, 'definicoesGuardar']);

// Painel administrativo — utilizadores e permissões
$router->get('/admin/utilizadores', [AdminController::class, 'utilizadores']);
$router->post('/admin/utilizadores/criar', [AdminController::class, 'utilizadorCriar']);
$router->post('/admin/utilizadores/{id}/estado', [AdminController::class, 'utilizadorEstado']);
$router->post('/admin/utilizadores/{id}/atualizar', [AdminController::class, 'utilizadorAtualizar']);
$router->post('/admin/utilizadores/{id}/eliminar', [AdminController::class, 'utilizadorEliminar']);
$router->get('/admin/utilizadores/{id}/permissoes', [AdminController::class, 'permissoesObter']);
$router->post('/admin/utilizadores/{id}/permissoes', [AdminController::class, 'permissoesGuardar']);

// Painel administrativo — blog
$router->get('/admin/blog', [AdminBlogController::class, 'artigos']);
$router->get('/admin/blog/novo', [AdminBlogController::class, 'novo']);
$router->post('/admin/blog/criar', [AdminBlogController::class, 'criar']);
$router->post('/admin/blog/imagem', [AdminBlogController::class, 'imagemConteudo']);
$router->get('/admin/blog/categorias', [AdminBlogController::class, 'categorias']);
$router->post('/admin/blog/categorias/criar', [AdminBlogController::class, 'categoriaCriar']);
$router->post('/admin/blog/categorias/{id}/atualizar', [AdminBlogController::class, 'categoriaAtualizar']);
$router->post('/admin/blog/categorias/{id}/estado', [AdminBlogController::class, 'categoriaEstado']);
$router->post('/admin/blog/categorias/{id}/remover', [AdminBlogController::class, 'categoriaRemover']);
$router->get('/admin/blog/{id}/editar', [AdminBlogController::class, 'editar']);
$router->post('/admin/blog/{id}/atualizar', [AdminBlogController::class, 'atualizar']);
$router->post('/admin/blog/{id}/eliminar', [AdminBlogController::class, 'eliminar']);

// Painel administrativo — conteúdo da homepage (CMS)
$router->get('/admin/conteudo/slider', [AdminConteudoController::class, 'slider']);
$router->post('/admin/conteudo/slider/criar', [AdminConteudoController::class, 'slideCriar']);
$router->post('/admin/conteudo/slider/{id}/atualizar', [AdminConteudoController::class, 'slideAtualizar']);
$router->post('/admin/conteudo/slider/{id}/estado', [AdminConteudoController::class, 'slideEstado']);
$router->post('/admin/conteudo/slider/{id}/remover', [AdminConteudoController::class, 'slideRemover']);
$router->get('/admin/conteudo/servicos', [AdminConteudoController::class, 'servicos']);
$router->post('/admin/conteudo/servicos/criar', [AdminConteudoController::class, 'servicoCriar']);
$router->post('/admin/conteudo/servicos/{id}/atualizar', [AdminConteudoController::class, 'servicoAtualizar']);
$router->post('/admin/conteudo/servicos/{id}/estado', [AdminConteudoController::class, 'servicoEstado']);
$router->post('/admin/conteudo/servicos/{id}/remover', [AdminConteudoController::class, 'servicoRemover']);
$router->get('/admin/conteudo/testemunhos', [AdminConteudoController::class, 'testemunhos']);
$router->post('/admin/conteudo/testemunhos/criar', [AdminConteudoController::class, 'testemunhoCriar']);
$router->post('/admin/conteudo/testemunhos/{id}/atualizar', [AdminConteudoController::class, 'testemunhoAtualizar']);
$router->post('/admin/conteudo/testemunhos/{id}/estado', [AdminConteudoController::class, 'testemunhoEstado']);
$router->post('/admin/conteudo/testemunhos/{id}/remover', [AdminConteudoController::class, 'testemunhoRemover']);

// SEO
$router->get('/sitemap.xml', [SitemapController::class, 'index']);

// Rate limit global: no máximo 60 pedidos POST por minuto por IP (acima disto é
// abuso/automatização). Limites mais apertados por acção ficam nos controladores.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    \Core\RateLimiter::recolherLixo();
    if (!\Core\RateLimiter::permitir('post-global:' . \Core\RateLimiter::ip(), 60, 60)) {
        http_response_code(429);
        header('Retry-After: 60');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['sucesso' => false, 'mensagem' => 'Demasiados pedidos. Aguarde um minuto e tente novamente.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$router->despachar($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
