<?php
$rotulosAcao = [
    'login'                  => 'entrou no painel',
    'logout'                 => 'terminou sessão',
    'artigo_criado'          => 'criou um artigo',
    'artigo_editado'         => 'editou um artigo',
    'artigo_removido'        => 'removeu um artigo',
    'utilizador_criado'      => 'criou um utilizador',
    'utilizador_editado'     => 'editou um utilizador',
    'utilizador_removido'    => 'removeu um utilizador',
    'utilizador_estado'      => 'alterou o estado de uma conta',
    'permissoes_alteradas'   => 'alterou permissões',
    'definicoes_alteradas'   => 'alterou as definições do site',
    'perfil_proprio_editado' => 'actualizou o próprio perfil',
    'senha_redefinida'       => 'redefiniu a senha',
    'pedido_recuperacao_senha' => 'pediu recuperação de senha',
];
$podeCriarArtigo = \Core\Permissoes::tem('blog', 'criar');
?>
<section class="admin-container pb-14">
    <div class="admin-page-head">
        <div>
            <h1>Olá, <?= htmlspecialchars(explode(' ', \Core\Auth::utilizador()['nome'] ?? '')[0]) ?></h1>
            <p>Resumo do site e do blog da Axiora.</p>
        </div>
        <?php if ($podeCriarArtigo): ?>
        <a href="<?= BASE ?>/admin/blog/novo" class="btn-primary text-sm">+ Novo artigo</a>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <a href="<?= BASE ?>/admin/blog" class="card p-5 block hover:border-teal">
            <p class="text-xs font-semibold text-muted">Artigos publicados</p>
            <p class="text-3xl font-extrabold text-ink mt-1"><?= (int) ($blog['publicados'] ?? 0) ?></p>
            <p class="text-xs text-muted mt-1"><?= (int) ($blog['total'] ?? 0) ?> no total</p>
        </a>
        <a href="<?= BASE ?>/admin/blog" class="card p-5 block hover:border-teal">
            <p class="text-xs font-semibold text-muted">Rascunhos</p>
            <p class="text-3xl font-extrabold text-ink mt-1"><?= (int) ($blog['rascunhos'] ?? 0) ?></p>
            <p class="text-xs text-muted mt-1"><?= (int) ($blog['agendados'] ?? 0) ?> agendado(s)</p>
        </a>
        <div class="card p-5 bg-teal-soft border-teal/30">
            <p class="text-xs font-semibold text-teal-dark">Leituras do blog</p>
            <p class="text-3xl font-extrabold text-teal-dark mt-1"><?= number_format((int) ($blog['visualizacoes'] ?? 0), 0, ',', '.') ?></p>
            <p class="text-xs text-teal-dark/80 mt-1">Visualizações acumuladas</p>
        </div>
        <a href="<?= BASE ?>/admin/conteudo/servicos" class="card p-5 block hover:border-teal">
            <p class="text-xs font-semibold text-muted">Conteúdo da homepage</p>
            <p class="text-3xl font-extrabold text-ink mt-1"><?= (int) $servicos ?></p>
            <p class="text-xs text-muted mt-1">serviços · <?= (int) $testemunhos ?> testemunhos activos</p>
        </a>
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        <div class="card">
            <div class="flex items-center justify-between px-5 py-4 border-b border-line">
                <h2 class="text-sm font-bold text-ink">Artigos mais lidos</h2>
                <a href="<?= BASE ?>/admin/blog" class="text-xs font-semibold text-teal-dark hover:underline">Ver todos</a>
            </div>
            <?php if (empty($maisLidos)): ?>
            <p class="px-5 py-8 text-sm text-muted text-center">Ainda não há artigos publicados.</p>
            <?php else: ?>
            <ul class="divide-y divide-line">
                <?php foreach ($maisLidos as $artigo): ?>
                <li class="flex items-center justify-between gap-4 px-5 py-3">
                    <div class="min-w-0">
                        <a href="<?= BASE ?>/admin/blog/<?= (int) $artigo['id'] ?>/editar" class="block text-sm font-semibold text-ink truncate hover:text-teal-dark"><?= htmlspecialchars($artigo['titulo']) ?></a>
                        <span class="text-xs text-muted"><?= dataPt($artigo['publicado_em'], true) ?></span>
                    </div>
                    <span class="badge bg-paper text-muted"><?= number_format((int) $artigo['visualizacoes'], 0, ',', '.') ?> leituras</span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="px-5 py-4 border-b border-line">
                <h2 class="text-sm font-bold text-ink">Actividade recente</h2>
            </div>
            <?php if (empty($atividade)): ?>
            <p class="px-5 py-8 text-sm text-muted text-center">Sem actividade registada.</p>
            <?php else: ?>
            <ul class="divide-y divide-line">
                <?php foreach ($atividade as $log): ?>
                <li class="px-5 py-3 text-sm">
                    <span class="font-semibold text-ink"><?= htmlspecialchars($log['nome'] ?? 'Sistema') ?></span>
                    <span class="text-muted"><?= htmlspecialchars($rotulosAcao[$log['acao']] ?? str_replace('_', ' ', $log['acao'])) ?></span>
                    <?php if (!empty($log['detalhes']) && str_starts_with($log['acao'], 'artigo_')): ?>
                    <span class="text-muted">— <?= htmlspecialchars(preg_replace('/^Artigo #\d+: /', '', $log['detalhes'])) ?></span>
                    <?php endif; ?>
                    <span class="block text-xs text-muted mt-0.5"><?= date('d/m/Y H:i', strtotime($log['criado_em'])) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</section>
