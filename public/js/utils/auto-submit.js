/**
 * Auto-submit Utility
 * Implements debounced real-time submission for search inputs
 */
$(function () {
    var searchTimeout = null;

    $(document).on('input', '.js-auto-submit', function () {
        var $input = $(this);
        var $form = $input.closest('form');

        // Clear previous timeout
        clearTimeout(searchTimeout);

        // Set new timeout (debounce 500ms)
        searchTimeout = setTimeout(function () {
            $form.submit();
        }, 500);
    });

    // Handle cursor position (keep cursor at the end after refresh)
    $('.js-auto-submit').each(function() {
        if (this.value.length > 0) {
            this.focus();
            this.setSelectionRange(this.value.length, this.value.length);
        }
    });
});
