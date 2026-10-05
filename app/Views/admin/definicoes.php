<?php
$podeEditar = \Core\Permissoes::tem('definicoes', 'editar');
$c = $configuracoes;
$campo = static function (string $nome, string $rotulo, string $ajuda = '', string $tipo = 'text', int $max = 255) use ($c, $podeEditar): string {
    return '<div>'
        . '<label class="rotulo" for="def-' . $nome . '">' . htmlspecialchars($rotulo) . '</label>'
        . '<input type="' . $tipo . '" id="def-' . $nome . '" name="' . $nome . '" maxlength="' . $max . '" value="' . htmlspecialchars((string) ($c[$nome] ?? '')) . '" class="campo"' . ($podeEditar ? '' : ' disabled') . '>'
        . ($ajuda !== '' ? '<p class="ajuda">' . $ajuda . '</p>' : '')
        . '<p class="campo-erro hidden" data-campo="' . $nome . '"></p>'
        . '</div>';
};
?>
<section class="admin-container pb-14">
    <div class="admin-page-head">
        <div>
            <h1>Definições do site</h1>
            <p>Contactos, horário, redes sociais e mapa — aparecem no cabeçalho, rodapé e secção de contacto.</p>
        </div>
    </div>

    <form data-ajax-form data-manter-valores data-endpoint="<?= BASE ?>/admin/definicoes/guardar" data-texto-enviando="A guardar..." data-scroll-erro novalidate class="max-w-4xl space-y-5">
        <?= \Core\Csrf::campo() ?>

        <div class="card p-6">
            <h2 class="text-base font-bold text-ink mb-1">Contactos</h2>
            <p class="text-xs text-muted mb-5">Usados nos botões "Ligue-nos", "Falar no WhatsApp" e no rodapé.</p>
            <div class="grid md:grid-cols-2 gap-4">
                <?= $campo('telefone', 'Telefone (como aparece no site)', 'Ex.: +244 938 070 748', 'text', 40) ?>
                <?= $campo('whatsapp', 'Número de WhatsApp', 'Só números, com indicativo do país. Ex.: 244938070748', 'text', 20) ?>
                <?= $campo('email', 'Email', '', 'email', 150) ?>
                <?= $campo('mensagem_whatsapp', 'Mensagem inicial do WhatsApp', 'Texto já escrito quando o cliente abre a conversa.', 'text', 200) ?>
            </div>
        </div>

        <div class="card p-6">
            <h2 class="text-base font-bold text-ink mb-5">Morada e horário</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="md:col-span-2"><?= $campo('endereco', 'Endereço completo', 'Mostrado na secção de contacto.') ?></div>
                <?= $campo('endereco_curto', 'Endereço curto', 'Barra do topo e rodapé. Ex.: São Paulo, Luanda', 'text', 80) ?>
                <?= $campo('horario', 'Horário (curto)', 'Separe os dias com "·". Ex.: Seg–Sex 08h–18h · Sáb 09h–13h', 'text', 80) ?>
                <div class="md:col-span-2"><?= $campo('horario_completo', 'Horário (completo)', 'Mostrado na secção de contacto.', 'text', 150) ?></div>
                <div class="md:col-span-2">
                    <label class="rotulo" for="def-mapa_embed">Mapa (Google Maps)</label>
                    <textarea id="def-mapa_embed" name="mapa_embed" rows="3" maxlength="1500" class="campo font-mono text-xs"<?= $podeEditar ? '' : ' disabled' ?>><?= htmlspecialchars((string) ($c['mapa_embed'] ?? '')) ?></textarea>
                    <p class="ajuda">No Google Maps: Partilhar → Incorporar um mapa → copiar HTML. Pode colar o código completo; deixe vazio para esconder o mapa.</p>
                    <p class="campo-erro hidden" data-campo="mapa_embed"></p>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h2 class="text-base font-bold text-ink mb-1">Redes sociais</h2>
            <p class="text-xs text-muted mb-5">Endereço completo da página. Deixe vazio para esconder o ícone no rodapé.</p>
            <div class="grid md:grid-cols-2 gap-4">
                <?php foreach (\App\Models\ConfiguracaoSite::REDES as $chave => [$nomeRede]): ?>
                <?= $campo($chave, $nomeRede, 'Ex.: https://facebook.com/axiora') ?>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($podeEditar): ?>
        <div class="flex justify-end">
            <button type="submit" class="btn-primary"><span class="texto-btn">Guardar definições</span></button>
        </div>
        <?php endif; ?>
    </form>
</section>
