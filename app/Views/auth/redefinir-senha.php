<div class="card p-6 md:p-8 shadow-sm">
    <h1 class="text-2xl font-extrabold text-ink mb-1">Definir nova senha</h1>
    <p class="text-sm text-muted mb-6">Use pelo menos 8 caracteres, combinando letras e números.</p>

    <form data-ajax-form data-endpoint="<?= BASE ?>/redefinir-senha" data-texto-enviando="A guardar..." novalidate>
        <?= \Core\Csrf::campo() ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="form-mensagem hidden mb-5 p-3 rounded-card text-sm" role="alert" aria-live="polite"></div>

        <div class="mb-4">
            <label for="senha" class="rotulo">Nova senha</label>
            <input type="password" id="senha" name="senha" required minlength="8" maxlength="72" autocomplete="new-password" data-forca-senha class="campo py-2.5">
            <p class="campo-erro hidden" data-campo="senha"></p>
        </div>
        <div class="mb-6">
            <label for="confirmar_senha" class="rotulo">Confirmar nova senha</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" required maxlength="72" autocomplete="new-password" data-igual-a="senha" class="campo py-2.5">
            <p class="campo-erro hidden" data-campo="confirmar_senha"></p>
        </div>

        <button type="submit" class="btn-primary w-full py-3"><span class="texto-btn">Guardar nova senha</span></button>
    </form>
</div>
