/**
 * Currency Input Helper
 * Handles thousands separators for Rupiah (Rp) inputs
 */
window.CurrencyInput = (function () {
    
    function format(val) {
        if (!val && val !== 0) return '';
        var numeric = String(val).replace(/\D/g, '');
        if (numeric === '') return '';
        return parseInt(numeric).toLocaleString('id-ID');
    }

    function unformat(str) {
        if (!str) return 0;
        return parseInt(String(str).replace(/\./g, '')) || 0;
    }

    // Apply formatting to an input element
    function apply(input) {
        var $el = $(input);
        var formatted = format($el.val());
        $el.val(formatted);
    }

    // Initialize listeners
    $(document).on('input', '.input-currency', function () {
        apply(this);
    });

    return {
        format: format,
        unformat: unformat,
        apply: apply,
        init: function(selector) {
            $(selector || '.input-currency').each(function() {
                apply(this);
            });
        }
    };
})();
