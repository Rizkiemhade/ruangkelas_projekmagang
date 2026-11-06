document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('[data-profile-form]');
    const photoInput = document.querySelector('[data-profile-photo-input]');
    const preview = document.querySelector('[data-profile-photo-preview]');

    if (!form || !photoInput || !preview) {
        return;
    }

    const cropControls = document.querySelector('[data-photo-crop]');
    const zoomControl = document.querySelector('[data-photo-zoom]');
    const resetButton = document.querySelector('[data-photo-reset]');
    const buttonLabel = document.querySelector('[data-photo-button-label]');
    const errorMessage = document.querySelector('[data-photo-error]');
    const submitButton = document.querySelector('[data-profile-save]');
    const defaultPreview = preview.dataset.defaultSrc;
    let selectedPhotoUrl = null;
    let isPreparingPhoto = false;
    let croppedPhotoReady = false;

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.hidden = false;
    }

    function clearError() {
        errorMessage.textContent = '';
        errorMessage.hidden = true;
    }

    function resetPhotoSelection() {
        if (selectedPhotoUrl) {
            URL.revokeObjectURL(selectedPhotoUrl);
            selectedPhotoUrl = null;
        }

        photoInput.value = '';
        preview.src = defaultPreview;
        preview.style.transform = 'scale(1)';
        zoomControl.value = '1';
        cropControls.hidden = true;
        resetButton.hidden = true;
        buttonLabel.textContent = 'Ganti foto';
        clearError();
    }

    photoInput.addEventListener('change', function () {
        const file = photoInput.files[0];

        if (!file) {
            return;
        }

        clearError();

        if (file.size > 2 * 1024 * 1024) {
            photoInput.value = '';
            showError('Ukuran foto maksimal 2 MB. Silakan pilih foto lain.');

            return;
        }

        const photoUrl = URL.createObjectURL(file);
        const candidate = new Image();

        candidate.onload = function () {
            if (candidate.naturalWidth > 3000 || candidate.naturalHeight > 3000) {
                URL.revokeObjectURL(photoUrl);
                photoInput.value = '';
                showError('Dimensi foto maksimal 3000 × 3000 piksel. Silakan pilih foto lain.');

                return;
            }

            if (selectedPhotoUrl) {
                URL.revokeObjectURL(selectedPhotoUrl);
            }

            selectedPhotoUrl = photoUrl;
            preview.src = photoUrl;
            preview.style.transform = 'scale(1)';
            zoomControl.value = '1';
            cropControls.hidden = false;
            resetButton.hidden = false;
            buttonLabel.textContent = 'Pilih foto lain';
        };

        candidate.onerror = function () {
            URL.revokeObjectURL(photoUrl);
            photoInput.value = '';
            showError('Foto tidak dapat dibuka. Silakan pilih file gambar yang valid.');
        };

        candidate.src = photoUrl;
    });

    zoomControl.addEventListener('input', function () {
        preview.style.transform = `scale(${zoomControl.value})`;
    });

    resetButton.addEventListener('click', resetPhotoSelection);

    form.addEventListener('submit', function (event) {
        if (!selectedPhotoUrl || croppedPhotoReady) {
            return;
        }

        event.preventDefault();

        if (isPreparingPhoto) {
            return;
        }

        isPreparingPhoto = true;
        clearError();
        submitButton.disabled = true;

        const source = new Image();

        source.onload = function () {
            const cropSize = Math.min(source.naturalWidth, source.naturalHeight) / Number(zoomControl.value);
            const cropX = (source.naturalWidth - cropSize) / 2;
            const cropY = (source.naturalHeight - cropSize) / 2;
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            if (!context) {
                isPreparingPhoto = false;
                submitButton.disabled = false;
                showError('Foto tidak dapat diproses di browser ini. Silakan coba browser lain.');

                return;
            }

            canvas.width = 512;
            canvas.height = 512;
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            context.drawImage(
                source,
                cropX,
                cropY,
                cropSize,
                cropSize,
                0,
                0,
                canvas.width,
                canvas.height
            );

            canvas.toBlob(function (blob) {
                if (!blob) {
                    isPreparingPhoto = false;
                    submitButton.disabled = false;
                    showError('Foto gagal diproses. Silakan coba lagi.');

                    return;
                }

                const transfer = new DataTransfer();
                transfer.items.add(new File([blob], 'profile-photo.jpg', { type: 'image/jpeg' }));
                photoInput.files = transfer.files;
                croppedPhotoReady = true;
                form.requestSubmit();
            }, 'image/jpeg', 0.9);
        };

        source.onerror = function () {
            isPreparingPhoto = false;
            submitButton.disabled = false;
            showError('Foto tidak dapat diproses. Silakan pilih foto lain.');
        };

        source.src = selectedPhotoUrl;
    });
});
