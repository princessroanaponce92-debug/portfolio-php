<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($p['full_name']) ?> | Portfolio</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/t2.css">
</head>
<body>
<?php include VIEWS . '/partials/toolbar.php'; ?>
<header class="hero">
  <?php if ($p['photo']): ?><img src="<?= e($p['photo']) ?>" alt="<?= e($p['full_name']) ?>"><?php endif; ?>
  <h1><?= e($p['full_name']) ?></h1>
  <p><?= e($p['about']) ?></p>
  <div class="chips">
    <?php if ($p['email']): ?><span>✉ <?= e($p['email']) ?></span><?php endif; ?>
    <?php if ($p['phone']): ?><span>📞 <?= e($p['phone']) ?></span><?php endif; ?>
    <?php if ($p['address']): ?><span>📍 <?= e($p['address']) ?></span><?php endif; ?>
  </div>
</header>

<main class="grid">
  <?php if ($p['skills']): ?><section class="card full"><h2>Skills</h2>
    <div class="tags"><?php foreach ($p['skills'] as $s): ?><span><?= e($s['name']) ?></span><?php endforeach; ?></div></section><?php endif; ?>

  <?php if ($p['education']): ?><section class="card"><h2>🎓 Education</h2>
    <?php foreach ($p['education'] as $x): ?><div class="mini"><h3><?= e($x['school']) ?></h3><p><?= e($x['degree']) ?></p><small><?= e($x['years']) ?></small></div><?php endforeach; ?></section><?php endif; ?>

  <?php if ($p['experience']): ?><section class="card"><h2>💼 Experience</h2>
    <?php foreach ($p['experience'] as $x): ?><div class="mini"><h3><?= e($x['position']) ?></h3><p><?= e($x['company']) ?> · <?= e($x['years']) ?></p><small><?= e($x['description']) ?></small></div><?php endforeach; ?></section><?php endif; ?>

  <?php if ($p['projects']): ?><section class="full"><h2 class="big">🚀 Projects</h2>
    <div class="cards"><?php foreach ($p['projects'] as $x): ?>
      <article class="card"><h3><?= e($x['title']) ?></h3><p><?= e($x['description']) ?></p>
      <?php if ($x['link']): ?><a href="<?= e($x['link']) ?>" target="_blank">Open project →</a><?php endif; ?></article>
    <?php endforeach; ?></div></section><?php endif; ?>

  <?php if ($p['social_links']): ?><section class="card full center"><h2>Let's connect</h2>
    <?php foreach ($p['social_links'] as $l): ?><a class="pill" href="<?= e($l['url']) ?>" target="_blank"><?= e($l['label']) ?></a><?php endforeach; ?></section><?php endif; ?>
</main>
</body>
</html>
