// Dùng chung cho create.blade.php và edit.blade.php
document.addEventListener('DOMContentLoaded', function() {
    const formatNumber = (num) => num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const cleanNumber  = (str) => str.replace(/\./g, '');

    // Format number inputs on load
    document.querySelectorAll('.format-number').forEach(input => {
        if (input.value) {
            let val = cleanNumber(input.value);
            if (!isNaN(val) && val.length > 0) input.value = formatNumber(val);
        }
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            e.target.value = value !== '' ? formatNumber(value) : '';
        });
    });

    // Clean dots before submit
    document.getElementById('couponForm').addEventListener('submit', function() {
        document.querySelectorAll('.format-number').forEach(input => {
            if (input.value) input.value = cleanNumber(input.value);
        });
        if (document.getElementById('unlimited_quantity').checked) {
            document.getElementById('quantity_input').value = 0;
        }
    });

    // Type toggle
    window.setType = function(type) {
        document.getElementById('typeInput').value = type;
        document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
        event.currentTarget.classList.add('active');
        const valueInput       = document.getElementById('valueInput');
        const valueUnit        = document.getElementById('valueUnit');
        const maxDiscountWrap  = document.getElementById('maxDiscountWrap');

        if (type === 'percent') {
            valueInput.classList.remove('format-number');
            valueInput.value = cleanNumber(valueInput.value);
            valueInput.setAttribute('max', '100');
            valueInput.placeholder = '0 – 100';
            valueUnit.textContent  = '%';
            maxDiscountWrap.style.display = 'block';
        } else {
            valueInput.classList.add('format-number');
            valueInput.removeAttribute('max');
            valueInput.placeholder = '0';
            valueUnit.textContent  = '₫';
            if (valueInput.value) valueInput.value = formatNumber(cleanNumber(valueInput.value));
            maxDiscountWrap.style.display = 'none';
        }
    };

    // Run type toggle on init to set correct state
    const currentType = document.getElementById('typeInput').value;
    const valueInput      = document.getElementById('valueInput');
    const valueUnit       = document.getElementById('valueUnit');
    const maxDiscountWrap = document.getElementById('maxDiscountWrap');
    if (currentType === 'percent') {
        valueInput.classList.remove('format-number');
        valueInput.setAttribute('max', '100');
        valueInput.placeholder = '0 – 100';
        valueUnit.textContent  = '%';
        maxDiscountWrap.style.display = 'block';
    } else {
        maxDiscountWrap.style.display = 'none';
    }

    // Unlimited quantity toggle
    const unlimitedCheckbox = document.getElementById('unlimited_quantity');
    const quantityInput     = document.getElementById('quantity_input');
    function toggleQuantity() {
        if (unlimitedCheckbox.checked) {
            quantityInput.style.display = 'none';
            if (quantityInput.value != 0) quantityInput.setAttribute('data-old-value', quantityInput.value);
            quantityInput.value = 0;
        } else {
            quantityInput.style.display = 'block';
            if (quantityInput.value == 0) quantityInput.value = quantityInput.getAttribute('data-old-value') || 100;
        }
    }
    unlimitedCheckbox.addEventListener('change', toggleQuantity);
    toggleQuantity();

    // Date validation
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput   = document.querySelector('input[name="end_date"]');
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            endDateInput.min = this.value || null;
        });
        endDateInput.addEventListener('change', function() {
            if (startDateInput.value && this.value && this.value <= startDateInput.value) {
                alert('Thời gian kết thúc phải lớn hơn thời gian bắt đầu!');
                this.value = '';
            }
        });
    }

    // Auto-generate code
    window.generateCode = function() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = 'CP';
        for (let i = 0; i < 8; i++) code += chars.charAt(Math.floor(Math.random() * chars.length));
        document.getElementById('codeInput').value = code;
    };
});
