<?php include VIEWS . '/partials/header.php'; ?>
<h1>Choose a Template</h1>
<p class="muted">Pick a design for <strong><?= e($p['full_name']) ?></strong>'s portfolio.</p>
<div class="template-grid">
<?php foreach ([[1, 'Simple', 'Clean and professional single-column layout.'],
                [2, 'Modern', 'Cards, sections and a bold gradient header.'],
                [3, 'Creative', 'Sidebar profile with a timeline-style layout.']] as [$n, $name, $desc]): ?>
  <div class="tcard <?= $p['template'] === $n ? 'active' : '' ?>">
    <div class="frame">
      <iframe src="/portfolio/<?= (int)$p['id'] ?>/preview?t=<?= $n ?>&embed=1" loading="lazy" tabindex="-1"></iframe>
    </div>
    <h3>Template <?= $n ?> – <?= e($name) ?></h3>
    <p class="muted"><?= e($desc) ?></p>
    <div class="actions">
      <a class="btn btn-outline" target="_blank" href="/portfolio/<?= (int)$p['id'] ?>/preview?t=<?= $n ?>">Preview</a>
      <form method="POST" action="/portfolio/<?= (int)$p['id'] ?>/template">
        <input type="hidden" name="template" value="<?= $n ?>">
        <button class="btn"><?= $p['template'] === $n ? 'Selected ✓' : 'Select' ?></button>
      </form>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php include VIEWS . '/partials/footer.php'; ?>
