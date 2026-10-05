<?php

namespace App\Controllers;

use Core\Auth;
use Core\Controller;
use Core\Html;
use Core\Slug;
use Core\Upload;
use Core\Validador;
use App\Models\Artigo;
use App\Models\CategoriaBlog;
use App\Models\Utilizador;

// =====================================================================
// Painel › Blog: artigos (notícias, dicas, comunicados) e categorias.
// Módulo de permissões: "blog".
// =====================================================================

class AdminBlogController extends Controller
{
    // ==================== Artigos ====================

    public function artigos(): void
    {
        $this->exigirPermissao('blog', 'ver');

        $this->view('admin/blog-artigos', [
            'artigos'     => (new Artigo())->todosAdmin(),
            'paginaAtual' => 'blog-artigos',
        ], [
            'titulo' => 'Artigos | Painel Administrativo',
        ], 'admin');
    }

    public function novo(): void
    {
        $this->exigirPermissao('blog', 'criar');
        $this->formulario(null);
    }

    public function editar(int $id): void
    {
        $this->exigirPermissao('blog', 'editar');
        $artigo = (new Artigo())->porId($id);
        if (!$artigo) {
            http_response_code(404);
            require CAMINHO_RAIZ . '/app/Views/paginas/404.php';
            exit;
        }
        $this->formulario($artigo);
    }

    private function formulario(?array $artigo): void
    {
        $this->view('admin/blog-artigo-form', [
            'artigo'      => $artigo,
            'categorias'  => (new CategoriaBlog())->todas(),
            'paginaAtual' => 'blog-artigos',
        ], [
            'titulo' => ($artigo ? 'Editar artigo' : 'Novo artigo') . ' | Painel Administrativo',
        ], 'admin');
    }

    /** Lê e valida o formulário do artigo. Devolve [dados, erros]. */
    private function dadosArtigo(Artigo $modelo, ?int $id): array
    {
        $titulo = Validador::texto($_POST['titulo'] ?? '', 180);
        $slug = Slug::gerar(Validador::texto($_POST['slug'] ?? '', 150) ?: $titulo);
        $slug = mb_substr($slug, 0, 140);
        $conteudo = Html::limpar((string) ($_POST['conteudo'] ?? ''));
        $estado = ($_POST['estado'] ?? '') === 'publicado' ? 'publicado' : 'rascunho';
        $publicadoEm = trim((string) ($_POST['publicado_em'] ?? ''));
        $categoriaId = (int) ($_POST['categoria_id'] ?? 0);

        $erros = [];
        if (mb_strlen($titulo) < 5) $erros['titulo'] = 'Indique um título com pelo menos 5 caracteres.';
        if ($slug === '') $erros['slug'] = 'Indique um endereço válido (letras, números e hífenes).';
        if (Html::texto($conteudo) === '' && !str_contains($conteudo, '<img')) $erros['conteudo'] = 'Escreva o conteúdo do artigo.';
        if ($categoriaId && !(new CategoriaBlog())->porId($categoriaId)) $erros['categoria_id'] = 'Categoria inválida.';

        if ($publicadoEm !== '') {
            if (!Validador::dataHora($publicadoEm)) {
                $erros['publicado_em'] = 'Data de publicação inválida.';
            } else {
                $publicadoEm = date('Y-m-d H:i:s', strtotime($publicadoEm));
            }
        } elseif ($estado === 'publicado') {
            $publicadoEm = date('Y-m-d H:i:s'); // publicar agora
        }

        $resumo = Validador::texto($_POST['resumo'] ?? '', 320);
        if ($resumo === '') {
            $textoSimples = Html::texto($conteudo);
            $resumo = mb_strlen($textoSimples) > 220 ? rtrim(mb_substr($textoSimples, 0, 217)) . '...' : $textoSimples;
        }

        $dados = [
            'titulo'         => $titulo,
            'slug'           => $slug === '' ? '' : $modelo->slugUnico($slug, $id),
            'categoria_id'   => $categoriaId ?: null,
            'resumo'         => $resumo,
            'conteudo'       => $conteudo,
            'meta_descricao' => Validador::texto($_POST['meta_descricao'] ?? '', 170),
            'estado'         => $estado,
            'destaque'       => !empty($_POST['destaque']),
            'publicado_em'   => $publicadoEm ?: null,
        ];

        return [$dados, $erros];
    }

    public function criar(): void
    {
        $this->exigirPermissao('blog', 'criar');
        $this->exigirCsrf();

        $modelo = new Artigo();
        [$dados, $erros] = $this->dadosArtigo($modelo, null);

        try {
            $dados['imagem'] = Upload::imagem($_FILES['imagem'] ?? null, 'blog');
        } catch (\RuntimeException $e) {
            $erros['imagem'] = $e->getMessage();
        }
        if ($erros) {
            Upload::remover($dados['imagem'] ?? null);
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        $dados['autor_id'] = Auth::id();
        $id = $modelo->criar($dados);
        (new Utilizador())->registarLog(Auth::id(), 'artigo_criado', "Artigo #{$id}: {$dados['titulo']}");

        $this->json([
            'sucesso'      => true,
            'mensagem'     => $dados['estado'] === 'publicado' ? 'Artigo publicado.' : 'Rascunho guardado.',
            'redirecionar' => caminho('/admin/blog/' . $id . '/editar'),
        ]);
    }

    public function atualizar(int $id): void
    {
        $this->exigirPermissao('blog', 'editar');
        $this->exigirCsrf();

        $modelo = new Artigo();
        $existente = $modelo->porId($id);
        if (!$existente) {
            $this->json(['sucesso' => false, 'mensagem' => 'Artigo não encontrado.'], 404);
        }

        [$dados, $erros] = $this->dadosArtigo($modelo, $id);

        $nova = null;
        try {
            $nova = Upload::imagem($_FILES['imagem'] ?? null, 'blog');
        } catch (\RuntimeException $e) {
            $erros['imagem'] = $e->getMessage();
        }
        if ($erros) {
            Upload::remover($nova);
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        $removerImagem = !empty($_POST['remover_imagem']);
        $dados['imagem'] = $nova ?? ($removerImagem ? null : $existente['imagem']);
        $modelo->atualizar($id, $dados);

        if ($nova || $removerImagem) {
            Upload::remover($existente['imagem']);
        }
        (new Utilizador())->registarLog(Auth::id(), 'artigo_editado', "Artigo #{$id}: {$dados['titulo']}");

        $this->json([
            'sucesso'  => true,
            'mensagem' => 'Artigo guardado.',
            'dados'    => ['slug' => $dados['slug'], 'resumo' => $dados['resumo'], 'publicado_em' => $dados['publicado_em'] ? date('Y-m-d\TH:i', strtotime($dados['publicado_em'])) : ''],
            'imagem'   => $dados['imagem'] ? BASE . '/' . $dados['imagem'] : null,
        ]);
    }

    public function eliminar(int $id): void
    {
        $this->exigirPermissao('blog', 'eliminar');
        $this->exigirCsrf();

        $modelo = new Artigo();
        $artigo = $modelo->porId($id);
        if (!$artigo) {
            $this->json(['sucesso' => false, 'mensagem' => 'Artigo não encontrado.'], 404);
        }

        $modelo->eliminar($id);
        Upload::remover($artigo['imagem']);
        // As imagens inseridas no corpo do artigo também são removidas.
        if (preg_match_all('#assets/uploads/blog/conteudo/[a-f0-9]+\.(?:jpg|png|webp)#', $artigo['conteudo'], $m)) {
            foreach (array_unique($m[0]) as $imagem) {
                Upload::remover($imagem);
            }
        }
        (new Utilizador())->registarLog(Auth::id(), 'artigo_removido', "Artigo #{$id}: {$artigo['titulo']}");

        $this->json(['sucesso' => true, 'mensagem' => 'Artigo removido.']);
    }

    /** Upload de imagens inseridas no corpo do artigo (botão "imagem" do editor). */
    public function imagemConteudo(): void
    {
        if (!\Core\Permissoes::tem('blog', 'criar') && !\Core\Permissoes::tem('blog', 'editar')) {
            $this->json(['sucesso' => false, 'mensagem' => 'Não tem permissão para esta acção.'], 403);
        }
        $this->exigirCsrf();
        $this->limitar('blog-imagem:' . Auth::id(), 30, 600);

        try {
            $caminho = Upload::imagem($_FILES['imagem'] ?? null, 'blog/conteudo');
        } catch (\RuntimeException $e) {
            $this->json(['sucesso' => false, 'mensagem' => $e->getMessage()], 422);
        }
        if (!$caminho) {
            $this->json(['sucesso' => false, 'mensagem' => 'Seleccione uma imagem.'], 422);
        }

        $this->json(['sucesso' => true, 'url' => BASE . '/' . $caminho]);
    }

    // ==================== Categorias ====================

    public function categorias(): void
    {
        $this->exigirPermissao('blog', 'ver');

        $this->view('admin/blog-categorias', [
            'categorias'  => (new CategoriaBlog())->todas(),
            'paginaAtual' => 'blog-categorias',
        ], [
            'titulo' => 'Categorias do blog | Painel Administrativo',
        ], 'admin');
    }

    private function dadosCategoria(CategoriaBlog $modelo, ?int $id): array
    {
        $nome = Validador::texto($_POST['nome'] ?? '', 80);
        $slug = mb_substr(Slug::gerar(Validador::texto($_POST['slug'] ?? '', 100) ?: $nome), 0, 100);

        $erros = [];
        if (mb_strlen($nome) < 2) $erros['nome'] = 'Indique o nome da categoria.';
        if ($slug === '') $erros['slug'] = 'Indique um endereço válido.';
        elseif ($modelo->slugExiste($slug, $id)) $erros['slug'] = 'Já existe uma categoria com este endereço.';
        if ($erros) {
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        return [
            'nome'      => $nome,
            'slug'      => $slug,
            'descricao' => Validador::texto($_POST['descricao'] ?? '', 255),
            'ordem'     => (int) ($_POST['ordem'] ?? 0),
        ];
    }

    public function categoriaCriar(): void
    {
        $this->exigirPermissao('blog', 'criar');
        $this->exigirCsrf();

        $modelo = new CategoriaBlog();
        $modelo->criar($this->dadosCategoria($modelo, null));
        $this->json(['sucesso' => true, 'mensagem' => 'Categoria criada.']);
    }

    public function categoriaAtualizar(int $id): void
    {
        $this->exigirPermissao('blog', 'editar');
        $this->exigirCsrf();

        $modelo = new CategoriaBlog();
        if (!$modelo->porId($id)) {
            $this->json(['sucesso' => false, 'mensagem' => 'Categoria não encontrada.'], 404);
        }
        $modelo->atualizar($id, $this->dadosCategoria($modelo, $id));
        $this->json(['sucesso' => true, 'mensagem' => 'Categoria actualizada.']);
    }

    public function categoriaEstado(int $id): void
    {
        $this->exigirPermissao('blog', 'editar');
        $this->exigirCsrf();

        (new CategoriaBlog())->atualizarEstado($id, (int) ($_POST['ativo'] ?? 0) === 1 ? 1 : 0);
        $this->json(['sucesso' => true, 'mensagem' => 'Categoria actualizada.']);
    }

    public function categoriaRemover(int $id): void
    {
        $this->exigirPermissao('blog', 'eliminar');
        $this->exigirCsrf();

        (new CategoriaBlog())->remover($id);
        $this->json(['sucesso' => true, 'mensagem' => 'Categoria removida. Os artigos ficaram sem categoria.']);
    }
}
