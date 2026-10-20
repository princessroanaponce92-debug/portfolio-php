
/*
|--------------------------------------------------------------------------
| ADD AND REMOVE PORTFOLIO ROWS
|--------------------------------------------------------------------------
*/

function addRow(id) {
    const box = document.getElementById(id);

    if (!box || !box.firstElementChild) {
        console.error('Cannot find a row to copy for:', id);
        return;
    }

    const row = box.firstElementChild.cloneNode(true);

    row.querySelectorAll('input, textarea').forEach((field) => {
        field.value = '';
        field.classList.remove('field-error');
        field.removeAttribute('aria-invalid');
    });

    box.appendChild(row);
}

function removeRow(btn) {
    const row = btn.closest('.row-item');

    if (!row) return;

    const box = row.parentElement;

    if (box.children.length > 1) {
        row.remove();
    } else {
        row.querySelectorAll('input, textarea').forEach((field) => {
            field.value = '';
            field.classList.remove('field-error');
            field.removeAttribute('aria-invalid');
        });
    }
}


/*
|--------------------------------------------------------------------------
| FORM VALIDATION
|--------------------------------------------------------------------------
*/

(function () {
    const form = document.querySelector('form.card-box');

    if (!form) return;

    const fullName = form.querySelector('[name="full_name"]');

    // These fields are required by the current PHP backend.
    const requiredFields = [
        { name: 'full_name', label: 'Full name' },
        { name: 'email', label: 'Email' },
        { name: 'phone', label: 'Contact number' },
        { name: 'address', label: 'Address' },
        { name: 'about', label: 'About Me' },
        { name: 'skills', label: 'Skills' }
    ];

    // Prevent numbers and unsupported characters in the full name.
    if (fullName) {
        fullName.addEventListener('input', function () {
            const cleaned = this.value.replace(
                /[^\p{L}\p{M} .'-]/gu,
                ''
            );

            if (this.value !== cleaned) {
                this.value = cleaned;
            }

            this.classList.remove('field-error');
            this.removeAttribute('aria-invalid');
        });
    }

    // Remove the red error highlight when a field is corrected.
    form.querySelectorAll('input, textarea').forEach((field) => {
        field.addEventListener('input', function () {
            if (this.value.trim() !== '') {
                this.classList.remove('field-error');
                this.removeAttribute('aria-invalid');
            }
        });
    });

    form.addEventListener('submit', function (event) {
        let firstInvalid = null;

        requiredFields.forEach(({ name, label }) => {
            const field = form.querySelector(`[name="${name}"]`);

            if (!field) return;

            const value = field.value.trim();

            let message = '';

            if (value === '') {
                message = label + ' is required.';
            } else if (
                name === 'full_name' &&
                !/^[\p{L}\p{M} .'-]+$/u.test(value)
            ) {
                message = 'Full name must contain letters only.';
            } else if (
                name === 'email' &&
                !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
            ) {
                message = 'Please enter a valid email address.';
            }

            if (message) {
                field.classList.add('field-error');
                field.setAttribute('aria-invalid', 'true');
                field.setCustomValidity(message);

                if (!firstInvalid) {
                    firstInvalid = field;
                }
            } else {
                field.classList.remove('field-error');
                field.removeAttribute('aria-invalid');
                field.setCustomValidity('');
            }
        });

        if (firstInvalid) {
            event.preventDefault();
            firstInvalid.reportValidity();
            firstInvalid.focus();
        }
    });
})();


/*
|--------------------------------------------------------------------------
| PHOTO PREVIEW, COMPRESSION AND REMOVAL
|--------------------------------------------------------------------------
*/

(function () {
    const input = document.getElementById('photo');

    if (!input) return;

    const MAX_MB = Number(input.dataset.maxMb) || 5;
    const MAX_BYTES = MAX_MB * 1024 * 1024;

    const preview = document.getElementById('photoPreview');
    const removeBtn = document.getElementById('removePhotoBtn');
    const removeFlag = document.getElementById('removePhoto');
    const message = document.getElementById('photoMsg');

    if (!preview || !removeBtn || !removeFlag || !message) {
        return;
    }

    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    let previewObjectUrl = null;

    function showMessage(text) {
        message.textContent = text;
    }

    function showPreview(src) {
        preview.src = src;
        preview.style.display = 'block';
        removeBtn.style.display = 'inline-block';
    }

    function hidePreview() {
        preview.removeAttribute('src');
        preview.style.display = 'none';
        removeBtn.style.display = 'none';
    }

    function clearObjectUrl() {
        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }
    }

    function resetPreviewToSavedPhoto() {
        clearObjectUrl();

        // Restore the original saved photo if one exists.
        const savedPhoto = preview.dataset.savedPhoto || '';

        if (savedPhoto) {
            showPreview(savedPhoto);
        } else {
            hidePreview();
        }
    }

    /*
     * Compress an image to JPEG.
     * If compression fails, the original image can still be previewed.
     */
    function shrink(file) {
        return new Promise((resolve) => {
            const image = new Image();
            const sourceUrl = URL.createObjectURL(file);

            image.onload = function () {
                const maxDimension = 1200;

                const scale = Math.min(
                    1,
                    maxDimension / Math.max(image.width, image.height)
                );

                const canvas = document.createElement('canvas');

                canvas.width = Math.max(
                    1,
                    Math.round(image.width * scale)
                );

                canvas.height = Math.max(
                    1,
                    Math.round(image.height * scale)
                );

                const context = canvas.getContext('2d');

                if (!context) {
                    URL.revokeObjectURL(sourceUrl);
                    resolve(null);
                    return;
                }

                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);

                context.drawImage(
                    image,
                    0,
                    0,
                    canvas.width,
                    canvas.height
                );

                URL.revokeObjectURL(sourceUrl);

                canvas.toBlob(
                    function (blob) {
                        resolve(blob);
                    },
                    'image/jpeg',
                    0.85
                );
            };

            image.onerror = function () {
                URL.revokeObjectURL(sourceUrl);
                resolve(null);
            };

            image.src = sourceUrl;
        });
    }

    /*
     * Validate and preview the selected photo.
     */
    input.addEventListener('change', async function () {
        const file = input.files && input.files[0];

        showMessage('');

        if (!file) return;

        const extensionAllowed = /\.(jpe?g|png|webp)$/i.test(file.name);

        if (
            !extensionAllowed ||
            !allowedTypes.includes(file.type)
        ) {
            showMessage(
                'Please choose a JPG, PNG or WEBP image.'
            );

            input.value = '';
            return;
        }

        if (file.size > MAX_BYTES) {
            showMessage(
                'That photo is ' +
                (file.size / 1048576).toFixed(1) +
                'MB. The maximum is ' +
                MAX_MB +
                'MB. Please choose a smaller photo.'
            );

            input.value = '';
            return;
        }

        removeFlag.value = '0';

        const blob = await shrink(file);

        // Do not use a result if the user changed the selection meanwhile.
        if (!input.files || !input.files[0]) return;

        let fileToPreview = file;

        if (blob) {
            try {
                const dataTransfer = new DataTransfer();

                dataTransfer.items.add(
                    new File(
                        [blob],
                        'photo.jpg',
                        { type: 'image/jpeg' }
                    )
                );

                input.files = dataTransfer.files;
                fileToPreview = input.files[0];
            } catch (error) {
                // Keep the original selected image if replacement is unsupported.
            }
        }

        clearObjectUrl();

        previewObjectUrl = URL.createObjectURL(fileToPreview);
        showPreview(previewObjectUrl);
        showMessage('');
    });

    
    removeBtn.addEventListener('click', function () {
        input.value = '';
        clearObjectUrl();
        hidePreview();

        removeFlag.value = '1';

        showMessage(
            'The photo will be removed when you save.'
        );
    });

    
    if (preview.getAttribute('src')) {
        preview.dataset.savedPhoto = preview.getAttribute('src');
    }
})();
