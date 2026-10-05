  </main>

  <?php
  // Lista de serviços do rodapé — a mesma que aparece na homepage.
  $servicosRodape = $servicos ?? (function () {
      try {
          return (new \App\Models\Servico())->todosAtivos();
      } catch (\Throwable $e) {
          return [];
      }
  })();
  ?>

  <!-- ─── FOOTER ─── -->
  <footer class="footer">
    <div class="container">
      <div class="footer-cta">
        <div>
          <h2>Pronto para resolver o seu assunto?</h2>
          <p>Envie-nos uma mensagem — respondemos no horário de atendimento.</p>
        </div>
        <a href="<?= htmlspecialchars($waGeral) ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener"><i class="mdi mdi-whatsapp"></i> Falar no WhatsApp</a>
      </div>

      <div class="footer-grid">
        <div class="footer-brand">
          <img src="<?= BASE ?>/assets/images/logo.png" alt="Axiora" width="608" height="236" loading="lazy" />
          <p>Soluções práticas e confiáveis em Luanda: viagens, viaturas, serviços técnicos, câmbio e logística.</p>
          <div class="footer-social">
            <?php foreach (\App\Models\ConfiguracaoSite::REDES as $chave => [$nomeRede, $iconeRede]): ?>
              <?php if (!empty($cfg[$chave])): ?>
              <a href="<?= htmlspecialchars($cfg[$chave]) ?>" target="_blank" rel="noopener" aria-label="<?= htmlspecialchars($nomeRede) ?>"><i class="mdi <?= $iconeRede ?>"></i></a>
              <?php endif; ?>
            <?php endforeach; ?>
            <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/\D+/', '', (string) $cfg['whatsapp'])) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="mdi mdi-whatsapp"></i></a>
          </div>
        </div>

        <div class="footer-col">
          <h4>Serviços</h4>
          <ul>
            <?php foreach ($servicosRodape as $servicoRodape): ?>
            <li><a href="<?= $ancora('servicos') ?>"><?= htmlspecialchars($servicoRodape['titulo']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Empresa</h4>
          <ul>
            <li><a href="<?= $ancora('sobre') ?>">Sobre Nós</a></li>
            <li><a href="<?= $ancora('como-funciona') ?>">Como Funciona</a></li>
            <li><a href="<?= $ancora('testemunhos') ?>">Testemunhos</a></li>
            <li><a href="<?= BASE ?>/blog">Blog &amp; Notícias</a></li>
            <li><a href="<?= $ancora('websites') ?>">Websites</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Contacto</h4>
          <ul>
            <li><a href="tel:<?= htmlspecialchars($telLink) ?>"><?= htmlspecialchars($cfg['telefone']) ?></a></li>
            <li><a href="mailto:<?= htmlspecialchars($cfg['email']) ?>"><?= htmlspecialchars($cfg['email']) ?></a></li>
            <li><a href="<?= $ancora('contacto') ?>"><?= htmlspecialchars($cfg['endereco_curto']) ?></a></li>
            <?php foreach (preg_split('/\s*·\s*/u', (string) $cfg['horario']) as $linhaHorario): ?>
            <li><?= htmlspecialchars($linhaHorario) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>© <span id="year"><?= date('Y') ?></span> <?= htmlspecialchars(NOME_EMPRESA) ?>. Todos os direitos reservados.</p>
        <p>Luanda, Angola</p>
      </div>
    </div>
  </footer>

  <a href="<?= htmlspecialchars($waGeral) ?>" class="wa-float" target="_blank" rel="noopener" aria-label="Falar no WhatsApp"><i class="mdi mdi-whatsapp"></i></a>

  <script src="<?= BASE ?>/assets/js/main.js?v=3"></script>
</body>

</html>
