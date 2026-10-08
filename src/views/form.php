<?php
include VIEWS . '/partials/header.php';
$e = $p ?: [];
$rowsOf = fn($a) => !empty($a) ? $a : [[]];
$v = fn($row, $k) => e($row[$k] ?? '');
?>
<h1><?= $p ? 'Edit Portfolio' : 'Create Portfolio' ?></h1>

<form class="card-box" method="POST" enctype="multipart/form-data"
      action="<?= $p ? '/portfolio/' . (int)$p['id'] . '/update' : '/portfolio/save' ?>">

  <h2>Personal Information</h2>
  <div class="grid-2">
    <div><label>Full Name *</label><input name="full_name" required value="<?= $v($e, 'full_name') ?>"></div>
    <div><label>Email *</label><input type="email" name="email" required value="<?= $v($e, 'email') ?>"></div>
    <div><label>Contact Number</label><input name="phone" value="<?= $v($e, 'phone') ?>"></div>
    <div><label>Address</label><input name="address" value="<?= $v($e, 'address') ?>"></div>
  </div>

  <label>Profile Picture (JPG, PNG or WEBP, max <?= MAX_MB ?>MB)</label>
  <input type="file" id="photo" name="photo" accept="image/*" data-max-mb="<?= MAX_MB ?>">
  <div class="photo-box">
    <img id="photoPreview" class="thumb" alt="Profile preview"
         src="<?= $v($e, 'photo') ?>" style="<?= !empty($e['photo']) ? '' : 'display:none' ?>">
    <button type="button" id="removePhotoBtn" class="btn btn-danger btn-sm"
            style="<?= !empty($e['photo']) ? '' : 'display:none' ?>">Remove photo</button>
  </div>
  <input type="hidden" name="remove_photo" id="removePhoto" value="0">
  <p class="muted" id="photoMsg"></p>

  <label>About Me</label>
  <textarea name="about" rows="4"><?= $v($e, 'about') ?></textarea>

  <h2>Education</h2>
  <div id="edu">
    <?php foreach ($rowsOf($e['education'] ?? []) as $x): ?>
    <div class="row-item">
      <input name="edu_school[]" placeholder="School / University" value="<?= $v($x, 'school') ?>">
      <input name="edu_degree[]" placeholder="Degree / Course" value="<?= $v($x, 'degree') ?>">
      <input name="edu_years[]" placeholder="Years (2022 - 2026)" value="<?= $v($x, 'years') ?>">
      <button type="button" class="btn-x" onclick="removeRow(this)">✕</button>
    </div>
    <?php endforeach; ?>
  </div>
  <button type="button" class="btn btn-outline" onclick="addRow('edu')">+ Add Education</button>

  <h2>Skills</h2>
  <input name="skills" placeholder="Separate with commas: HTML, CSS, JavaScript, MySQL"
         value="<?= e(implode(', ', array_column($e['skills'] ?? [], 'name'))) ?>">

  <h2>Projects</h2>
  <div id="proj">
    <?php foreach ($rowsOf($e['projects'] ?? []) as $x): ?>
    <div class="row-item">
      <input name="proj_title[]" placeholder="Project title" value="<?= $v($x, 'title') ?>">
      <textarea name="proj_desc[]" rows="2" placeholder="Short description"><?= $v($x, 'description') ?></textarea>
      <input name="proj_link[]" placeholder="Project link (optional)" value="<?= $v($x, 'link') ?>">
      <button type="button" class="btn-x" onclick="removeRow(this)">✕</button>
    </div>
    <?php endforeach; ?>
  </div>
  <button type="button" class="btn btn-outline" onclick="addRow('proj')">+ Add Project</button>

  <h2>Work Experience</h2>
  <div id="exp">
    <?php foreach ($rowsOf($e['experience'] ?? []) as $x): ?>
    <div class="row-item">
      <input name="exp_company[]" placeholder="Company" value="<?= $v($x, 'company') ?>">
      <input name="exp_position[]" placeholder="Position" value="<?= $v($x, 'position') ?>">
      <input name="exp_years[]" placeholder="Years" value="<?= $v($x, 'years') ?>">
      <textarea name="exp_desc[]" rows="2" placeholder="What you did"><?= $v($x, 'description') ?></textarea>
      <button type="button" class="btn-x" onclick="removeRow(this)">✕</button>
    </div>
    <?php endforeach; ?>
  </div>
  <button type="button" class="btn btn-outline" onclick="addRow('exp')">+ Add Experience</button>

  <h2>Social Media / Website Links</h2>
  <div id="links">
    <?php foreach ($rowsOf($e['social_links'] ?? []) as $x): ?>
    <div class="row-item">
      <input name="link_label[]" placeholder="Label (LinkedIn, GitHub...)" value="<?= $v($x, 'label') ?>">
      <input name="link_url[]" placeholder="https://..." value="<?= $v($x, 'url') ?>">
      <button type="button" class="btn-x" onclick="removeRow(this)">✕</button>
    </div>
    <?php endforeach; ?>
  </div>
  <button type="button" class="btn btn-outline" onclick="addRow('links')">+ Add Link</button>

  <div class="form-actions">
    <button class="btn btn-big"><?= $p ? 'Save Changes' : 'Save &amp; Choose Template' ?></button>
    <a class="btn btn-outline btn-big" href="<?= $p ? '/manage' : '/' ?>">Cancel</a>
  </div>
</form>
<script src="/js/form.js"></script>
<?php include VIEWS . '/partials/footer.php'; ?>
