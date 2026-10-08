<?php

namespace App\Controllers;

use Core\Controller;
use Core\Html;
use Core\Upload;
use Core\Validador;
use App\Models\HeroSlide;
use App\Models\SecaoSite;
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
            'texto'             => Html::limpar((string) ($_POST['texto'] ?? ''), 'curto'),
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
            'descricao'         => Html::limpar((string) ($_POST['descricao'] ?? ''), 'curto'),
            'imagem_alt'        => Validador::texto($_POST['imagem_alt'] ?? '', 160),
            'mensagem_whatsapp' => Validador::texto($_POST['mensagem_whatsapp'] ?? '', 255),
            'ordem'             => (int) ($_POST['ordem'] ?? 0),
        ];
        $erros = [];
        if ($dados['titulo'] === '') $erros['titulo'] = 'Indique o nome do serviço.';
        if (Html::texto($dados['descricao']) === '') $erros['descricao'] = 'Indique uma descrição curta.';
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
            'texto'     => Html::limpar((string) ($_POST['texto'] ?? ''), 'curto'),
            'estrelas'  => (int) ($_POST['estrelas'] ?? 5),
            'ordem'     => (int) ($_POST['ordem'] ?? 0),
        ];
        $erros = [];
        if ($dados['nome'] === '') $erros['nome'] = 'Indique o nome do cliente.';
        if (Html::texto($dados['texto']) === '') $erros['texto'] = 'Indique o testemunho.';
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

    // ==================== Secções do site (CMS genérico) ====================

    public function secoes(): void
    {
        $this->pagina('conteudo-secoes', 'Secções do site', ['secoes' => (new SecaoSite())->resumo()]);
    }

    public function secao(string $chave): void
    {
        $def = $this->definicaoOu404($chave);
        $modelo = new SecaoSite();

        $itens = [];
        foreach ($def['grupos'] ?? [] as $grupo => $_) {
            $itens[$grupo] = $modelo->itens($chave, $grupo);
        }

        $this->exigirPermissao('conteudo', 'ver');
        $this->view('admin/conteudo-secao', [
            'chave'       => $chave,
            'definicao'   => $def,
            'secao'       => $modelo->obter($chave),
            'itensGrupos' => $itens,
            'paginaAtual' => 'conteudo-secoes',
        ], [
            'titulo' => $def['nome'] . ' | Painel Administrativo',
        ], 'admin');
    }

    public function secaoGuardar(string $chave): void
    {
        $this->acao('editar');
        $def = $this->definicaoOu404($chave, true);
        $modelo = new SecaoSite();
        $atual = $modelo->obter($chave)['campos'];

        [$campos, $erros, $novasImagens, $antigas] = $this->lerCampos($def['campos'], $atual, 'secoes');
        if ($erros) {
            array_map([Upload::class, 'remover'], $novasImagens);
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        $modelo->guardar($chave, $campos);
        array_map([Upload::class, 'remover'], $antigas);
        (new \App\Models\Utilizador())->registarLog(\Core\Auth::id(), 'secao_editada', 'Secção: ' . $def['nome']);

        $imagens = [];
        foreach ($def['campos'] as $campo => $defCampo) {
            if ($defCampo['tipo'] === 'imagem') {
                $imagens[$campo] = $campos[$campo] ? BASE . '/' . $campos[$campo] : '';
            }
        }

        $this->json(['sucesso' => true, 'mensagem' => 'Secção "' . $def['nome'] . '" guardada.', 'imagens' => $imagens]);
    }

    public function secaoVisibilidade(string $chave): void
    {
        $this->acao('editar');
        $def = $this->definicaoOu404($chave, true);
        if (empty($def['ocultavel'])) {
            $this->json(['sucesso' => false, 'mensagem' => 'Esta secção não pode ser escondida.'], 422);
        }
        $visivel = (int) ($_POST['ativo'] ?? 0) === 1 ? 1 : 0;
        (new SecaoSite())->definirVisivel($chave, $visivel);
        $this->json(['sucesso' => true, 'mensagem' => $visivel ? 'Secção visível no site.' : 'Secção escondida do site.']);
    }

    public function secaoItemCriar(string $chave): void
    {
        $this->acao('criar');
        $def = $this->definicaoOu404($chave, true);
        $grupo = (string) ($_POST['grupo'] ?? '');
        $defGrupo = $def['grupos'][$grupo] ?? null;
        if (!$defGrupo) {
            $this->json(['sucesso' => false, 'mensagem' => 'Grupo inválido.'], 422);
        }

        [$dados, $erros] = $this->lerCampos($defGrupo['campos'], [], 'secoes');
        if ($erros) {
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        (new SecaoSite())->criarItem($chave, $grupo, $dados, (int) ($_POST['ordem'] ?? 0));
        $this->json(['sucesso' => true, 'mensagem' => 'Item adicionado.']);
    }

    public function secaoItemAtualizar(int $id): void
    {
        $this->acao('editar');
        $modelo = new SecaoSite();
        [$item, $defGrupo] = $this->itemOu404($modelo, $id);

        [$dados, $erros] = $this->lerCampos($defGrupo['campos'], $item['dados'], 'secoes');
        if ($erros) {
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        $modelo->atualizarItem($id, $dados, (int) ($_POST['ordem'] ?? $item['ordem']));
        $this->json(['sucesso' => true, 'mensagem' => 'Item actualizado.']);
    }

    public function secaoItemEstado(int $id): void
    {
        $this->acao('editar');
        $modelo = new SecaoSite();
        $this->itemOu404($modelo, $id);
        $modelo->estadoItem($id, (int) ($_POST['ativo'] ?? 0) === 1 ? 1 : 0);
        $this->json(['sucesso' => true, 'mensagem' => 'Item actualizado.']);
    }

    public function secaoItemRemover(int $id): void
    {
        $this->acao('eliminar');
        $modelo = new SecaoSite();
        $this->itemOu404($modelo, $id);
        $modelo->removerItem($id);
        $this->json(['sucesso' => true, 'mensagem' => 'Item removido.']);
    }

    private function definicaoOu404(string $chave, bool $json = false): array
    {
        $def = SecaoSite::definicao($chave);
        if ($def) {
            return $def;
        }
        if ($json) {
            $this->json(['sucesso' => false, 'mensagem' => 'Secção não encontrada.'], 404);
        }
        http_response_code(404);
        require CAMINHO_RAIZ . '/app/Views/paginas/404.php';
        exit;
    }

    /** @return array{0: array, 1: array} item e definição do seu grupo */
    private function itemOu404(SecaoSite $modelo, int $id): array
    {
        $item = $modelo->itemPorId($id);
        $defGrupo = $item ? (SecaoSite::definicao($item['secao'])['grupos'][$item['grupo']] ?? null) : null;
        if (!$item || !$defGrupo) {
            $this->json(['sucesso' => false, 'mensagem' => 'Item não encontrado.'], 404);
        }
        return [$item, $defGrupo];
    }

    /**
     * Lê e valida os campos de um formulário do CMS conforme o tipo definido
     * em config/secoes.php. Devolve [valores, erros, imagens novas, imagens antigas a apagar].
     */
    private function lerCampos(array $definicoes, array $atuais, string $pastaUpload): array
    {
        $valores = [];
        $erros = [];
        $novas = [];
        $antigas = [];

        foreach ($definicoes as $campo => $def) {
            $tipo = $def['tipo'];
            $bruto = $_POST[$campo] ?? '';

            switch ($tipo) {
                case 'curto':
                case 'rico':
                case 'lista':
                    $valor = Html::limpar(is_string($bruto) ? $bruto : '', $tipo);
                    if (mb_strlen($valor) > 20000) {
                        $erros[$campo] = 'Texto demasiado longo.';
                    }
                    if (!empty($def['obrigatorio']) && Html::texto($valor) === '') {
                        $erros[$campo] = 'Preencha este campo.';
                    }
                    break;

                case 'icone':
                    $valor = strtolower(Validador::texto($bruto, 60));
                    if ($valor !== '' && !str_starts_with($valor, 'mdi-')) {
                        $valor = 'mdi-' . $valor;
                    }
                    if ($valor !== '' && !preg_match('/^mdi-[a-z0-9-]+$/', $valor)) {
                        $erros[$campo] = 'Use o nome de um ícone, ex.: mdi-whatsapp';
                    }
                    break;

                case 'checkbox':
                    $valor = !empty($bruto) ? 1 : 0;
                    break;

                case 'imagem':
                    $valor = $atuais[$campo] ?? ($def['padrao'] ?? '');
                    try {
                        $nova = Upload::imagem($_FILES[$campo] ?? null, $pastaUpload);
                    } catch (\RuntimeException $e) {
                        $erros[$campo] = $e->getMessage();
                        $nova = null;
                    }
                    if ($nova) {
                        $novas[] = $nova;
                        $antigas[] = $valor;
                        $valor = $nova;
                    } elseif (!empty($_POST['repor_' . $campo]) && isset($def['padrao'])) {
                        $antigas[] = $valor;
                        $valor = $def['padrao'];
                    }
                    break;

                default: // texto
                    $valor = Validador::texto($bruto, (int) ($def['max'] ?? 255));
                    if (!empty($def['obrigatorio']) && $valor === '') {
                        $erros[$campo] = 'Preencha este campo.';
                    }
            }

            $valores[$campo] = $valor;
        }

        return [$valores, $erros, $novas, $antigas];
    }
}
