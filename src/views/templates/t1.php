<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($p['full_name']) ?> | Portfolio</title>
  <link rel="stylesheet" href="/css/t1.css">
</head>
<body>
<?php include VIEWS . '/partials/toolbar.php'; ?>
<div class="wrap">
  <header>
    <?php if ($p['photo']): ?><img src="<?= e($p['photo']) ?>" alt="<?= e($p['full_name']) ?>"><?php endif; ?>
    <h1><?= e($p['full_name']) ?></h1>
    <p class="contact"><?= e(implode('  •  ', array_filter([$p['email'], $p['phone'], $p['address']]))) ?></p>
  </header>

  <?php if ($p['about']): ?><section><h2>About Me</h2><p><?= e($p['about']) ?></p></section><?php endif; ?>

  <?php if ($p['education']): ?><section><h2>Education</h2>
    <?php foreach ($p['education'] as $x): ?>
      <div class="item"><strong><?= e($x['school']) ?></strong><span><?= e($x['degree']) ?> <em><?= e($x['years']) ?></em></span></div>
    <?php endforeach; ?></section><?php endif; ?>

  <?php if ($p['skills']): ?><section><h2>Skills</h2>
    <ul class="tags"><?php foreach ($p['skills'] as $s): ?><li><?= e($s['name']) ?></li><?php endforeach; ?></ul></section><?php endif; ?>

  <?php if ($p['projects']): ?><section><h2>Projects</h2>
    <?php foreach ($p['projects'] as $x): ?>
      <div class="item"><strong><?= e($x['title']) ?></strong><span><?= e($x['description']) ?></span>
      <?php if ($x['link']): ?><a href="<?= e($x['link']) ?>" target="_blank">View project →</a><?php endif; ?></div>
    <?php endforeach; ?></section><?php endif; ?>

  <?php if ($p['experience']): ?><section><h2>Work Experience</h2>
    <?php foreach ($p['experience'] as $x): ?>
      <div class="item"><strong><?= e($x['position']) ?> – <?= e($x['company']) ?></strong>
      <em><?= e($x['years']) ?></em><span><?= e($x['description']) ?></span></div>
    <?php endforeach; ?></section><?php endif; ?>

  <?php if ($p['social_links']): ?><section><h2>Connect</h2>
    <p class="links"><?php foreach ($p['social_links'] as $l): ?><a href="<?= e($l['url']) ?>" target="_blank"><?= e($l['label']) ?></a><?php endforeach; ?></p></section><?php endif; ?>
</div>
</body>
</html>
