<v-flash-sale :errors="errors">
    <x-admin::shimmer.settings.themes.product-carousel />
</v-flash-sale>

<!-- Flash Sale Vue Component -->
@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-flash-sale-template"
    >
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-2.5 flex items-center justify-between gap-x-2.5">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.flash-sale')
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.flash-sale-description')
                        </p>
                    </div>

                    <!-- Add Product Button -->
                    <div
                        class="secondary-button"
                        @click="$refs.productSearch.openDrawer()"
                    >
                        @lang('admin::app.catalog.products.edit.links.add-btn')
                    </div>
                </div>

                <!-- Title -->
                <x-admin::form.control-group class="mb-2.5 pt-4">
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.settings.themes.edit.filter-title')
                    </x-admin::form.control-group.label>

                    <v-field
                        type="text"
                        name="{{ $currentLocale->code }}[options][title]"
                        value="{{ $theme->translate($currentLocale->code)->options['title'] ?? '' }}"
                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        :class="[errors['{{ $currentLocale->code }}[options][title]'] ? 'border border-red-600 hover:border-red-600' : '']"
                        rules="required"
                        label="@lang('admin::app.settings.themes.edit.filter-title')"
                        placeholder="@lang('admin::app.settings.themes.edit.filter-title')"
                    >
                    </v-field>

                    <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[options][title]" />
                </x-admin::form.control-group>

                <!-- Subtitle -->
                <x-admin::form.control-group class="mb-2.5">
                    <x-admin::form.control-group.label>
                        @lang('admin::app.settings.themes.edit.flash-sale-subtitle')
                    </x-admin::form.control-group.label>

                    <v-field
                        type="text"
                        name="{{ $currentLocale->code }}[options][subtitle]"
                        value="{{ $theme->translate($currentLocale->code)->options['subtitle'] ?? '' }}"
                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        label="@lang('admin::app.settings.themes.edit.flash-sale-subtitle')"
                        placeholder="@lang('admin::app.settings.themes.edit.flash-sale-subtitle')"
                    >
                    </v-field>
                </x-admin::form.control-group>

                <!-- View All URL -->
                <x-admin::form.control-group class="mb-2.5">
                    <x-admin::form.control-group.label>
                        @lang('admin::app.settings.themes.edit.url')
                    </x-admin::form.control-group.label>

                    <v-field
                        type="text"
                        name="{{ $currentLocale->code }}[options][view_all_url]"
                        value="{{ $theme->translate($currentLocale->code)->options['view_all_url'] ?? '' }}"
                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        label="@lang('admin::app.settings.themes.edit.url')"
                        placeholder="@lang('admin::app.settings.themes.edit.url')"
                    >
                    </v-field>
                </x-admin::form.control-group>

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
                                <!-- Hidden Input: index keeps the admin's chosen order -->
                                <input
                                    type="hidden"
                                    :name="'{{ $currentLocale->code }}[options][product_ids][' + index + ']'"
                                    :value="element.id"
                                />

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
                                        @lang('admin::app.settings.themes.edit.delete')
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
                        alt="@lang('admin::app.settings.themes.edit.flash-sale')"
                    >

                    <div class="flex flex-col items-center gap-1.5">
                        <p class="text-base font-semibold text-gray-400">
                            @lang('admin::app.settings.themes.edit.flash-sale')
                        </p>

                        <p class="text-gray-400">
                            @lang('admin::app.settings.themes.edit.flash-sale-description')
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
        </div>
    </script>

    <script type="module">
        app.component('v-flash-sale', {
            template: '#v-flash-sale-template',

            props: ['errors'],

            data() {
                return {
                    /**
                     * Hydrated server-side from the saved product IDs, so the admin
                     * sees the products (with image/price) they picked previously.
                     */
                    addedProducts: @json($flashSaleProducts ?? []),
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
            },
        });
    </script>
@endPushOnce
