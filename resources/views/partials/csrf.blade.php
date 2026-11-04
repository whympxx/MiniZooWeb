<script>
    // Menambahkan token CSRF Laravel ke setiap fetch() same-origin dan ke form POST
    // yang dibuat lewat JavaScript, sehingga script lama tetap berjalan tanpa diubah.
    (function () {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (!meta) return;
        var token = meta.getAttribute('content');

        function sameOrigin(input) {
            try {
                var url = new URL(typeof input === 'string' ? input : input.url, window.location.href);
                return url.origin === window.location.origin;
            } catch (e) {
                return false;
            }
        }

        var originalFetch = window.fetch;
        window.fetch = function (input, init) {
            if (!sameOrigin(input)) return originalFetch.call(this, input, init);
            init = init || {};
            var headers = new Headers(init.headers || {});
            headers.set('X-CSRF-TOKEN', token);
            headers.set('X-Requested-With', 'XMLHttpRequest');
            if (!headers.has('Accept')) headers.set('Accept', 'application/json');
            init.headers = headers;
            init.credentials = init.credentials || 'same-origin';
            return originalFetch.call(this, input, init);
        };

        function addToken(form) {
            if (!form || (form.method || '').toLowerCase() !== 'post') return;
            if (form.querySelector('input[name="_token"]')) return;
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_token';
            input.value = token;
            form.appendChild(input);
        }

        var originalSubmit = HTMLFormElement.prototype.submit;
        HTMLFormElement.prototype.submit = function () {
            addToken(this);
            return originalSubmit.call(this);
        };
        document.addEventListener('submit', function (e) { addToken(e.target); }, true);
    })();
</script>
