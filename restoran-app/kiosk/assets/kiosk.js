document.addEventListener('DOMContentLoaded', function () {

    const orderTypeInputs = document.querySelectorAll(
        'input[name="jenis_pesanan"]'
    );

    const tableNumberGroup = document.getElementById('tableNumberGroup');
    const tableNumberInput = document.getElementById('nomor_meja');

    function updateTableField() {
        const selected = document.querySelector(
            'input[name="jenis_pesanan"]:checked'
        );

        if (!selected || !tableNumberGroup || !tableNumberInput) {
            return;
        }

        const takeaway = selected.value === 'takeaway';

        tableNumberGroup.hidden = takeaway;
        tableNumberInput.required = !takeaway;

        if (takeaway) {
            tableNumberInput.value = '';
        }
    }

    orderTypeInputs.forEach(function (input) {
        input.addEventListener('change', updateTableField);
    });

    updateTableField();

    const checkoutForm = document.getElementById('checkoutForm');

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function () {
            const button = checkoutForm.querySelector(
                '.submit-order-button'
            );

            if (button) {
                button.disabled = true;
                button.textContent = 'Sending Order...';
            }
        });
    }
});
