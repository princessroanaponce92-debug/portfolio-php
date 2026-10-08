<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($p['full_name']) ?> | Portfolio</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/t3.css">
</head>
<body>
<?php include VIEWS . '/partials/toolbar.php'; ?>
<div class="layout">
  <aside>
    <?php if ($p['photo']): ?><img src="<?= e($p['photo']) ?>" alt="<?= e($p['full_name']) ?>"><?php endif; ?>
    <h1><?= e($p['full_name']) ?></h1>
    <div class="contact">
      <?php if ($p['email']): ?><p>✉ <?= e($p['email']) ?></p><?php endif; ?>
      <?php if ($p['phone']): ?><p>📞 <?= e($p['phone']) ?></p><?php endif; ?>
      <?php if ($p['address']): ?><p>📍 <?= e($p['address']) ?></p><?php endif; ?>
    </div>
    <?php if ($p['skills']): ?><h3>Skills</h3>
      <ul><?php foreach ($p['skills'] as $s): ?><li><?= e($s['name']) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($p['social_links']): ?><h3>Find me</h3>
      <?php foreach ($p['social_links'] as $l): ?><a href="<?= e($l['url']) ?>" target="_blank">↗ <?= e($l['label']) ?></a><?php endforeach; ?><?php endif; ?>
  </aside>

  <section class="content">
    <?php if ($p['about']): ?><div class="quote"><?= e($p['about']) ?></div><?php endif; ?>

    <?php if ($p['experience']): ?><h2>Experience</h2><div class="timeline">
      <?php foreach ($p['experience'] as $x): ?><div class="t-item"><b><?= e($x['years']) ?></b>
        <h4><?= e($x['position']) ?> @ <?= e($x['company']) ?></h4><p><?= e($x['description']) ?></p></div><?php endforeach; ?></div><?php endif; ?>

    <?php if ($p['education']): ?><h2>Education</h2><div class="timeline">
      <?php foreach ($p['education'] as $x): ?><div class="t-item"><b><?= e($x['years']) ?></b>
        <h4><?= e($x['school']) ?></h4><p><?= e($x['degree']) ?></p></div><?php endforeach; ?></div><?php endif; ?>

    <?php if ($p['projects']): ?><h2>Projects</h2><div class="blocks">
      <?php foreach ($p['projects'] as $i => $x): ?><div class="block b<?= $i % 3 ?>"><h4><?= e($x['title']) ?></h4><p><?= e($x['description']) ?></p>
        <?php if ($x['link']): ?><a href="<?= e($x['link']) ?>" target="_blank">Visit ↗</a><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
  </section>
</div>
</body>
</html>
