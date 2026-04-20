/**
 * AppToast — Custom toast notifications via jQuery
 */
window.AppToast = (function () {

    function show(message, type) {
        var iconMap = {
            'success': '<iconify-icon icon="solar:check-circle-linear" stroke-width="1.5" class="text-xl"></iconify-icon>',
            'error': '<iconify-icon icon="solar:danger-triangle-linear" stroke-width="1.5" class="text-xl"></iconify-icon>',
            'warning': '<iconify-icon icon="solar:info-circle-linear" stroke-width="1.5" class="text-xl"></iconify-icon>',
        };

        var colorMap = {
            'success': 'bg-[#dcfce7] text-[#16a34a] border-[#bbf7d0]',
            'error': 'bg-[#fee2e2] text-[#ef4444] border-[#fecaca]',
            'warning': 'bg-[#fef3c7] text-[#ca8a04] border-[#fde68a]',
        };

        var toast = $(
            '<div class="flex items-center gap-3 p-4 rounded-xl shadow-lg border text-sm max-w-sm ' + (colorMap[type] || colorMap.success) + '">' +
            iconMap[type] || iconMap.success +
            '<span class="flex-1">' + $('<div>').text(message).html() + '</span>' +
            '<button class="text-current/50 hover:text-current" onclick="$(this).parent().fadeOut(200, function(){ $(this).remove(); })">' +
            '<iconify-icon icon="solar:close-circle-linear" stroke-width="1.5" class="text-lg"></iconify-icon>' +
            '</button>' +
            '</div>'
        );

        var container = $('#toast-container');
        if (container.length === 0) {
            $('body').append('<div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>');
            container = $('#toast-container');
        }

        container.append(toast);
        toast.hide().fadeIn(200);

        setTimeout(function () {
            toast.fadeOut(300, function () { $(this).remove(); });
        }, 3500);
    }

    return {
        success: function (msg) { show(msg, 'success'); },
        error:   function (msg) { show(msg, 'error'); },
        warning: function (msg) { show(msg, 'warning'); },
    };

})();
