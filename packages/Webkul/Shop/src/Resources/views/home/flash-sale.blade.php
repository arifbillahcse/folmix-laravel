@push('styles')
    <style>
        /*
         * Plain CSS instead of a Tailwind utility (grid-cols-4) - same reason
         * as the category page's grid: this theme's compiled CSS bundle isn't
         * rebuilt from source on deploy, so only utility classes already
         * present elsewhere at build time actually exist in the shipped
         * stylesheet.
         */
        .folmix-flash-sale-product-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            row-gap: 2rem;
            column-gap: 2rem;
        }

        @media (max-width: 1280px) {
            .folmix-flash-sale-product-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 1060px) {
            .folmix-flash-sale-product-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .folmix-flash-sale-product-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                justify-items: center;
                column-gap: 1rem;
            }
        }
    </style>
@endpush

<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('shop::app.home.flash-sale.title')
    </x-slot>

    <v-flash-sale-listing>
        <div class="container mt-20 max-lg:px-8 max-md:mt-8 max-sm:mt-7 max-sm:!px-4">
            <div class="folmix-flash-sale-product-grid">
                <x-shop::shimmer.products.cards.grid count="12" />
            </div>
        </div>
    </v-flash-sale-listing>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-flash-sale-listing-template"
        >
            <div class="container mt-20 max-lg:px-8 max-md:mt-8 max-sm:mt-7 max-sm:!px-4">
                <h1 class="font-dmserif text-3xl max-md:text-2xl max-sm:text-xl">
                    @lang('shop::app.home.flash-sale.title')
                </h1>

                <template v-if="isLoading">
                    <div class="folmix-flash-sale-product-grid mt-8">
                        <x-shop::shimmer.products.cards.grid count="12" />
                    </div>
                </template>

                <template v-else-if="products.length">
                    <div class="folmix-flash-sale-product-grid mt-8">
                        <x-shop::products.card v-for="product in products" />
                    </div>
                </template>

                <template v-else>
                    <div class="m-auto grid w-full place-content-center items-center justify-items-center py-32 text-center">
                        <p
                            class="text-xl max-md:text-sm"
                            role="heading"
                        >
                            @lang('shop::app.home.flash-sale.empty')
                        </p>
                    </div>
                </template>
            </div>
        </script>

        <script type="module">
            app.component('v-flash-sale-listing', {
                template: '#v-flash-sale-listing-template',

                data() {
                    return {
                        isLoading: true,

                        products: [],
                    };
                },

                mounted() {
                    this.getProducts();
                },

                methods: {
                    getProducts() {
                        this.$axios.get("{{ route('shop.api.products.flash-sale.all') }}")
                            .then(response => {
                                this.isLoading = false;

                                this.products = response.data.data;
                            })
                            .catch(error => {
                                this.isLoading = false;
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-shop::layouts>
