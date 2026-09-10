<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.suggested-products.edit.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                @lang('admin::app.settings.suggested-products.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ url('/suggested') }}"
                    target="_blank"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.settings.suggested-products.edit.view-page')
                </a>
            </div>
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-300">
            @lang('admin::app.settings.suggested-products.edit.info')
        </p>

        <v-suggested-products></v-suggested-products>
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-suggested-products-template"
        >
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <div class="mb-2.5 flex items-center justify-between gap-x-2.5">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.suggested-products.edit.products')
                        </p>

                        <!-- Add Product Button -->
                        <div
                            class="secondary-button"
                            @click="$refs.productSearch.openDrawer()"
                        >
                            @lang('admin::app.catalog.products.edit.links.add-btn')
                        </div>
                    </div>

                    <span class="mb-4 mt-4 block w-full border-b dark:border-gray-800"></span>

                    <!-- Selected Products -->
                    <div
                        class="grid"
                        v-if="addedProducts.length"
                    >
                        <draggable
                            ghost-class="draggable-ghost"
                            handle=".icon-drag"
                            v-bind="{animation: 200}"
                            :list="addedProducts"
                            item-key="id"
                        >
                            <template #item="{ element, index }">
                                <div class="flex justify-between gap-2.5 border-b border-slate-300 p-4 last:border-b-0 dark:border-gray-800">
                                    <!-- Information -->
                                    <div class="flex gap-2.5">
                                        <!-- Drag Icon -->
                                        <i class="icon-drag cursor-grab text-xl text-gray-600 transition-all dark:text-gray-300"></i>

                                        <!-- Image -->
                                        <div
                                            class="relative h-[60px] max-h-[60px] w-full max-w-[60px] overflow-hidden rounded"
                                            :class="{'border border-dashed border-gray-300 dark:border-gray-800 dark:mix-blend-exclusion dark:invert': ! element.images?.length}"
                                        >
                                            <template v-if="! element.images?.length">
                                                <img src="{{ bagisto_asset('images/product-placeholders/front.svg') }}">

                                                <p class="absolute bottom-1.5 w-full text-center text-[6px] font-semibold text-gray-400">
                                                    @lang('admin::app.catalog.products.edit.links.image-placeholder')
                                                </p>
                                            </template>

                                            <template v-else>
                                                <img :src="element.images[0].url">
                                            </template>
                                        </div>

                                        <!-- Details -->
                                        <div class="grid place-content-start gap-1.5">
                                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                @{{ element.name }}
                                            </p>

                                            <p class="text-gray-600 dark:text-gray-300">
                                                @{{ "@lang('admin::app.catalog.products.edit.links.sku')".replace(':sku', element.sku) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="grid place-content-start gap-1 ltr:text-right rtl:text-left">
                                        <p class="font-semibold text-gray-800 dark:text-white">
                                            @{{ element.formatted_price }}
                                        </p>

                                        <p
                                            class="cursor-pointer text-red-600 transition-all hover:underline"
                                            @click="remove(element)"
                                        >
                                            @lang('admin::app.settings.suggested-products.edit.remove')
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                    </div>

                    <!-- Empty State -->
                    <div
                        class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                        v-else
                    >
                        <img
                            class="h-[120px] w-[120px] p-2 dark:mix-blend-exclusion dark:invert"
                            src="{{ bagisto_asset('images/empty-placeholders/default.svg') }}"
                            alt="@lang('admin::app.settings.suggested-products.edit.products')"
                        >

                        <div class="flex flex-col items-center gap-1.5">
                            <p class="text-base font-semibold text-gray-400">
                                @lang('admin::app.settings.suggested-products.edit.empty-title')
                            </p>

                            <p class="text-gray-400">
                                @lang('admin::app.settings.suggested-products.edit.empty-description')
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Product Search Blade Component -->
                <x-admin::products.search
                    ref="productSearch"
                    ::added-product-ids="addedProductIds"
                    @onProductAdded="addSelected($event)"
                />

                <div class="flex justify-end">
                    <button
                        type="button"
                        class="primary-button"
                        ::disabled="isSaving"
                        @click="save"
                    >
                        @lang('admin::app.settings.suggested-products.edit.save-btn')
                    </button>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-suggested-products', {
                template: '#v-suggested-products-template',

                data() {
                    return {
                        /**
                         * Hydrated server-side from the saved product IDs, so the admin
                         * sees the products (with image/price) already on the page.
                         */
                        addedProducts: @json($suggestedProducts ?? []),

                        isSaving: false,
                    };
                },

                computed: {
                    addedProductIds() {
                        return this.addedProducts.map(product => product.id);
                    },
                },

                methods: {
                    addSelected(selectedProducts) {
                        this.addedProducts = [...this.addedProducts, ...selectedProducts];
                    },

                    remove(product) {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                this.addedProducts = this.addedProducts.filter(item => item.id !== product.id);
                            },
                        });
                    },

                    save() {
                        this.isSaving = true;

                        this.$axios.put("{{ route('admin.settings.suggested_products.update') }}", {
                                product_ids: this.addedProductIds,
                            })
                            .then(response => {
                                this.isSaving = false;

                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            })
                            .catch(error => {
                                this.isSaving = false;

                                this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message ?? '' });
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
