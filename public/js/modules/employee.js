/**
 * Employee Management Module
 */
window.AppEmployees = {
    editEmployee: function (btn) {
        var $btn = $(btn);
        var id = $btn.data('id');
        var name = $btn.data('name');
        var email = $btn.data('email');

        $('#modal-employee-title').text('Edit Employee');
        $('#employee-id-field').val(id);
        $('#employee-name').val(name);
        $('#employee-email').val(email);
        $('#employee-password').val('');
        $('#password-hint').text('Leave blank to keep existing password.');

        AppModal.open('modal-employee');
    },

    suspendEmployee: function (btn) {
        var $btn = $(btn);
        var id = $btn.data('id');
        var reason = $btn.data('reason');

        $('#suspend-user-id').val(id);
        $('#suspend-reason').val(reason || '');
        AppModal.open('modal-suspend');
    },

    deleteEmployee: function (btn) {
        var $btn = $(btn);
        var id = $btn.data('id');
        var name = $btn.data('name');

        $('#delete-employee-name').text(name);
        $('#btn-confirm-delete-employee').off('click').on('click', function () {
            AppAjax.delete('/employees/' + id, function (res) {
                AppModal.close('modal-delete-employee');
                AppToast.success(res.message);
                location.reload();
            });
        });

        AppModal.open('modal-delete-employee');
    }
};

$(function () {
    // Add Button Reset
    $('[onclick*="modal-employee"]').on('click', function() {
        if ($(this).hasClass('bg-[#edcc94]')) { // Only for "Add" button
            $('#modal-employee-title').text('Add Employee');
            $('#employee-id-field').val('');
            $('#employee-name').val('');
            $('#employee-email').val('');
            $('#employee-password').val('');
            $('#password-hint').text('Min. 6 characters.');
        }
    });

    // Form Submit (Create/Update)
    $('#form-employee').on('submit', function (e) {
        e.preventDefault();
        var id = $('#employee-id-field').val();
        var url = id ? '/employees/' + id : '/employees';
        var method = id ? 'PUT' : 'POST';

        AppAjax.send(url, method, $(this).serialize(), function (res) {
            AppModal.close('modal-employee');
            AppToast.success(res.message);
            location.reload();
        }, function (xhr) {
            AppToast.error(xhr.responseJSON?.message || 'Gagal menyimpan data.');
        });
    });

    // Form Suspend
    $('#form-suspend').on('submit', function (e) {
        e.preventDefault();
        var id = $('#suspend-user-id').val();
        
        AppAjax.send('/employees/' + id + '/suspend', 'PATCH', $(this).serialize(), function (res) {
            AppModal.close('modal-suspend');
            AppToast.success(res.message);
            location.reload();
        });
    });
});
