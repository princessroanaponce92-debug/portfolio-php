
<?php
include VIEWS . '/partials/header.php';

$e = $p ?: [];
$rowsOf = fn($a) => !empty($a) ? $a : [[]];
$v = fn($row, $k) => e($row[$k] ?? '');
?>

<div class="form-page">
    <div class="form-top">
        <a class="btn btn-outline" href="<?= $p ? '/manage' : '/' ?>">
            ← Back
        </a>
    </div>

    <h1><?= $p ? 'Edit Portfolio' : 'Create Portfolio' ?></h1>
    <p class="muted">Fields marked with * are required.</p>

    <form class="card-box"
          method="POST"
          enctype="multipart/form-data"
          action="<?= $p ? '/portfolio/' . (int)$p['id'] . '/update' : '/portfolio/save' ?>">

        <input type="hidden" name="csrf_token"
               value="<?= e(csrf_token()) ?>">

        <h2>Personal Information</h2>

        <div class="grid-2">
            <div>
                <label for="full_name">Full Name *</label>
                <input
                    id="full_name"
                    name="full_name"
                    type="text"
                    required
                    maxlength="255"
                    pattern="[\p{L}\p{M} .'-]+"
                    title="Enter your name using letters only, with spaces, apostrophes, periods, or hyphens if needed."
                    autocomplete="name"
                    value="<?= $v($e, 'full_name') ?>"
                >
                <small class="field-error" id="nameError"></small>
            </div>

            <div>
                <label for="email">Email *</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    maxlength="255"
                    autocomplete="email"
                    value="<?= $v($e, 'email') ?>"
                >
            </div>

            <div>
                <label for="phone">Contact Number *</label>
                <input
                    id="phone"
                    name="phone"
                    type="tel"
                    required
                    maxlength="30"
                    autocomplete="tel"
                    value="<?= $v($e, 'phone') ?>"
                >
            </div>

            <div>
                <label for="address">Address *</label>
                <input
                    id="address"
                    name="address"
                    type="text"
                    required
                    maxlength="255"
                    autocomplete="street-address"
                    value="<?= $v($e, 'address') ?>"
                >
            </div>
        </div>

        <label for="photo">Profile Picture (JPG, PNG or WEBP, max <?= MAX_MB ?>MB)</label>
        <input
            type="file"
            id="photo"
            name="photo"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            data-max-mb="<?= MAX_MB ?>"
        >

        <div class="photo-box">
            <img
                id="photoPreview"
                class="thumb"
                alt="Profile preview"
                src="<?= $v($e, 'photo') ?>"
                style="<?= !empty($e['photo']) ? '' : 'display:none' ?>"
            >

            <button
                type="button"
                id="removePhotoBtn"
                class="btn btn-danger btn-sm"
                style="<?= !empty($e['photo']) ? '' : 'display:none' ?>"
            >Remove photo</button>
        </div>

        <input type="hidden" name="remove_photo" id="removePhoto" value="0">
        <p class="muted" id="photoMsg"></p>

        <label for="about">About Me *</label>
        <textarea
            id="about"
            name="about"
            rows="4"
            required
            maxlength="5000"
        ><?= $v($e, 'about') ?></textarea>

        <h2>Education</h2>
        <div id="edu">
            <?php foreach ($rowsOf($e['education'] ?? []) as $x): ?>
                <div class="row-item">
                    <input name="edu_school[]" placeholder="School / University"
                           value="<?= $v($x, 'school') ?>">
                    <input name="edu_degree[]" placeholder="Degree / Course"
                           value="<?= $v($x, 'degree') ?>">
                    <input name="edu_years[]" placeholder="Years (2022 - 2026)"
                           value="<?= $v($x, 'years') ?>">
                    <button type="button" class="btn-x"
                            onclick="removeRow(this)" aria-label="Remove education">✕</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-outline" onclick="addRow('edu')">
            + Add Education
        </button>

        <h2>Skills *</h2>
        <input
            name="skills"
            type="text"
            required
            maxlength="2000"
            placeholder="Separate with commas: HTML, CSS, JavaScript, MySQL"
            value="<?= e(implode(', ', array_column($e['skills'] ?? [], 'name'))) ?>"
        >

        <h2>Projects <span class="muted">(Optional)</span></h2>
        <div id="proj">
            <?php foreach ($rowsOf($e['projects'] ?? []) as $x): ?>
                <div class="row-item">
                    <input name="proj_title[]" placeholder="Project title"
                           value="<?= $v($x, 'title') ?>">
                    <textarea name="proj_desc[]" rows="2"
                              placeholder="Short description"><?= $v($x, 'description') ?></textarea>
                    <input name="proj_link[]" placeholder="Project link (optional)"
                           value="<?= $v($x, 'link') ?>">
                    <button type="button" class="btn-x"
                            onclick="removeRow(this)" aria-label="Remove project">✕</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-outline" onclick="addRow('proj')">
            + Add Project
        </button>

        <h2>Work Experience <span class="muted">(Optional)</span></h2>
        <div id="exp">
            <?php foreach ($rowsOf($e['experience'] ?? []) as $x): ?>
                <div class="row-item">
                    <input name="exp_company[]" placeholder="Company"
                           value="<?= $v($x, 'company') ?>">
                    <input name="exp_position[]" placeholder="Position"
                           value="<?= $v($x, 'position') ?>">
                    <input name="exp_years[]" placeholder="Years"
                           value="<?= $v($x, 'years') ?>">
                    <textarea name="exp_desc[]" rows="2"
                              placeholder="What you did"><?= $v($x, 'description') ?></textarea>
                    <button type="button" class="btn-x"
                            onclick="removeRow(this)" aria-label="Remove experience">✕</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-outline" onclick="addRow('exp')">
            + Add Experience
        </button>

        <h2>Social Media / Website Links</h2>
        <div id="links">
            <?php foreach ($rowsOf($e['social_links'] ?? []) as $x): ?>
                <div class="row-item">
                    <input name="link_label[]" placeholder="Label (LinkedIn, GitHub...)"
                           value="<?= $v($x, 'label') ?>">
                    <input name="link_url[]" type="url" placeholder="https://..."
                           value="<?= $v($x, 'url') ?>">
                    <button type="button" class="btn-x"
                            onclick="removeRow(this)" aria-label="Remove link">✕</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-outline" onclick="addRow('links')">
            + Add Link
        </button>

        <div class="form-actions">
            <button type="submit" class="btn btn-big">
                <?= $p ? 'Save Changes' : 'Save & Choose Template' ?>
            </button>

            <a class="btn btn-outline btn-big"
               href="<?= $p ? '/manage' : '/' ?>">Cancel</a>
        </div>
    </form>
</div>

<style>
.form-top {
    margin-bottom: 16px;
}

.field-error {
    display: block;
    color: #b42318;
    margin-top: 5px;
}

input:invalid:not(:focus):not(:placeholder-shown),
textarea:invalid:not(:focus):not(:placeholder-shown) {
    border-color: #d92d20;
}

.form-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 24px;
}

@media (max-width: 600px) {
    .form-actions > * {
        flex: 1 1 100%;
        text-align: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('full_name');
    const nameError = document.getElementById('nameError');

    if (nameInput) {
        nameInput.addEventListener('input', function () {
            // Remove digits and other characters not allowed in a name.
            const cleaned = this.value.replace(/[^\p{L}\p{M} .'-]/gu, '');

            if (this.value !== cleaned) {
                this.value = cleaned;
            }

            nameError.textContent = '';
        });

        nameInput.addEventListener('invalid', function () {
            nameError.textContent = 'Please enter a valid full name using letters only.';
        });
    }

    const form = document.querySelector('form.card-box');

    if (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
            }
        });
    }
});
</script>

<script src="/js/form.js"></script>

<?php include VIEWS . '/partials/footer.php'; ?>
