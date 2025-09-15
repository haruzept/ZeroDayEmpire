<?php
// Simple template rendering attack definitions.
?>
<section class="attacks-grid">
  <?php foreach ($defs as $d): ?>
    <article class="card attack">
      <header>
        <h3><?php echo htmlspecialchars($d['name']); ?></h3>
        <span class="badge level">Lv.1</span>
      </header>
      <p class="descr"><?php echo htmlspecialchars($d['descr']); ?></p>
      <form method="post">
        <input type="hidden" name="start_code" value="<?php echo htmlspecialchars($d['code']); ?>">
        <button class="btn">Starten</button>
      </form>
    </article>
  <?php endforeach; ?>
</section>
