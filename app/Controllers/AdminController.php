<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Permissoes;
use Core\Validador;
use App\Models\Utilizador;
use App\Models\Artigo;
use App\Models\ConfiguracaoSite;
use App\Models\Servico;
use App\Models\Testemunho;

// =====================================================================
// Painel administrativo — dashboard, utilizadores/permissões, perfil
// próprio e definições do site. O conteúdo da homepage está em
// AdminConteudoController e o blog em AdminBlogController.
// =====================================================================

class AdminController extends Controller
{
    private const TIPOS_VALIDOS = ['administrador', 'funcionario'];

    /**
     * Anti escalada de privilégios: só um administrador pode criar/editar/remover
     * contas de administrador ou atribuir o tipo "administrador".
     */
    private function exigirPodeGerirConta(?array $alvo, ?string $novoTipo = null): void
    {
        if (Auth::ehTipo('administrador')) {
            return;
        }
        if (($alvo && $alvo['tipo'] === 'administrador') || $novoTipo === 'administrador') {
            $this->json(['sucesso' => false, 'mensagem' => 'Só um administrador pode gerir contas de administrador.'], 403);
        }
        if ($alvo && (int) $alvo['id'] === Auth::id() && $novoTipo !== null && $novoTipo !== $alvo['tipo']) {
            $this->json(['sucesso' => false, 'mensagem' => 'Não pode alterar o seu próprio tipo de conta.'], 403);
        }
    }

    /**
     * Matriz de permissões que o utilizador actual pode atribuir: o administrador
     * atribui tudo; um funcionário só concede permissões que ele próprio tem.
     */
    private function filtrarPermissoesAtribuiveis(int $alvoId, array $matriz): array
    {
        $alvo = (new Utilizador())->porId($alvoId);
        if (!$alvo || $alvo['tipo'] !== 'funcionario') {
            $this->json(['sucesso' => false, 'mensagem' => 'Só é possível definir permissões de contas de funcionário.'], 422);
        }
        if (Auth::ehTipo('administrador')) {
            return $matriz;
        }
        if ($alvoId === Auth::id()) {
            $this->json(['sucesso' => false, 'mensagem' => 'Não pode alterar as suas próprias permissões.'], 403);
        }

        $filtrada = [];
        foreach (Permissoes::MODULOS as $modulo => $_) {
            foreach (Permissoes::ACOES as $acao => $__) {
                if (!empty($matriz[$modulo][$acao]) && Permissoes::tem($modulo, $acao)) {
                    $filtrada[$modulo][$acao] = '1';
                }
            }
        }
        return $filtrada;
    }

    // ==================== Dashboard ====================

    public function dashboard(): void
    {
        // Funcionário sem acesso ao Dashboard vai para a primeira área que pode ver.
        if (Auth::ehTipo('funcionario') && !Permissoes::tem('dashboard', 'ver')) {
            $pagina = Permissoes::primeiraPagina();
            if ($pagina !== null) {
                header('Location: ' . caminho($pagina));
                exit;
            }
        }

        $this->exigirPermissao('dashboard', 'ver');

        $artigoModel = new Artigo();

        $this->view('admin/dashboard', [
            'blog'        => $artigoModel->resumo(),
            'maisLidos'   => $artigoModel->maisLidos(5),
            'servicos'    => count((new Servico())->todosAtivos()),
            'testemunhos' => count((new Testemunho())->todosAtivos(100)),
            'atividade'   => (new Utilizador())->atividadeRecente(8),
            'paginaAtual' => 'dashboard',
        ], [
            'titulo'    => 'Dashboard | Painel Administrativo',
            'canonical' => URL_BASE . '/admin',
        ], 'admin');
    }

    // ==================== Utilizadores ====================

    public function utilizadores(): void
    {
        $this->exigirPermissao('utilizadores', 'ver');

        $this->view('admin/utilizadores', [
            'utilizadores' => (new Utilizador())->listarTodos(),
            'tipos'        => self::TIPOS_VALIDOS,
            'modulos'      => Permissoes::MODULOS,
            'acoes'        => Permissoes::ACOES,
            'paginaAtual'  => 'utilizadores',
        ], [
            'titulo'    => 'Utilizadores | Painel Administrativo',
            'canonical' => URL_BASE . '/admin/utilizadores',
        ], 'admin');
    }

    public function utilizadorCriar(): void
    {
        $this->exigirPermissao('utilizadores', 'criar');
        $this->exigirCsrf();

        $modelo = new Utilizador();

        $nome     = Validador::texto($_POST['nome'] ?? '', 150);
        $email    = strtolower(trim((string) ($_POST['email'] ?? '')));
        $tipo     = (string) ($_POST['tipo'] ?? 'funcionario');
        $senha    = (string) ($_POST['senha'] ?? '');
        $telefone = Validador::texto($_POST['telefone'] ?? '', 20);

        $this->exigirPodeGerirConta(null, $tipo);

        $erros = [];
        if ($nome === '') $erros['nome'] = 'Indique o nome.';
        if (!Validador::email($email)) $erros['email'] = 'Indique um email válido.';
        elseif ($modelo->emailExiste($email)) $erros['email'] = 'Já existe uma conta com este email.';
        if (!in_array($tipo, self::TIPOS_VALIDOS, true)) $erros['tipo'] = 'Tipo inválido.';
        if (!Validador::telefone($telefone)) $erros['telefone'] = 'Indique um telefone válido.';
        if (($erroSenha = Validador::erroSenha($senha, $email)) !== null) $erros['senha'] = $erroSenha;

        if (!empty($erros)) {
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        $id = $modelo->criar(compact('nome', 'email', 'telefone', 'senha', 'tipo'));
        $modelo->registarLog(Auth::id(), 'utilizador_criado', "Utilizador #{$id} criado ({$tipo})");

        $this->json(['sucesso' => true, 'mensagem' => 'Utilizador criado com sucesso.']);
    }

    /** Toggle rápido de activo/inactivo (checkbox na tabela). */
    public function utilizadorEstado(int $id): void
    {
        $this->exigirPermissao('utilizadores', 'editar');
        $this->exigirCsrf();

        $ativo = (int) ($_POST['ativo'] ?? 0) === 1 ? 1 : 0;
        if ($id === Auth::id() && $ativo === 0) {
            $this->json(['sucesso' => false, 'mensagem' => 'Não pode desactivar a sua própria conta.'], 422);
        }

        $modelo = new Utilizador();
        $alvo = $modelo->porId($id);
        if (!$alvo) {
            $this->json(['sucesso' => false, 'mensagem' => 'Utilizador não encontrado.'], 404);
        }
        $this->exigirPodeGerirConta($alvo);

        $modelo->atualizarPermissoes($id, null, $ativo);
        $modelo->registarLog(Auth::id(), 'utilizador_estado', "Utilizador #{$id} — activo: {$ativo}");

        $this->json(['sucesso' => true, 'mensagem' => $ativo ? 'Conta activada.' : 'Conta desactivada.']);
    }

    /** Edição completa (nome, email, telefone, tipo e, opcionalmente, senha) — via sheet. */
    public function utilizadorAtualizar(int $id): void
    {
        $this->exigirPermissao('utilizadores', 'editar');
        $this->exigirCsrf();

        $modelo = new Utilizador();
        $existente = $modelo->porId($id);
        if (!$existente) {
            $this->json(['sucesso' => false, 'mensagem' => 'Utilizador não encontrado.'], 404);
        }

        $nome     = Validador::texto($_POST['nome'] ?? '', 150);
        $email    = strtolower(trim((string) ($_POST['email'] ?? '')));
        $tipo     = (string) ($_POST['tipo'] ?? $existente['tipo']);
        $senha    = (string) ($_POST['senha'] ?? '');
        $telefone = Validador::texto($_POST['telefone'] ?? '', 20);

        $this->exigirPodeGerirConta($existente, $tipo);

        $erros = [];
        if ($nome === '') $erros['nome'] = 'Indique o nome.';
        if (!Validador::email($email)) $erros['email'] = 'Indique um email válido.';
        elseif ($modelo->emailExisteNoutroUtilizador($email, $id)) $erros['email'] = 'Já existe outra conta com este email.';
        if (!in_array($tipo, self::TIPOS_VALIDOS, true)) $erros['tipo'] = 'Tipo inválido.';
        if (!Validador::telefone($telefone)) $erros['telefone'] = 'Indique um telefone válido.';
        if ($senha !== '' && ($erroSenha = Validador::erroSenha($senha, $email)) !== null) $erros['senha'] = $erroSenha;
        if ($id === Auth::id() && $tipo !== $existente['tipo']) $erros['tipo'] = 'Não pode alterar o tipo da sua própria conta.';

        if (!empty($erros)) {
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        $modelo->atualizarCompleto($id, compact('nome', 'email', 'telefone', 'tipo'));
        if ($senha !== '') {
            $modelo->atualizarSenha($id, $senha);
            $modelo->invalidarTokensRecuperacao($id);
        }

        $modelo->registarLog(Auth::id(), 'utilizador_editado', "Utilizador #{$id} editado");

        $this->json(['sucesso' => true, 'mensagem' => 'Utilizador actualizado com sucesso.']);
    }

    public function utilizadorEliminar(int $id): void
    {
        $this->exigirPermissao('utilizadores', 'eliminar');
        $this->exigirCsrf();

        if ($id === Auth::id()) {
            $this->json(['sucesso' => false, 'mensagem' => 'Não pode remover a sua própria conta.'], 422);
        }

        $modelo = new Utilizador();
        $utilizador = $modelo->porId($id);
        if (!$utilizador) {
            $this->json(['sucesso' => false, 'mensagem' => 'Utilizador não encontrado.'], 404);
        }
        $this->exigirPodeGerirConta($utilizador);

        $modelo->eliminar($id);
        \Core\Upload::remover($utilizador['avatar'] ?? null);
        $modelo->registarLog(Auth::id(), 'utilizador_removido', "Utilizador #{$id} ({$utilizador['email']}) removido");

        $this->json(['sucesso' => true, 'mensagem' => 'Utilizador removido.']);
    }

    public function permissoesObter(int $id): void
    {
        $this->exigirPermissao('utilizadores', 'editar');

        $this->json(['sucesso' => true, 'matriz' => Permissoes::matrizDoUtilizador($id)]);
    }

    public function permissoesGuardar(int $id): void
    {
        $this->exigirPermissao('utilizadores', 'editar');
        $this->exigirCsrf();

        $matriz = is_array($_POST['perm'] ?? null) ? $_POST['perm'] : [];
        Permissoes::guardar($id, $this->filtrarPermissoesAtribuiveis($id, $matriz));
        (new Utilizador())->registarLog(Auth::id(), 'permissoes_alteradas', "Permissões do utilizador #{$id} actualizadas");

        $this->json(['sucesso' => true, 'mensagem' => 'Permissões guardadas com sucesso.']);
    }

    // ==================== O meu perfil ====================

    public function perfilAtualizar(): void
    {
        if (!Auth::ehTipo('administrador', 'funcionario')) {
            $this->json(['sucesso' => false, 'mensagem' => 'Sessão expirada. Entre novamente.'], 401);
        }
        $this->exigirCsrf();

        $modelo = new Utilizador();
        $eu = $modelo->porId((int) Auth::id());

        $nome       = Validador::texto($_POST['nome'] ?? '', 150);
        $telefone   = Validador::texto($_POST['telefone'] ?? '', 20);
        $senha      = (string) ($_POST['senha'] ?? '');
        $confirmar  = (string) ($_POST['confirmar_senha'] ?? '');
        $senhaAtual = (string) ($_POST['senha_atual'] ?? '');

        $erros = [];
        if (mb_strlen($nome) < 3) $erros['nome'] = 'Indique o seu nome.';
        if (!Validador::telefone($telefone)) $erros['telefone'] = 'Indique um telefone válido.';
        if ($senha !== '') {
            if (!$modelo->verificarSenha($senhaAtual, $eu['senha_hash'])) $erros['senha_atual'] = 'A senha actual não está correcta.';
            elseif (($erroSenha = Validador::erroSenha($senha, $eu['email'])) !== null) $erros['senha'] = $erroSenha;
            elseif (!hash_equals($senha, $confirmar)) $erros['confirmar_senha'] = 'As senhas não coincidem.';
        }

        try {
            $avatar = \Core\Upload::imagem($_FILES['avatar'] ?? null, 'avatares');
        } catch (\RuntimeException $e) {
            $erros['avatar'] = $e->getMessage();
            $avatar = null;
        }

        if (!empty($erros)) {
            \Core\Upload::remover($avatar);
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        $modelo->atualizarPerfil((int) $eu['id'], ['nome' => $nome, 'telefone' => $telefone ?: null]);
        Auth::atualizarNomeSessao($nome);
        if ($avatar) {
            $modelo->atualizarAvatar((int) $eu['id'], $avatar);
            \Core\Upload::remover($eu['avatar']);
        }
        if ($senha !== '') {
            $modelo->atualizarSenha((int) $eu['id'], $senha);
            $modelo->invalidarTokensRecuperacao((int) $eu['id']);
        }
        $modelo->registarLog((int) $eu['id'], 'perfil_proprio_editado', null);

        $this->json(['sucesso' => true, 'mensagem' => 'Perfil actualizado.']);
    }

    // ==================== Definições do site ====================

    public function definicoes(): void
    {
        $this->exigirPermissao('definicoes', 'ver');

        $this->view('admin/definicoes', [
            'configuracoes' => ConfiguracaoSite::publicas(),
            'paginaAtual'   => 'definicoes',
        ], [
            'titulo'    => 'Definições | Painel Administrativo',
            'canonical' => URL_BASE . '/admin/definicoes',
        ], 'admin');
    }

    public function definicoesGuardar(): void
    {
        $this->exigirPermissao('definicoes', 'editar');
        $this->exigirCsrf();

        $dados = [
            'telefone'          => Validador::texto($_POST['telefone'] ?? '', 40),
            'whatsapp'          => preg_replace('/\D+/', '', (string) ($_POST['whatsapp'] ?? '')),
            'mensagem_whatsapp' => Validador::texto($_POST['mensagem_whatsapp'] ?? '', 200),
            'email'             => Validador::texto($_POST['email'] ?? '', 150),
            'endereco'          => Validador::texto($_POST['endereco'] ?? '', 255),
            'endereco_curto'    => Validador::texto($_POST['endereco_curto'] ?? '', 80),
            'horario'           => Validador::texto($_POST['horario'] ?? '', 80),
            'horario_completo'  => Validador::texto($_POST['horario_completo'] ?? '', 150),
            'mapa_embed'        => Validador::texto($_POST['mapa_embed'] ?? '', 1500),
        ];
        $erros = [];

        if ($dados['email'] !== '' && !Validador::email($dados['email'])) {
            $erros['email'] = 'Indique um email válido.';
        }
        if ($dados['whatsapp'] !== '' && (strlen($dados['whatsapp']) < 9 || strlen($dados['whatsapp']) > 15)) {
            $erros['whatsapp'] = 'Indique o número com indicativo, ex.: 244938070748.';
        }
        // Aceita o código <iframe> completo copiado do Google Maps e extrai o endereço.
        if (preg_match('/src="([^"]+)"/', $dados['mapa_embed'], $m)) {
            $dados['mapa_embed'] = html_entity_decode($m[1]);
        }
        if ($dados['mapa_embed'] !== '' && !preg_match('#^https://(www\.)?google\.[a-z.]+/maps/embed\?#', $dados['mapa_embed'])) {
            $erros['mapa_embed'] = 'Cole o endereço "Incorporar mapa" do Google Maps (começa por https://www.google.com/maps/embed?).';
        }

        foreach (ConfiguracaoSite::REDES as $chave => [$nome]) {
            $url = Validador::texto($_POST[$chave] ?? '', 255);
            if ($url !== '' && !preg_match('#^https?://#i', $url)) {
                $url = 'https://' . $url;
            }
            if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
                $erros[$chave] = "Indique o endereço completo da página de {$nome}.";
            }
            $dados[$chave] = $url;
        }

        if ($erros) {
            $this->json(['sucesso' => false, 'erros' => $erros], 422);
        }

        (new ConfiguracaoSite())->guardar($dados);
        (new Utilizador())->registarLog(Auth::id(), 'definicoes_alteradas', null);

        $this->json(['sucesso' => true, 'mensagem' => 'Definições guardadas com sucesso.', 'dados' => $dados]);
    }
}
