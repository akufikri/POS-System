/**
 * AppAjax — thin jQuery $.ajax wrapper with consistent error handling
 */
window.AppAjax = (function () {

    function defaultError(xhr) {
        var message = 'Terjadi kesalahan. Coba lagi.';

        if (xhr.responseJSON) {
            message = xhr.responseJSON.message || message;

            // Laravel validation errors (422)
            if (xhr.status === 422 && xhr.responseJSON.errors) {
                var errors = xhr.responseJSON.errors;
                var firstKey = Object.keys(errors)[0];
                message = errors[firstKey][0];
            }
        }

        AppToast.error(message);
    }

    return {
        send: function (url, method, data, onSuccess, onError) {
            var actualMethod = method.toUpperCase();
            var useMethodSpoofing = ['PUT', 'PATCH', 'DELETE'].includes(actualMethod);
            
            var ajaxConfig = {
                url: url,
                type: useMethodSpoofing ? 'POST' : actualMethod,
                data: data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: onSuccess,
                error: onError || defaultError
            };

            // Handle method spoofing for Laravel
            if (useMethodSpoofing) {
                if (data instanceof FormData) {
                    data.append('_method', actualMethod);
                } else if (typeof data === 'string') {
                    ajaxConfig.data = data + (data.length > 0 ? '&' : '') + '_method=' + actualMethod;
                } else if (typeof data === 'object' && data !== null) {
                    data._method = actualMethod;
                }
            }

            // Detect FormData
            if (data instanceof FormData) {
                ajaxConfig.processData = false;
                ajaxConfig.contentType = false;
            }

            $.ajax(ajaxConfig);
        },

        get: function (url, onSuccess, onError) {
            $.getJSON(url)
                .done(onSuccess)
                .fail(onError || defaultError);
        },
        
        post: function (url, data, onSuccess, onError) {
            this.send(url, 'POST', data, onSuccess, onError);
        },

        put: function (url, data, onSuccess, onError) {
            this.send(url, 'PUT', data, onSuccess, onError);
        },

        patch: function (url, data, onSuccess, onError) {
            this.send(url, 'PATCH', data, onSuccess, onError);
        },

        delete: function (url, data, onSuccess, onError) {
            this.send(url, 'DELETE', data, onSuccess, onError);
        }
    };

})();
