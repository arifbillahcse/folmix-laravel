{{--
    Just for You Grid Component (3 rows x 4 columns = 12 products per page)

    Uses the "JustForYouController" API (route: shop.api.products.just_for_you.index) which returns:
    - Featured products from the last-viewed category (read from the `last_viewed_category_id`
      cookie, written by the product view page)
    - Fallback: featured products globally (for new visitors / when the cookie is absent)

    Product cards (x-shop::products.card) are Vue components that only render correctly inside a
    Vue `v-for` scope, so this section follows the same Vue + API pattern as the other native
    homepage sections ("New Arrival", "Flash Sale") instead of looping over Blade `$product`
    objects directly.

    Layout note: the grid uses plain CSS (`jfy-` prefixed classes) rather than Tailwind utility
    classes. Tailwind only scans `packages/Webkul/Shop/src/Resources/**` for class names, so
    utilities that appear nowhere in that package -- `grid-cols-4` among them -- are never
    compiled into the shop stylesheet. Using them here silently produced a one-column layout.

    "Load More" appends the next page in place over AJAX, without a page reload.

    Props:
      - title: Section heading (default "Just for You")
      - src: API endpoint URL
--}}

@props([
    'title' => 'Just for You',
    'src',
])

<v-just-for-you-grid
    src="{{ $src }}"
    title="{{ $title }}"
>
    <x-shop::shimmer.products.carousel :navigation-link="false" />
</v-just-for-you-grid>

@pushOnce('styles')
    <style>
        .jfy-section {
            margin-top: 80px;
        }

        .jfy-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .jfy-title {
            font-family: 'DM Serif Display', serif;
            font-size: 30px;
            line-height: 1.2;
        }

        .jfy-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 32px;
            margin-top: 40px;
        }

        /*
            The product card is built for a fixed 291px column and caps its inner blocks with
            `max-w-[291px]` / `min-w-[291px]`. Inside a fluid grid those caps make narrow columns
            overflow, so they are relaxed to the column width here.
        */
        .jfy-grid > * {
            width: 100%;
            min-width: 0;
        }

        .jfy-grid [class*="max-w-[291px]"],
        .jfy-grid [class*="min-w-[291px]"],
        .jfy-grid [class*="max-w-[192px]"],
        .jfy-grid [class*="min-w-[170px]"] {
            max-width: 100% !important;
            min-width: 0 !important;
        }

        .jfy-grid img {
            width: 100%;
        }

        .jfy-more {
            display: flex;
            justify-content: center;
            margin-top: 32px;
        }

        .jfy-more button:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        @media (max-width: 1180px) {
            .jfy-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .jfy-section {
                margin-top: 32px;
            }

            .jfy-title {
                font-size: 20px;
            }

            .jfy-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
                margin-top: 20px;
            }

            .jfy-more {
                margin-top: 20px;
            }
        }
    </style>
@endPushOnce

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-just-for-you-grid-template"
    >
        <div
            class="container jfy-section max-lg:px-8 max-sm:!px-4"
            v-if="! isLoading && products.length"
        >
            <div class="jfy-head">
                <h2 class="jfy-title">
                    @{{ title }}
                </h2>
            </div>

            <div class="jfy-grid">
                <x-shop::products.card
                    v-for="product in products"
                    ::key="product.id"
                />
            </div>

            <div
                class="jfy-more"
                v-if="currentPage < lastPage"
            >
                <button
                    type="button"
                    class="secondary-button block w-max rounded-2xl px-11 py-3 text-center text-base max-md:rounded-lg"
                    :disabled="isLoadingMore"
                    @click="loadMore"
                >
                    <span v-if="! isLoadingMore">Load More</span>

                    <span v-else>Loading...</span>
                </button>
            </div>
        </div>

        <template v-if="isLoading">
            <x-shop::shimmer.products.carousel :navigation-link="false" />
        </template>
    </script>

    <script type="module">
        app.component('v-just-for-you-grid', {
            template: '#v-just-for-you-grid-template',

            props: [
                'src',
                'title',
            ],

            data() {
                return {
                    isLoading: true,

                    isLoadingMore: false,

                    products: [],

                    currentPage: 1,

                    lastPage: 1,
                };
            },

            mounted() {
                this.getProducts();
            },

            methods: {
                /**
                 * Fetch a page of products. The first page replaces the grid, later pages are
                 * appended in place so "Load More" never reloads the page.
                 */
                getProducts(page = 1) {
                    this.$axios.get(this.src, { params: { page } })
                        .then(response => {
                            const products = response.data.data ?? [];

                            this.products = page === 1
                                ? products
                                : this.products.concat(products);

                            this.currentPage = response.data.meta?.current_page ?? page;
                            this.lastPage = response.data.meta?.last_page ?? page;

                            this.isLoading = false;
                            this.isLoadingMore = false;
                        })
                        .catch(error => {
                            this.isLoading = false;
                            this.isLoadingMore = false;

                            console.log(error);
                        });
                },

                loadMore() {
                    if (
                        this.isLoadingMore
                        || this.currentPage >= this.lastPage
                    ) {
                        return;
                    }

                    this.isLoadingMore = true;

                    this.getProducts(this.currentPage + 1);
                },
            },
        });
    </script>
@endPushOnce
