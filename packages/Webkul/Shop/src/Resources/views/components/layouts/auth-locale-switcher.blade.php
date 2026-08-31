@if (core()->getCurrentChannel()->locales()->count() > 1)
    <x-shop::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'right' : 'left' }}">
        <x-slot:toggle>
            <div
                class="flex cursor-pointer items-center gap-1"
                role="button"
                tabindex="0"
            >
                <img
                    src="{{ ! empty(core()->getCurrentLocale()->logo_url)
                            ? core()->getCurrentLocale()->logo_url
                            : bagisto_asset('images/default-language.svg')
                        }}"
                    class="h-4 w-6"
                    alt="@lang('shop::app.components.layouts.header.desktop.top.default-locale')"
                    width="24"
                    height="16"
                />

                <span
                    class="text-2xl icon-arrow-down"
                    role="presentation"
                ></span>
            </div>
        </x-slot>

        <x-slot:content class="journal-scroll max-h-[500px] !p-0">
            <v-auth-locale-switcher></v-auth-locale-switcher>
        </x-slot>
    </x-shop::dropdown>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-auth-locale-switcher-template"
        >
            <div class="my-2.5 grid gap-1 overflow-auto max-md:my-0 sm:max-h-[500px]">
                <span
                    class="flex cursor-pointer items-center gap-2.5 px-5 py-2 text-base hover:bg-gray-100"
                    :class="{'bg-gray-100': locale.code == '{{ app()->getLocale() }}'}"
                    v-for="locale in locales"
                    @click="change(locale)"
                >
                    <img
                        :src="locale.logo_url || '{{ bagisto_asset('images/default-language.svg') }}'"
                        width="24"
                        height="16"
                    />

                    @{{ locale.name }}
                </span>
            </div>
        </script>

        <script type="module">
            app.component('v-auth-locale-switcher', {
                template: '#v-auth-locale-switcher-template',

                data() {
                    return {
                        locales: @json(core()->getCurrentChannel()->locales()->orderBy('name')->get()),
                    };
                },

                methods: {
                    change(locale) {
                        let url = new URL(window.location.href);

                        url.searchParams.set('locale', locale.code);

                        window.location.href = url.href;
                    }
                }
            });
        </script>
    @endPushOnce
@endif
