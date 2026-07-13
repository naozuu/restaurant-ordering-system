document.addEventListener('DOMContentLoaded', function () {
    const confirmDeleteButtons = document.querySelectorAll('[data-confirm-delete]');

    confirmDeleteButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            const message = button.dataset.confirmDelete || 'Are you sure you want to delete this item?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    const quantityInputs = document.querySelectorAll('[data-quantity-input]');

    quantityInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            if (Number(input.value) < 1) {
                input.value = 1;
            }
        });
    });

    const alerts = document.querySelectorAll('.alert[data-auto-close]');

    alerts.forEach(function (alert) {
        window.setTimeout(function () {
            alert.remove();
        }, 4000);
    });
});
