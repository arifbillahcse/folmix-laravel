{!! view_render_event('bagisto.shop.layout.footer.before') !!}

<!--
    The category and theme customization repositories are injected directly
    here because there is no way to retrieve them from the view composer, as
    this is an anonymous component.
-->
@inject('themeCustomizationRepository', 'Webkul\Theme\Repositories\ThemeCustomizationRepository')
@inject('categoryRepository', 'Webkul\Category\Repositories\CategoryRepository')

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

    $footerCategories = $categoryRepository->getVisibleCategoryTree($channel->root_category_id);
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
        @if ($customization?->options)
            <div v-pre>
                <p class="mb-5 text-sm font-semibold uppercase text-zinc-800">
                    @lang('shop::app.components.layouts.footer.useful-links')
                </p>

                <div class="grid grid-cols-1 gap-x-10 gap-y-5 sm:grid-cols-2">
                    @foreach ($customization->options as $footerLinkSection)
                        <ul class="grid gap-5 text-sm">
                            @php
                                usort($footerLinkSection, function ($a, $b) {
                                    return $a['sort_order'] - $b['sort_order'];
                                });
                            @endphp

                            @foreach ($footerLinkSection as $link)
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
                    @endforeach
                </div>
            </div>
        @endif

        {!! view_render_event('bagisto.shop.layout.footer.useful_links.after') !!}

        {!! view_render_event('bagisto.shop.layout.footer.categories.before') !!}

        <!-- Categories -->
        @if (count($footerCategories))
            <div>
                <p class="mb-5 text-sm font-semibold uppercase text-zinc-800">
                    @lang('shop::app.components.layouts.footer.categories')
                </p>

                <ul class="grid gap-5 text-sm">
                    @foreach ($footerCategories as $category)
                        <li>
                            <a
                                class="text-zinc-600 hover:text-navyBlue"
                                href="{{ $category->url }}"
                            >
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {!! view_render_event('bagisto.shop.layout.footer.categories.after') !!}
    </div>

    {!! view_render_event('bagisto.shop.layout.footer.newsletter_subscription.before') !!}

    <!-- News Letter subscription -->
    @if (core()->getConfigData('customer.settings.newsletter.subscription'))
        <div class="grid gap-2.5 px-[60px] pb-[60px] max-md:px-8 max-md:pb-8 max-sm:px-4 max-sm:pb-5">
            <p
                class="max-w-[288px] text-3xl italic leading-[45px] text-navyBlue max-md:text-2xl max-sm:text-lg"
                role="heading"
                aria-level="2"
            >
                @lang('shop::app.components.layouts.footer.newsletter-text')
            </p>

            <p class="text-xs">
                @lang('shop::app.components.layouts.footer.subscribe-stay-touch')
            </p>

            <div>
                <x-shop::form
                    :action="route('shop.subscription.store')"
                    class="mt-2.5 rounded max-sm:mt-0"
                    toolname="subscribe_to_newsletter"
                    tooldescription="{{ trans('shop::app.components.layouts.webmcp.subscribe-newsletter') }}"
                    toolautosubmit
                >
                    <div class="relative w-full">
                        <x-shop::form.control-group.control
                            type="email"
                            class="block w-[420px] max-w-full rounded-xl border-2 border-[#e9decc] bg-[#F1EADF] px-5 py-4 text-base max-1060:w-full max-md:p-3.5 max-sm:mb-0 max-sm:rounded-lg max-sm:border-2 max-sm:p-2 max-sm:text-sm"
                            name="email"
                            rules="required|email"
                            label="Email"
                            :aria-label="trans('shop::app.components.layouts.footer.email')"
                            placeholder="email@example.com"
                            toolparamdescription="{{ trans('shop::app.components.layouts.webmcp.subscribe-newsletter-email') }}"
                        />

                        <x-shop::form.control-group.error control-name="email" />

                        <button
                            type="submit"
                            class="absolute top-1.5 flex w-max items-center rounded-xl bg-white px-7 py-2.5 font-medium hover:bg-zinc-100 ltr:right-2 rtl:left-2 max-md:top-1 max-md:px-5 max-md:text-xs max-sm:mt-0 max-sm:rounded-lg max-sm:px-4 max-sm:py-2"
                        >
                            @lang('shop::app.components.layouts.footer.subscribe')
                        </button>
                    </div>
                </x-shop::form>
            </div>
        </div>
    @endif

    {!! view_render_event('bagisto.shop.layout.footer.newsletter_subscription.after') !!}

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
