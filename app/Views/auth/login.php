<div class="card p-6 md:p-8 shadow-sm">
    <h1 class="text-2xl font-extrabold text-ink mb-1">Painel de gestão</h1>
    <p class="text-sm text-muted mb-6">Entre para gerir o site, o blog e os conteúdos.</p>

    <form data-ajax-form data-endpoint="<?= BASE ?>/login" data-texto-enviando="A entrar..." novalidate>
        <?= \Core\Csrf::campo() ?>
        <input type="hidden" name="redirecionar" value="<?= htmlspecialchars($redirecionar ?? '') ?>">

        <div class="form-mensagem hidden mb-5 p-3 rounded-card text-sm" role="alert" aria-live="polite"></div>

        <div class="mb-4">
            <label for="email" class="rotulo">Email</label>
            <input type="email" id="email" name="email" required maxlength="150" autocomplete="email" autofocus class="campo py-2.5">
            <p class="campo-erro hidden" data-campo="email"></p>
        </div>

        <div class="mb-2">
            <label for="senha" class="rotulo">Senha</label>
            <input type="password" id="senha" name="senha" required maxlength="72" autocomplete="current-password" class="campo py-2.5">
            <p class="campo-erro hidden" data-campo="senha"></p>
        </div>

        <div class="text-right mb-6">
            <a href="<?= BASE ?>/recuperar-senha" class="text-sm font-semibold text-teal-dark hover:underline">Esqueceu a senha?</a>
        </div>

        <button type="submit" class="btn-primary w-full py-3">
            <span class="texto-btn">Entrar</span>
        </button>
    </form>
</div>
