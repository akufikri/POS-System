/**
 * AppModal — DaisyUI dialog modal helpers via jQuery
 */
window.AppModal = (function () {

    function getEl(id) {
        return document.getElementById(id);
    }

    return {
        open: function (id) {
            var el = getEl(id);
            if (el) el.showModal();
        },

        close: function (id) {
            var el = getEl(id);
            if (el) el.close();
        },

        /**
         * Populate form fields inside modal from a data object.
         * Maps object keys to input[name="key"] elements.
         */
        populate: function (id, data) {
            var modal = $('#' + id);
            $.each(data, function (key, value) {
                var field = modal.find('[name="' + key + '"]');
                if (field.is(':checkbox')) {
                    field.prop('checked', value === '1' || value === true || value === 1);
                } else {
                    field.val(value || '');
                }
            });
        },

        reset: function (id) {
            var modal = $('#' + id);
            modal.find('form')[0] && modal.find('form')[0].reset();
        },
    };

})();
