        </main>
    </div>
</div>

<div class="sheet-overlay" data-sheet-overlay></div>

<!-- ===================== Sheet global: Editar o meu perfil ===================== -->
<aside class="sheet" id="sheet-meu-perfil" aria-hidden="true">
    <div class="sheet__header">
        <div>
            <h2 class="sheet__title">Editar perfil</h2>
            <p class="sheet__subtitle">Os seus dados de acesso ao painel.</p>
        </div>
        <button type="button" class="sheet__close" data-sheet-close aria-label="Fechar">&times;</button>
    </div>
    <form data-ajax-form data-endpoint="<?= BASE ?>/admin/perfil/atualizar" data-texto-enviando="A guardar..." data-reload-on-success enctype="multipart/form-data" novalidate>
        <?= \Core\Csrf::campo() ?>
        <div class="sheet__body space-y-4">
            <div>
                <label class="rotulo">Foto de perfil</label>
                <div class="upload-preview">
                    <?php if (!empty($utilizadorAtual['avatar'])): ?>
                    <img src="<?= BASE . '/' . htmlspecialchars($utilizadorAtual['avatar']) ?>" alt="" class="upload-preview__avatar">
                    <?php else: ?>
                    <span class="upload-preview__avatar"><?= htmlspecialchars($iniciaisUtilizador) ?></span>
                    <?php endif; ?>
                    <div class="flex-1">
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" data-max-mb="4" class="upload-field">
                        <p class="ajuda">JPG, PNG ou WEBP — até 4MB.</p>
                    </div>
                </div>
                <p class="campo-erro hidden" data-campo="avatar"></p>
            </div>
            <div>
                <label class="rotulo">Nome</label>
                <input type="text" name="nome" required minlength="3" maxlength="150" value="<?= htmlspecialchars($utilizadorAtual['nome'] ?? '') ?>" class="campo">
                <p class="campo-erro hidden" data-campo="nome"></p>
            </div>
            <div>
                <label class="rotulo">Email</label>
                <input type="email" value="<?= htmlspecialchars($utilizadorAtual['email'] ?? '') ?>" disabled class="campo bg-paper text-muted">
                <p class="ajuda">O email é alterado por um administrador em Utilizadores.</p>
            </div>
            <div>
                <label class="rotulo">Telefone</label>
                <input type="tel" name="telefone" maxlength="20" pattern="\+?[0-9\s\(\)\-]{6,20}" value="<?= htmlspecialchars($utilizadorAtual['telefone'] ?? '') ?>" class="campo">
                <p class="campo-erro hidden" data-campo="telefone"></p>
            </div>
            <div class="pt-2 border-t border-line">
                <p class="text-sm font-bold text-ink mt-3 mb-3">Alterar senha <span class="font-normal text-muted">(opcional)</span></p>
                <div class="space-y-4">
                    <div>
                        <label class="rotulo">Nova senha</label>
                        <input type="password" name="senha" minlength="8" maxlength="72" autocomplete="new-password" placeholder="Deixar em branco para manter a actual" data-forca-senha class="campo">
                        <p class="campo-erro hidden" data-campo="senha"></p>
                    </div>
                    <div>
                        <label class="rotulo">Confirmar nova senha</label>
                        <input type="password" name="confirmar_senha" maxlength="72" autocomplete="new-password" data-igual-a="senha" class="campo">
                        <p class="campo-erro hidden" data-campo="confirmar_senha"></p>
                    </div>
                    <div>
                        <label class="rotulo">Senha actual</label>
                        <input type="password" name="senha_atual" maxlength="72" autocomplete="current-password" placeholder="Obrigatória só para alterar a senha" class="campo">
                        <p class="campo-erro hidden" data-campo="senha_atual"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="sheet__footer">
            <button type="button" class="btn-secondary text-sm" data-sheet-close>Cancelar</button>
            <button type="submit" class="btn-primary text-sm"><span class="texto-btn">Guardar</span></button>
        </div>
    </form>
</aside>

<?php if (!empty($usaDataTables)): ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-dt/3.0.3/css/dataTables.dataTables.min.css" integrity="sha384-IkYzSBi8dm4Cg6IWFobByqhtleZ+PI1q8HYZXz9QgfRw+9LiJ3uVKcGDIWZFa+0k" crossorigin="anonymous" referrerpolicy="no-referrer">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpQvzx0cegeju1MVsGrX5xXxAvs/HgeFs" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net/3.0.3/dataTables.min.js" integrity="sha384-X5KZbfdKx8n/0SCjkS34+fUuuNQe0j9vp4fp1aZ1oF52HL5ZYK9USnnd2KifcQ9l" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-dt/3.0.3/js/dataTables.dataTables.min.js" integrity="sha384-iD7jsPICJBoIgDCWznr0BV9JsUdM7NyRN+ZgkkE9zlFLWmRu/cxQ3woLW58VeV3A" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<?php endif; ?>
<script src="<?= BASE ?>/assets/js/admin.js?v=1" defer></script>
<script src="<?= BASE ?>/assets/js/app.js?v=1" defer></script>
</body>
</html>
