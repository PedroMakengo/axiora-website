<div class="card p-6 md:p-8 shadow-sm">
    <h1 class="text-2xl font-extrabold text-ink mb-1">Recuperar senha</h1>
    <p class="text-sm text-muted mb-6">Indique o email da sua conta. Enviaremos um link para definir uma nova senha (válido por 1 hora).</p>

    <form data-ajax-form data-endpoint="<?= BASE ?>/recuperar-senha" data-texto-enviando="A enviar..." novalidate>
        <?= \Core\Csrf::campo() ?>
        <div class="form-mensagem hidden mb-5 p-3 rounded-card text-sm" role="alert" aria-live="polite"></div>

        <div class="mb-6">
            <label for="email" class="rotulo">Email</label>
            <input type="email" id="email" name="email" required maxlength="150" autocomplete="email" autofocus class="campo py-2.5">
            <p class="campo-erro hidden" data-campo="email"></p>
        </div>

        <button type="submit" class="btn-primary w-full py-3"><span class="texto-btn">Enviar link de recuperação</span></button>
    </form>

    <p class="text-center text-sm text-muted mt-6">
        <a href="<?= BASE ?>/login" class="font-semibold text-teal-dark hover:underline">&larr; Voltar ao login</a>
    </p>
</div>
