{!! view_render_event('bagisto.shop.layout.footer.before') !!}

<!--
    The theme customization repository is injected directly here because
    there is no way to retrieve it from the view composer, as this is an
    anonymous component.
-->
@inject('themeCustomizationRepository', 'Webkul\Theme\Repositories\ThemeCustomizationRepository')

<!--
    This code needs to be refactored to reduce the amount of PHP in the Blade
    template as much as possible.
-->
@php
    $channel = core()->getCurrentChannel();

    $customization = $themeCustomizationRepository->findOneWhere([
        'type'       => 'footer_links',
        'status'     => 1,
        'theme_code' => $channel->theme,
        'channel_id' => $channel->id,
    ]);

    $footerLinks = collect($customization?->options ?? [])
        ->flatten(1)
        ->sortBy('sort_order')
        ->values();
@endphp

<footer class="mt-9 bg-lightOrange max-sm:mt-10">
    <div class="grid grid-cols-1 gap-x-10 gap-y-10 p-[60px] sm:grid-cols-2 lg:grid-cols-3 max-md:gap-8 max-md:p-8 max-sm:grid-cols-1 max-sm:px-4 max-sm:py-5">
        {!! view_render_event('bagisto.shop.layout.footer.brand.before') !!}

        <!-- Brand / About / Contact -->
        <div class="grid gap-4">
            <img
                class="h-10 w-max"
                src="{{ $channel->logo_url ?? bagisto_asset('images/logo.svg') }}"
                alt="{{ $channel->name ?? config('app.name') }}"
            />

            <p class="max-w-[320px] text-sm text-zinc-600">
                @lang('shop::app.components.layouts.footer.about-tagline')
            </p>

            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 shrink-0 place-content-center rounded-lg bg-navyBlue text-white">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                </span>

                <div>
                    <p class="text-xs font-semibold uppercase text-zinc-500">
                        @lang('shop::app.components.layouts.footer.need-help')
                    </p>

                    <p class="text-base font-bold text-navyBlue">
                        @lang('shop::app.components.layouts.footer.phone-number')
                    </p>
                </div>
            </div>
        </div>

        {!! view_render_event('bagisto.shop.layout.footer.brand.after') !!}

        {!! view_render_event('bagisto.shop.layout.footer.useful_links.before') !!}

        <!-- Useful Links (Admin Managed) -->
        @if ($footerLinks->isNotEmpty())
            <div v-pre>
                <p class="mb-5 text-sm font-semibold uppercase text-zinc-800">
                    @lang('shop::app.components.layouts.footer.useful-links')
                </p>

                <ul class="grid gap-5 text-sm">
                    @foreach ($footerLinks as $link)
                        <li>
                            <a
                                class="text-zinc-600 hover:text-navyBlue"
                                href="{{ $link['url'] }}"
                            >
                                {{ $link['title'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {!! view_render_event('bagisto.shop.layout.footer.useful_links.after') !!}

        {!! view_render_event('bagisto.shop.layout.footer.address.before') !!}

        <!-- Company Address -->
        <div>
            <p class="mb-5 text-sm font-semibold uppercase text-zinc-800">
                @lang('shop::app.components.layouts.footer.address-heading')
            </p>

            <p class="mb-3 text-sm text-zinc-600">
                @lang('shop::app.components.layouts.footer.address-trademark')
            </p>

            <ul class="grid gap-2 text-sm text-zinc-600">
                <li>@lang('shop::app.components.layouts.footer.address-street')</li>
                <li>@lang('shop::app.components.layouts.footer.address-city')</li>
                <li>@lang('shop::app.components.layouts.footer.address-vat')</li>
            </ul>
        </div>

        {!! view_render_event('bagisto.shop.layout.footer.address.after') !!}
    </div>

    <div class="flex justify-between bg-[#F1EADF] px-[60px] py-3.5 max-md:justify-center max-sm:px-5">
        {!! view_render_event('bagisto.shop.layout.footer.footer_text.before') !!}

        <p class="text-sm text-zinc-600 max-md:text-center">
            @if (core()->getConfigData('general.content.footer.copyright_content'))
                {!! core()->getConfigData('general.content.footer.copyright_content') !!}
            @else
                @lang('shop::app.components.layouts.footer.footer-text', ['current_year'=> date('Y') ])
            @endif
        </p>

        {!! view_render_event('bagisto.shop.layout.footer.footer_text.after') !!}
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
