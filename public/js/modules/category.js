/**
 * Category module
 */
$(function () {

    var isEditing = false;

    // ─── Open modal for create ──────────────────────────────────────────────────
    $(document).on('click', 'button[onclick*="AppModal.open(\'modal-category\')"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        isEditing = false;
        $('#modal-category-title').text('Add Category');
        $('#category-id-field').val('');
        $('#category-name').val('');
        $('#category-sort').val(0);
        AppModal.open('modal-category');
    });

    // ─── Edit category ───────────────────────────────────────────────────────────
    $(document).on('click', '[onclick*="AppCategories.editCategory"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        isEditing = true;
        var $btn = $(this);

        $('#modal-category-title').text('Edit Category');
        $('#category-id-field').val($btn.data('id'));
        $('#category-name').val($btn.data('name'));
        $('#category-sort').val($btn.data('sort'));

        AppModal.open('modal-category');
    });

    // ─── Delete category ─────────────────────────────────────────────────────────
    $(document).on('click', '[onclick*="AppCategories.deleteCategory"]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var categoryId = $btn.data('id');
        var categoryName = $btn.data('name');

        $('#delete-category-name').text(categoryName);
        $('#btn-confirm-delete-category').data('id', categoryId);

        AppModal.open('modal-delete-category');
    });

    // ─── Confirm delete category ─────────────────────────────────────────────────────
    $('#btn-confirm-delete-category').on('click', function () {
        var categoryId = $(this).data('id');
        var $btn = $(this);
        $btn.prop('disabled', true).text('Deleting...');

        AppAjax.delete('/categories/' + categoryId, function (data) {
            AppToast.success('Category deleted successfully');
            AppModal.close('modal-delete-category');
            location.reload();
        }, function (xhr) {
            $btn.prop('disabled', false).text('Delete');
            AppToast.error('Failed to delete category');
        });
    });

    // ─── Save category (create/update) ────────────────────────────────────────────
    $('#form-category').on('submit', function (e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('name', $('#category-name').val().trim());
        formData.append('sort_order', $('#category-sort').val() || 0);

        if (!$('#category-name').val().trim()) {
            AppToast.error('Category name is required');
            return;
        }

        var url = isEditing
            ? '/categories/' + $('#category-id-field').val()
            : '/categories';

        var method = isEditing ? 'put' : 'post';

        AppAjax[method](url, formData, function (data) {
            if (data.success) {
                AppToast.success(isEditing ? 'Category updated successfully' : 'Category created successfully');
                AppModal.close('modal-category');
                location.reload();
            }
        }, function (xhr) {
            AppToast.error('Failed to save category');
    });
});
