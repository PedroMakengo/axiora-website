<?php
/**
 * Desenha um campo do CMS conforme o tipo definido em config/secoes.php.
 * Os campos de texto longo usam o editor de texto (textarea[data-editor],
 * transformado pelo assets/js/admin.js).
 */
if (!function_exists('campoCms')) {
    function campoCms(string $nome, array $def, $valor, string $prefixoId = 'cms'): string
    {
        $id = $prefixoId . '-' . $nome;
        $rotulo = htmlspecialchars($def['rotulo'] ?? $nome);
        $ajuda = !empty($def['ajuda']) ? '<p class="ajuda">' . htmlspecialchars($def['ajuda']) . '</p>' : '';
        $erro = '<p class="campo-erro hidden" data-campo="' . $nome . '"></p>';
        $obrigatorio = !empty($def['obrigatorio']) ? ' required' : '';
        $valorTexto = htmlspecialchars((string) $valor);

        switch ($def['tipo']) {
            case 'curto':
            case 'rico':
            case 'lista':
                return '<div><label class="rotulo" for="' . $id . '">' . $rotulo . '</label>'
                    . '<textarea id="' . $id . '" name="' . $nome . '" data-editor="' . $def['tipo'] . '" class="campo"' . $obrigatorio . '>' . $valorTexto . '</textarea>'
                    . $ajuda . $erro . '</div>';

            case 'icone':
                return '<div><label class="rotulo" for="' . $id . '">' . $rotulo . '</label>'
                    . '<div class="campo-icone"><span class="campo-icone__preview"><i class="mdi ' . $valorTexto . '"></i></span>'
                    . '<input type="text" id="' . $id . '" name="' . $nome . '" value="' . $valorTexto . '" maxlength="60" list="icones-mdi" class="campo" data-icone placeholder="mdi-whatsapp"></div>'
                    . '<p class="ajuda">Nome do ícone em <a href="https://pictogrammers.com/library/mdi/" target="_blank" rel="noopener" class="text-teal-dark font-semibold hover:underline">pictogrammers.com/library/mdi</a> (ex.: mdi-car-outline).</p>'
                    . $erro . '</div>';

            case 'checkbox':
                return '<label class="flex items-center gap-2.5 text-sm font-semibold text-ink cursor-pointer">'
                    . '<input type="checkbox" name="' . $nome . '" value="1"' . (!empty($valor) ? ' checked' : '') . '> ' . $rotulo . '</label>';

            case 'imagem':
                $src = $valor ? BASE . '/' . htmlspecialchars((string) $valor) : '';
                $repor = isset($def['padrao']) && $valor !== $def['padrao']
                    ? '<label class="flex items-center gap-2 text-xs text-muted mt-2 cursor-pointer"><input type="checkbox" name="repor_' . $nome . '" value="1"> Repor a imagem original</label>'
                    : '';
                return '<div><label class="rotulo">' . $rotulo . '</label>'
                    . '<div class="upload-preview"><img src="' . $src . '" alt="" class="upload-preview__img' . ($src ? '' : ' hidden') . '" data-preview-imagem data-imagem-campo="' . $nome . '">'
                    . '<input type="file" name="' . $nome . '" accept="image/jpeg,image/png,image/webp" data-max-mb="4" class="upload-field"></div>'
                    . $ajuda . $repor . $erro . '</div>';

            default:
                return '<div><label class="rotulo" for="' . $id . '">' . $rotulo . '</label>'
                    . '<input type="text" id="' . $id . '" name="' . $nome . '" value="' . $valorTexto . '" maxlength="' . (int) ($def['max'] ?? 255) . '" class="campo"' . $obrigatorio . '>'
                    . $ajuda . $erro . '</div>';
        }
    }
}
