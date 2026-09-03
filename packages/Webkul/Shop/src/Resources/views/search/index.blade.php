<?php
    $searchTitle = $suggestion ?? $query;
    $title = $searchTitle ? trans('shop::app.search.title', ['query' => $searchTitle]) : trans('shop::app.search.results');
    $searchInstead = $suggestion ? $query : null;
?>
<!-- SEO Meta Content -->
@push('meta')
    <meta
        name="description"
        content="{{ $title }}"
    />

    <meta
        name="keywords"
        content="{{ $title }}"
    />
@endPush

@push('styles')
    <style>
        /*
         * Plain CSS instead of Tailwind grid-cols-4/5 utilities (not
         * compiled into this theme's shipped CSS - see the same note in
         * categories/view.blade.php) so the grid can widen to fill the
         * space freed by removing the filters sidebar.
         */
        .folmix-search-product-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            row-gap: 2rem;
            column-gap: 2rem;
        }

        @media (max-width: 1280px) {
            .folmix-search-product-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 1060px) {
            .folmix-search-product-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .folmix-search-product-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                justify-items: center;
                column-gap: 1rem;
            }
        }
    </style>
@endpush

<x-shop::layouts :has-feature="false">
    <!-- Page Title -->
    <x-slot:title>
        {{ $title }}
    </x-slot>

    <div class="container px-[60px] max-lg:px-8 max-sm:px-4">
        @if (request()->has('image-search'))
            @include('shop::search.images.results')
        @endif

        <div class="mt-8 flex items-center justify-between max-md:mt-5">
            <h2 class="break-all text-2xl font-medium max-sm:text-base">
                <span v-text="'{{ preg_replace('/[,\\"\\\']+/', '', $title) }}'" ></span>
            </h2>
        </div>

        @if ($searchInstead)
            <form
                action="{{ route('shop.search.index', ['suggest' => false]) }}"
                class="flex max-w-[445px] items-center"
                role="search"
            >
                <input
                    type="text"
                    name="query"
                    class="hidden"
                    value="{{ $searchInstead }}"
                >

                <input
                    type="text"
                    name="suggest"
                    class="hidden"
                    value="0"
                >

                <p
                    class="mt-1 text-sm text-gray-600"
                    v-pre
                >
                    {{ trans('shop::app.search.suggest') }}

                    <button
                        type="submit"
                        class="text-navyBlue hover:opacity-80 hover:underline"
                        aria-label="{{ trans('shop::app.components.layouts.header.desktop.bottom.submit') }}"
                    >
                        {{ $searchInstead }}
                    </button>
                </p>
            </form>
        @endif
    </div>

    <!-- Product Listing -->
    <v-search>
        <x-shop::shimmer.categories.view />
    </v-search>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-search-template"
        >
            <div class="container px-[60px] max-lg:px-8 max-sm:px-4">
                <div class="md:mt-10">
                    <!-- Product Listing Container -->
                    <div class="flex-1">
                        <!-- Product Listing Toolbar -->
                        @include('shop::categories.toolbar')

                        <!-- Product List Card Container -->
                        <div
                            class="mt-8 grid grid-cols-1 gap-6"
                            v-if="(filters.toolbar.applied.mode ?? filters.toolbar.default.mode) === 'list'"
                        >
                            <!-- Product Card Shimmer Effect -->
                            <template v-if="isLoading">
                                <x-shop::shimmer.products.cards.list count="12" />
                            </template>

                            <!-- Product Card Listing -->
                            <template v-else>
                                <template v-if="products.length">
                                    <x-shop::products.card
                                        ::mode="'list'"
                                        v-for="product in products"
                                    />
                                </template>

                                <!-- Empty Products Container -->
                                <template v-else>
                                    <div class="m-auto grid w-full place-content-center items-center justify-items-center py-32 text-center">
                                        <img
                                            class="max-sm:h-[100px] max-sm:w-[100px]"
                                            src="{{ bagisto_asset('images/thank-you.png') }}"
                                            alt="Empty result"
                                            loading="lazy"
                                            decoding="async"
                                        />

                                        <p
                                            class="text-xl max-sm:text-sm"
                                            role="heading"
                                        >
                                            @lang('shop::app.categories.view.empty')
                                        </p>
                                    </div>
                                </template>
                            </template>
                        </div>

                        <!-- Product Grid Card Container -->
                        <div v-else>
                            <!-- Product Card Shimmer Effect -->
                            <template v-if="isLoading">
                                <div class="folmix-search-product-grid mt-8">
                                    <x-shop::shimmer.products.cards.grid count="12" />
                                </div>
                            </template>

                            <!-- Product Card Listing -->
                            <template v-else>
                                <template v-if="products.length">
                                    <div class="folmix-search-product-grid mt-8">
                                        <x-shop::products.card
                                            ::mode="'grid'"
                                            v-for="product in products"
                                            :navigation-link="route('shop.search.index')"
                                        />
                                    </div>
                                </template>

                                <!-- Empty Products Container -->
                                <template v-else>
                                    <div class="m-auto grid w-full place-content-center items-center justify-items-center py-32 text-center">
                                        <img
                                            class="max-sm:h-[100px] max-sm:w-[100px]"
                                            src="{{ bagisto_asset('images/thank-you.png') }}"
                                            alt="Empty result"
                                            loading="lazy"
                                            decoding="async"
                                        />

                                        <p
                                            class="text-xl max-sm:text-sm"
                                            role="heading"
                                        >
                                            @lang('shop::app.categories.view.empty')
                                        </p>
                                    </div>
                                </template>
                            </template>
                        </div>

                        <!-- Load More Button -->
                        <button
                            class="secondary-button mx-auto mt-[60px] block w-max rounded-2xl px-11 py-3 text-center text-base max-md:rounded-lg max-md:text-sm max-sm:mt-7 max-sm:px-7 max-sm:py-2"
                            @click="loadMoreProducts"
                            v-if="links.next"
                        >
                            @lang('shop::app.categories.view.load-more')
                        </button>
                    </div>
                </div>
            </div>
    </script>

        <script type="module">
            app.component('v-search', {
                template: '#v-search-template',

                data() {
                    return {
                        isMobile: window.innerWidth <= 767,

                        isLoading: true,

                        isDrawerActive: {
                            toolbar: false,

                            filter: false,
                        },

                        filters: {
                            toolbar: {
                                default: {},

                                applied: {},
                            },

                            filter: {},
                        },

                        products: [],

                        links: {},
                    }
                },

                computed: {
                    queryParams() {
                        let queryParams = Object.assign({}, this.filters.filter, this.filters.toolbar.applied);

                        return this.removeJsonEmptyValues(queryParams);
                    },

                    queryString() {
                        return this.jsonToQueryString(this.queryParams);
                    },
                },

                watch: {
                    queryParams() {
                        this.getProducts();
                    },

                    queryString() {
                        window.history.pushState({}, '', '?' + this.queryString);
                    },
                },

                methods: {
                    setFilters(type, filters) {
                        this.filters[type] = filters;
                    },

                    clearFilters(type, filters) {
                        this.filters[type] = {};
                    },

                    getProducts() {
                        this.isDrawerActive = {
                            toolbar: false,

                            filter: false,
                        };

                        this.$axios.get(("{{ route('shop.api.products.index') }}"), {
                            params: this.queryParams
                        })
                            .then(response => {
                                this.isLoading = false;

                                this.products = response.data.data;

                                this.links = response.data.links;
                            }).catch(error => {
                                console.log(error);
                            });
                    },

                    loadMoreProducts() {
                        if (this.links.next) {
                            this.$axios.get(this.links.next).then(response => {
                                this.products = [...this.products, ...response.data.data];

                                this.links = response.data.links;
                            }).catch(error => {
                                console.log(error);
                            });
                        }
                    },

                    removeJsonEmptyValues(params) {
                        Object.keys(params).forEach(function (key) {
                            if ((! params[key] && params[key] !== undefined)) {
                                delete params[key];
                            }

                            if (Array.isArray(params[key])) {
                                params[key] = params[key].join(',');
                            }
                        });

                        return params;
                    },

                    jsonToQueryString(params) {
                        let parameters = new URLSearchParams();

                        for (const key in params) {
                            parameters.append(key, params[key]);
                        }

                        return parameters.toString();
                    }
                },
            });
        </script>
    @endPushOnce
</x-shop::layouts>
