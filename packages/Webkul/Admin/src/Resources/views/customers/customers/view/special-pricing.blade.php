<!-- Special Pricing -->
<div class="box-shadow rounded bg-white p-4 last:pb-0 dark:bg-gray-900">
    <div class="flex items-center justify-between p-4 pb-0">
        <div class="flex flex-col gap-1">
            <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
                @lang('admin::app.customers.customers.view.special-pricing.title')
            </p>

            <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                @lang('admin::app.customers.customers.view.special-pricing.info')
            </p>
        </div>
    </div>

    <v-customer-special-price :customer-id="{{ $customer->id }}"></v-customer-special-price>
</div>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-customer-special-price-template"
    >
        <div>
            <div class="flex items-center justify-end p-4">
                <p
                    class="cursor-pointer text-blue-600 transition-all hover:underline"
                    @click="openAddModal"
                >
                    @lang('admin::app.customers.customers.view.special-pricing.add-btn')
                </p>
            </div>

            <!-- List -->
            <div class="grid">
                <div
                    class="flex justify-between gap-2.5 border-b border-slate-300 p-4 last:border-none dark:border-gray-800"
                    v-for="item in prices"
                    :key="item.id"
                >
                    <div class="grid gap-1">
                        <p class="font-semibold text-gray-800 dark:text-white">
                            @{{ item.product.name }}
                        </p>

                        <p class="text-gray-600 dark:text-gray-300">
                            @{{ "@lang('admin::app.customers.customers.view.special-pricing.sku')".replace(':sku', item.product.sku) }}
                        </p>
                    </div>

                    <div class="grid place-content-start gap-1 text-right">
                        <p class="text-sm text-gray-500 line-through dark:text-gray-400">
                            @{{ $admin.formatPrice(item.product.price) }}
                        </p>

                        <p class="font-semibold text-gray-800 dark:text-white">
                            @{{ $admin.formatPrice(item.price) }}
                        </p>

                        <p
                            class="cursor-pointer text-blue-600 transition-all hover:underline"
                            @click="openEditModal(item)"
                        >
                            @lang('admin::app.customers.customers.view.special-pricing.edit-btn')
                        </p>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                class="flex items-center gap-5 p-4"
                v-if="! prices.length"
            >
                <img
                    src="{{ bagisto_asset('images/icon-discount.svg') }}"
                    class="h-20 w-20 rounded border border-dashed border-gray-300 dark:border-gray-800 dark:mix-blend-exclusion dark:invert"
                />

                <div class="flex flex-col gap-1.5">
                    <p class="text-base font-semibold text-gray-400">
                        @lang('admin::app.customers.customers.view.special-pricing.empty-title')
                    </p>

                    <p class="text-gray-400">
                        @lang('admin::app.customers.customers.view.special-pricing.empty-info')
                    </p>
                </div>
            </div>

            <!-- Product Search Drawer -->
            <x-admin::products.search
                ref="productSearch"
                ::added-product-ids="addedProductIds"
                @onProductAdded="onProductAdded($event)"
            />

            <!-- Price Form Modal -->
            <x-admin::form
                v-slot="{ handleSubmit }"
                as="div"
            >
                <form @submit="handleSubmit($event, save)">
                    <x-admin::modal ref="priceModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p
                                class="text-lg font-bold text-gray-800 dark:text-white"
                                v-if="! selectedPrice.id"
                            >
                                @lang('admin::app.customers.customers.view.special-pricing.create-title')
                            </p>

                            <p
                                class="text-lg font-bold text-gray-800 dark:text-white"
                                v-else
                            >
                                @lang('admin::app.customers.customers.view.special-pricing.update-title')
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>
                            <div class="grid gap-1.5 pb-4">
                                <p class="font-semibold text-gray-800 dark:text-white">
                                    @{{ selectedPrice.product_name }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ "@lang('admin::app.customers.customers.view.special-pricing.sku')".replace(':sku', selectedPrice.product_sku) }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ "@lang('admin::app.customers.customers.view.special-pricing.regular-price')".replace(':price', $admin.formatPrice(selectedPrice.regular_price)) }}
                                </p>
                            </div>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.customers.customers.view.special-pricing.price')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="price"
                                    rules="required|decimal|min_value:0"
                                    v-model="selectedPrice.price"
                                    :label="trans('admin::app.customers.customers.view.special-pricing.price')"
                                />

                                <x-admin::form.control-group.error control-name="price" />
                            </x-admin::form.control-group>
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <div class="flex items-center gap-x-2.5">
                                <x-admin::button
                                    button-type="button"
                                    class="cursor-pointer whitespace-nowrap rounded-md border-2 border-transparent px-3 py-1.5 font-semibold text-red-600 transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                                    :title="trans('admin::app.customers.customers.view.special-pricing.delete-btn')"
                                    v-if="selectedPrice.id"
                                    @click="remove"
                                />

                                <x-admin::button
                                    button-type="submit"
                                    class="primary-button"
                                    :title="trans('admin::app.customers.customers.view.special-pricing.save-btn')"
                                    ::loading="isSaving"
                                    ::disabled="isSaving"
                                />
                            </div>
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </div>
    </script>

    <script type="module">
        app.component('v-customer-special-price', {
            template: '#v-customer-special-price-template',

            props: ['customerId'],

            data() {
                return {
                    prices: @json($customer->product_prices),

                    selectedPrice: this.emptyPrice(),

                    isSaving: false,
                }
            },

            computed: {
                addedProductIds() {
                    return this.prices.map(item => item.product_id);
                }
            },

            methods: {
                emptyPrice() {
                    return {
                        id: null,
                        product_id: null,
                        product_name: '',
                        product_sku: '',
                        regular_price: 0,
                        price: 0,
                    };
                },

                openAddModal() {
                    this.selectedPrice = this.emptyPrice();

                    this.$refs.productSearch.openDrawer();
                },

                openEditModal(item) {
                    this.selectedPrice = {
                        id: item.id,
                        product_id: item.product_id,
                        product_name: item.product.name,
                        product_sku: item.product.sku,
                        regular_price: item.product.price,
                        price: item.price,
                    };

                    this.$refs.priceModal.open();
                },

                onProductAdded(products) {
                    if (! products.length) {
                        return;
                    }

                    let product = products[0];

                    this.selectedPrice = {
                        id: null,
                        product_id: product.id,
                        product_name: product.name,
                        product_sku: product.sku,
                        regular_price: product.price,
                        price: product.price,
                    };

                    this.$refs.priceModal.open();
                },

                save(params) {
                    this.isSaving = true;

                    let request = this.selectedPrice.id
                        ? this.$axios.put(
                            "{{ route('admin.customers.customers.special_prices.update', [$customer->id, ':priceId']) }}".replace(':priceId', this.selectedPrice.id),
                            { price: params.price }
                        )
                        : this.$axios.post(
                            "{{ route('admin.customers.customers.special_prices.store', $customer->id) }}",
                            { product_id: this.selectedPrice.product_id, price: params.price }
                        );

                    request
                        .then((response) => {
                            this.isSaving = false;

                            if (this.selectedPrice.id) {
                                this.prices = this.prices.map(item => item.id === response.data.data.id ? response.data.data : item);
                            } else {
                                this.prices.push(response.data.data);
                            }

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$refs.priceModal.close();
                        })
                        .catch((error) => {
                            this.isSaving = false;
                        });
                },

                remove() {
                    const selectedId = this.selectedPrice?.id ?? null;

                    if (! selectedId) {
                        return;
                    }

                    this.$refs.priceModal.close();

                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            this.$axios.delete(
                                "{{ route('admin.customers.customers.special_prices.delete', [$customer->id, ':priceId']) }}".replace(':priceId', selectedId)
                            )
                                .then((response) => {
                                    this.prices = this.prices.filter(item => item.id !== selectedId);

                                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                })
                                .catch((error) => {});
                        }
                    });
                },
            },
        });
    </script>
@endPushOnce
