<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.activity-log.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('admin::app.activity-log.index.title')
        </p>
    </div>

    {!! view_render_event('bagisto.admin.activity_log.list.before') !!}

    <x-admin::datagrid :src="route('admin.activity_log.index')" />

    {!! view_render_event('bagisto.admin.activity_log.list.after') !!}

    @push('scripts')
        <script>
            /**
             * Resolves the Country column client-side via a free public
             * lookup service, since this server cannot be relied on to
             * reach the internet itself. Runs in the admin's own browser,
             * which has normal internet access.
             */
            (function () {
                const CACHE_KEY_PREFIX = 'activity-log-country:';
                const QUEUE_DELAY_MS = 200;

                let queue = [];
                let processing = false;

                function isPrivateOrLocalIp(ip) {
                    return (
                        ip === '127.0.0.1'
                        || ip === '::1'
                        || /^10\./.test(ip)
                        || /^192\.168\./.test(ip)
                        || /^172\.(1[6-9]|2\d|3[01])\./.test(ip)
                    );
                }

                function getCachedCountry(ip) {
                    try {
                        return localStorage.getItem(CACHE_KEY_PREFIX + ip);
                    } catch (e) {
                        return null;
                    }
                }

                function setCachedCountry(ip, country) {
                    try {
                        localStorage.setItem(CACHE_KEY_PREFIX + ip, country);
                    } catch (e) {
                        // Ignore storage errors (private browsing, quota, etc).
                    }
                }

                function renderCountry(el, country) {
                    el.textContent = country || '—';
                }

                function processQueue() {
                    if (processing || ! queue.length) {
                        return;
                    }

                    processing = true;

                    const el = queue.shift();
                    const ip = el.dataset.ip;

                    fetch('https://ipwho.is/' + encodeURIComponent(ip))
                        .then((response) => response.json())
                        .then((data) => {
                            const country = data && data.success !== false ? data.country : null;

                            if (country) {
                                setCachedCountry(ip, country);
                            }

                            renderCountry(el, country);
                        })
                        .catch(() => renderCountry(el, null))
                        .finally(() => {
                            processing = false;

                            setTimeout(processQueue, QUEUE_DELAY_MS);
                        });
                }

                function scan(root) {
                    const elements = (root || document).querySelectorAll('.activity-log-country:not([data-resolved])');

                    elements.forEach((el) => {
                        const ip = el.dataset.ip;

                        if (! ip) {
                            return;
                        }

                        el.dataset.resolved = '1';

                        if (isPrivateOrLocalIp(ip)) {
                            renderCountry(el, null);

                            return;
                        }

                        const cached = getCachedCountry(ip);

                        if (cached) {
                            renderCountry(el, cached);

                            return;
                        }

                        queue.push(el);

                        processQueue();
                    });
                }

                document.addEventListener('DOMContentLoaded', () => {
                    scan();

                    new MutationObserver(() => scan()).observe(document.body, {
                        childList: true,
                        subtree: true,
                    });
                });
            })();
        </script>
    @endpush
</x-admin::layouts>
