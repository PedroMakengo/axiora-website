<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Artigo;
use App\Models\CategoriaBlog;

class BlogController extends Controller
{
    private const POR_PAGINA = 9;

    public function index(): void
    {
        $this->listar(null);
    }

    public function categoria(string $slug): void
    {
        $categoria = (new CategoriaBlog())->porSlugAtiva($slug);
        if (!$categoria) {
            $this->naoEncontrado();
        }
        $this->listar($categoria);
    }

    private function listar(?array $categoria): void
    {
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $pesquisa = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 80);

        $modelo = new Artigo();
        $resultado = $modelo->publicados($pagina, self::POR_PAGINA, $categoria ? (int) $categoria['id'] : null, $pesquisa);
        $totalPaginas = max(1, (int) ceil($resultado['total'] / self::POR_PAGINA));

        if ($pagina > $totalPaginas && $resultado['total'] > 0) {
            $this->naoEncontrado();
        }

        $caminhoBase = $categoria ? '/blog/categoria/' . $categoria['slug'] : '/blog';
        $titulo = $categoria ? $categoria['nome'] . ' | Blog ' . NOME_CURTO : 'Blog e Notícias | ' . NOME_CURTO;
        if ($pagina > 1) {
            $titulo = 'Página ' . $pagina . ' — ' . $titulo;
        }

        $this->view('blog/index', [
            'artigos'      => $resultado['itens'],
            'total'        => $resultado['total'],
            'pagina'       => $pagina,
            'totalPaginas' => $totalPaginas,
            'pesquisa'     => $pesquisa,
            'categoria'    => $categoria,
            'caminhoBase'  => $caminhoBase,
            'categorias'   => (new CategoriaBlog())->ativasComContagem(),
            'ultimos'      => $modelo->ultimos(4),
            'paginaAtual'  => 'blog',
        ], [
            'titulo'    => $titulo,
            'descricao' => $categoria['descricao'] ?? 'Notícias, comunicados e dicas práticas da Axiora sobre vistos, passaportes, gestão de viaturas, câmbio e serviços em Luanda.',
            'canonical' => URL_BASE . $caminhoBase . ($pagina > 1 ? '?pagina=' . $pagina : ''),
            // Resultados de pesquisa não devem ser indexados (conteúdo duplicado).
            'robots'    => $pesquisa !== '' ? 'noindex, follow' : 'index, follow',
        ]);
    }

    public function mostrar(string $slug): void
    {
        $modelo = new Artigo();
        $artigo = $modelo->porSlugPublicado($slug);
        if (!$artigo) {
            $this->naoEncontrado();
        }

        // Conta uma visualização por artigo e por sessão (recarregar a página não infla o número).
        $vistos = $_SESSION['artigos_vistos'] ?? [];
        if (!in_array((int) $artigo['id'], $vistos, true)) {
            $modelo->incrementarVisualizacoes((int) $artigo['id']);
            $vistos[] = (int) $artigo['id'];
            $_SESSION['artigos_vistos'] = array_slice($vistos, -50);
        }

        $url = URL_BASE . '/blog/' . $artigo['slug'];
        $imagem = $artigo['imagem'] ? URL_BASE . '/' . $artigo['imagem'] : URL_BASE . '/assets/images/hero/luanda.webp';
        $descricao = $artigo['meta_descricao'] ?: ($artigo['resumo'] ?: mb_substr(\Core\Html::texto($artigo['conteudo']), 0, 160));

        $jsonld = [
            '@context'         => 'https://schema.org',
            '@type'            => 'BlogPosting',
            'headline'         => $artigo['titulo'],
            'description'      => $descricao,
            'image'            => [$imagem],
            'datePublished'    => date('c', strtotime($artigo['publicado_em'])),
            'dateModified'     => date('c', strtotime($artigo['atualizado_em'] ?: $artigo['publicado_em'])),
            'mainEntityOfPage' => $url,
            'author'           => ['@type' => 'Organization', 'name' => NOME_CURTO, 'url' => URL_BASE . '/'],
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => NOME_EMPRESA,
                'logo'  => ['@type' => 'ImageObject', 'url' => URL_BASE . '/assets/images/logo.png'],
            ],
        ];

        $this->view('blog/mostrar', [
            'artigo'       => $artigo,
            'relacionados' => $modelo->relacionados($artigo, 3),
            'urlPartilha'  => $url,
            'paginaAtual'  => 'blog',
        ], [
            'titulo'    => $artigo['titulo'] . ' | Blog ' . NOME_CURTO,
            'descricao' => $descricao,
            'canonical' => $url,
            'imagem'    => $imagem,
            'tipo'      => 'article',
            'jsonld'    => $jsonld,
            'publicado' => date('c', strtotime($artigo['publicado_em'])),
        ]);
    }

    private function naoEncontrado(): void
    {
        http_response_code(404);
        require CAMINHO_RAIZ . '/app/Views/paginas/404.php';
        exit;
    }
}
