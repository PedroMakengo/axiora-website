<?php

namespace App\Controllers;

use Core\Controller;
use Core\Upload;
use Core\Validador;
use App\Models\HeroSlide;
use App\Models\Servico;
use App\Models\Testemunho;

// =====================================================================
// Painel › Conteúdo do site (CMS da homepage): slider do hero,
// serviços e testemunhos. Módulo de permissões: "conteudo".
// =====================================================================

class AdminConteudoController extends Controller
{
    private function pagina(string $view, string $titulo, array $dados): void
    {
        $this->exigirPermissao('conteudo', 'ver');
        $this->view('admin/' . $view, $dados + ['paginaAtual' => $view], [
            'titulo' => $titulo . ' | Painel Administrativo',
        ], 'admin');
    }

    private function acao(string $acao): void
    {
        $this->exigirPermissao('conteudo', $acao);
        $this->exigirCsrf();
    }

    // ==================== Slider (hero) ====================

    public function slider(): void
    {
        $this->pagina('conteudo-slider', 'Slider', ['slides' => (new HeroSlide())->todos()]);
    }

    private function dadosSlide(): array
    {
        $dados = [
            'rotulo'            => Validador::texto($_POST['rotulo'] ?? '', 40),
            'titulo'            => Validador::texto($_POST['titulo'] ?? '', 160),
            'titulo_destaque'   => Validador::texto($_POST['titulo_destaque'] ?? '', 160),
            'texto'             => Validador::texto($_POST['texto'] ?? '', 400),
            'botao_texto'       => Validador::texto($_POST['botao_texto'] ?? '', 60),
            'mensagem_whatsapp' => Validador::texto($_POST['mensagem_whatsapp'] ?? '', 255),
            'ordem'             => (int) ($_POST['ordem'] ?? 0),
        ];
        $erros = [];
        if ($dados['rotulo'] === '') $erros['rotulo'] = 'Indique o texto do separador.';
        if ($dados['titulo'] === '') $erros['titulo'] = 'Indique o título.';
        return [$dados, $erros];
    }

    public function slideCriar(): void
    {
        $this->acao('criar');
        [$dados, $erros] = $this->dadosSlide();

        try {
            $dados['imagem'] = Upload::imagem($_FILES['imagem'] ?? null, 'slides');
        } catch (\RuntimeException $e) {
            $erros['imagem'] = $e->getMessage();
        }
        if (empty($dados['imagem']) && !isset($erros['imagem'])) {
            $erros['imagem'] = 'Envie uma imagem para o slide.';
        }
        if ($erros) {
            Upload::remover($dados['imagem'] ?? null);
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        (new HeroSlide())->criar($dados);
        $this->json(['sucesso' => true, 'mensagem' => 'Slide criado com sucesso.']);
    }

    public function slideAtualizar(int $id): void
    {
        $this->acao('editar');
        $modelo = new HeroSlide();
        $existente = $modelo->porId($id);
        if (!$existente) {
            $this->json(['sucesso' => false, 'mensagem' => 'Slide não encontrado.'], 404);
        }

        [$dados, $erros] = $this->dadosSlide();
        $nova = null;
        try {
            $nova = Upload::imagem($_FILES['imagem'] ?? null, 'slides');
        } catch (\RuntimeException $e) {
            $erros['imagem'] = $e->getMessage();
        }
        if ($erros) {
            Upload::remover($nova);
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        $dados['imagem'] = $nova ?? $existente['imagem'];
        $modelo->atualizar($id, $dados);
        if ($nova) {
            Upload::remover($existente['imagem']);
        }
        $this->json(['sucesso' => true, 'mensagem' => 'Slide actualizado.']);
    }

    public function slideEstado(int $id): void
    {
        $this->acao('editar');
        (new HeroSlide())->atualizarEstado($id, (int) ($_POST['ativo'] ?? 0) === 1 ? 1 : 0);
        $this->json(['sucesso' => true, 'mensagem' => 'Slide actualizado.']);
    }

    public function slideRemover(int $id): void
    {
        $this->acao('eliminar');
        $modelo = new HeroSlide();
        $existente = $modelo->porId($id);
        $modelo->remover($id);
        Upload::remover($existente['imagem'] ?? null);
        $this->json(['sucesso' => true, 'mensagem' => 'Slide removido.']);
    }

    // ==================== Serviços ====================

    public function servicos(): void
    {
        $this->pagina('conteudo-servicos', 'Serviços', ['servicos' => (new Servico())->todos()]);
    }

    private function dadosServico(): array
    {
        $dados = [
            'titulo'            => Validador::texto($_POST['titulo'] ?? '', 120),
            'descricao'         => Validador::texto($_POST['descricao'] ?? '', 400),
            'imagem_alt'        => Validador::texto($_POST['imagem_alt'] ?? '', 160),
            'mensagem_whatsapp' => Validador::texto($_POST['mensagem_whatsapp'] ?? '', 255),
            'ordem'             => (int) ($_POST['ordem'] ?? 0),
        ];
        $erros = [];
        if ($dados['titulo'] === '') $erros['titulo'] = 'Indique o nome do serviço.';
        if ($dados['descricao'] === '') $erros['descricao'] = 'Indique uma descrição curta.';
        return [$dados, $erros];
    }

    public function servicoCriar(): void
    {
        $this->acao('criar');
        [$dados, $erros] = $this->dadosServico();
        try {
            $dados['imagem'] = Upload::imagem($_FILES['imagem'] ?? null, 'servicos');
        } catch (\RuntimeException $e) {
            $erros['imagem'] = $e->getMessage();
        }
        if ($erros) {
            Upload::remover($dados['imagem'] ?? null);
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        (new Servico())->criar($dados);
        $this->json(['sucesso' => true, 'mensagem' => 'Serviço criado com sucesso.']);
    }

    public function servicoAtualizar(int $id): void
    {
        $this->acao('editar');
        $modelo = new Servico();
        $existente = $modelo->porId($id);
        if (!$existente) {
            $this->json(['sucesso' => false, 'mensagem' => 'Serviço não encontrado.'], 404);
        }

        [$dados, $erros] = $this->dadosServico();
        $nova = null;
        try {
            $nova = Upload::imagem($_FILES['imagem'] ?? null, 'servicos');
        } catch (\RuntimeException $e) {
            $erros['imagem'] = $e->getMessage();
        }
        if ($erros) {
            Upload::remover($nova);
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        $dados['imagem'] = $nova ?? $existente['imagem'];
        $modelo->atualizar($id, $dados);
        if ($nova) {
            Upload::remover($existente['imagem']);
        }
        $this->json(['sucesso' => true, 'mensagem' => 'Serviço actualizado.']);
    }

    public function servicoEstado(int $id): void
    {
        $this->acao('editar');
        (new Servico())->atualizarEstado($id, (int) ($_POST['ativo'] ?? 0) === 1 ? 1 : 0);
        $this->json(['sucesso' => true, 'mensagem' => 'Serviço actualizado.']);
    }

    public function servicoRemover(int $id): void
    {
        $this->acao('eliminar');
        $modelo = new Servico();
        $existente = $modelo->porId($id);
        $modelo->remover($id);
        Upload::remover($existente['imagem'] ?? null);
        $this->json(['sucesso' => true, 'mensagem' => 'Serviço removido.']);
    }

    // ==================== Testemunhos ====================

    public function testemunhos(): void
    {
        $this->pagina('conteudo-testemunhos', 'Testemunhos', ['testemunhos' => (new Testemunho())->todos()]);
    }

    private function dadosTestemunho(): array
    {
        $dados = [
            'nome'      => Validador::texto($_POST['nome'] ?? '', 120),
            'descricao' => Validador::texto($_POST['descricao'] ?? '', 160),
            'texto'     => Validador::texto($_POST['texto'] ?? '', 600),
            'estrelas'  => (int) ($_POST['estrelas'] ?? 5),
            'ordem'     => (int) ($_POST['ordem'] ?? 0),
        ];
        $erros = [];
        if ($dados['nome'] === '') $erros['nome'] = 'Indique o nome do cliente.';
        if ($dados['texto'] === '') $erros['texto'] = 'Indique o testemunho.';
        if ($erros) {
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }
        return $dados;
    }

    public function testemunhoCriar(): void
    {
        $this->acao('criar');
        (new Testemunho())->criar($this->dadosTestemunho());
        $this->json(['sucesso' => true, 'mensagem' => 'Testemunho criado com sucesso.']);
    }

    public function testemunhoAtualizar(int $id): void
    {
        $this->acao('editar');
        (new Testemunho())->atualizar($id, $this->dadosTestemunho());
        $this->json(['sucesso' => true, 'mensagem' => 'Testemunho actualizado.']);
    }

    public function testemunhoEstado(int $id): void
    {
        $this->acao('editar');
        (new Testemunho())->atualizarEstado($id, (int) ($_POST['ativo'] ?? 0) === 1 ? 1 : 0);
        $this->json(['sucesso' => true, 'mensagem' => 'Testemunho actualizado.']);
    }

    public function testemunhoRemover(int $id): void
    {
        $this->acao('eliminar');
        (new Testemunho())->remover($id);
        $this->json(['sucesso' => true, 'mensagem' => 'Testemunho removido.']);
    }
}
