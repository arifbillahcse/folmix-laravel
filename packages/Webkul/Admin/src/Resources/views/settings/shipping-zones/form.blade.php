@php
    $zone = $zone ?? null;
@endphp

<v-shipping-zone-form></v-shipping-zone-form>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-shipping-zone-form-template"
    >
        <form @submit.prevent="save">
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @{{ isEdit
                        ? "@lang('admin::app.settings.shipping-zones.edit.title')"
                        : "@lang('admin::app.settings.shipping-zones.create.title')" }}
                </p>

                <div class="flex items-center gap-x-2.5">
                    <a
                        href="{{ route('admin.settings.shipping_zones.index') }}"
                        class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                    >
                        @lang('admin::app.settings.shipping-zones.index.form.back')
                    </a>

                    <button
                        type="submit"
                        class="primary-button"
                        :disabled="isSaving"
                    >
                        @lang('admin::app.settings.shipping-zones.index.form.save-btn')
                    </button>
                </div>
            </div>

            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left column -->
                <div class="flex flex-1 flex-col gap-2.5 max-xl:flex-auto">
                    <!-- General -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.shipping-zones.index.form.name')
                        </p>

                        <div class="mb-4">
                            <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white required">
                                @lang('admin::app.settings.shipping-zones.index.form.name')
                            </label>

                            <input
                                type="text"
                                v-model="zone.name"
                                class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            />

                            <p class="mt-1 text-xs text-red-600" v-if="errors.name" v-text="errors.name[0]"></p>
                        </div>

                        <div class="mb-4 flex items-center gap-2.5">
                            <input
                                type="checkbox"
                                id="is_rest_of_world"
                                v-model="zone.is_rest_of_world"
                            />

                            <label for="is_rest_of_world" class="text-sm text-gray-800 dark:text-white">
                                @lang('admin::app.settings.shipping-zones.index.form.rest-of-world')
                            </label>
                        </div>
                    </div>

                    <!-- Regions -->
                    <div
                        class="box-shadow rounded bg-white p-4 dark:bg-gray-900"
                        v-if="! zone.is_rest_of_world"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex flex-col gap-1">
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.shipping-zones.index.form.regions')
                                </p>

                                <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                                    @lang('admin::app.settings.shipping-zones.index.form.regions-info')
                                </p>
                            </div>

                            <div
                                class="secondary-button cursor-pointer"
                                @click="addLocation"
                            >
                                @lang('admin::app.settings.shipping-zones.index.form.add-region-btn')
                            </div>
                        </div>

                        <div
                            class="mb-3 flex items-end gap-2.5 border-b border-gray-100 pb-3 last:border-b-0 last:pb-0 dark:border-gray-800"
                            v-for="(location, index) in zone.locations"
                            :key="index"
                        >
                            <div class="flex-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.shipping-zones.index.form.country')
                                </label>

                                <select
                                    v-model="location.country_code"
                                    class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                >
                                    <option value="">@lang('admin::app.settings.shipping-zones.index.form.country')</option>

                                    <option
                                        v-for="country in countries"
                                        :value="country.code"
                                        v-text="country.name"
                                        :key="country.code"
                                    ></option>
                                </select>
                            </div>

                            <div class="flex-1">
                                <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.shipping-zones.index.form.postcode')
                                </label>

                                <input
                                    type="text"
                                    v-model="location.postcode"
                                    class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                />
                            </div>

                            <span
                                class="icon-delete cursor-pointer rounded-md p-2.5 text-2xl text-red-600 transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                                @click="removeLocation(index)"
                            ></span>
                        </div>

                        <p
                            class="text-sm text-gray-500 dark:text-gray-300"
                            v-if="! zone.locations.length"
                        >
                            @lang('admin::app.settings.shipping-zones.index.form.add-region-btn')
                        </p>
                    </div>

                    <!-- Methods -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex flex-col gap-1">
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.shipping-zones.index.form.methods')
                                </p>

                                <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                                    @lang('admin::app.settings.shipping-zones.index.form.methods-info')
                                </p>
                            </div>

                            <div
                                class="secondary-button cursor-pointer"
                                @click="addMethod"
                            >
                                @lang('admin::app.settings.shipping-zones.index.form.add-method-btn')
                            </div>
                        </div>

                        <div
                            class="mb-3 grid gap-2.5 border-b border-gray-100 pb-3 last:border-b-0 last:pb-0 dark:border-gray-800"
                            v-for="(method, index) in zone.methods"
                            :key="index"
                        >
                            <div class="flex items-end gap-2.5">
                                <div class="flex-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                                        @lang('admin::app.settings.shipping-zones.index.form.method-type')
                                    </label>

                                    <select
                                        v-model="method.type"
                                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="flat_rate">@lang('admin::app.settings.shipping-zones.index.form.flat-rate')</option>
                                        <option value="free_shipping">@lang('admin::app.settings.shipping-zones.index.form.free-shipping')</option>
                                    </select>
                                </div>

                                <div class="flex-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                                        @lang('admin::app.settings.shipping-zones.index.form.method-title')
                                    </label>

                                    <input
                                        type="text"
                                        v-model="method.title"
                                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                    />
                                </div>

                                <span
                                    class="icon-delete cursor-pointer rounded-md p-2.5 text-2xl text-red-600 transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                                    @click="removeMethod(index)"
                                ></span>
                            </div>

                            <div
                                class="flex items-end gap-2.5"
                                v-if="method.type === 'flat_rate'"
                            >
                                <div class="flex-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                                        @lang('admin::app.settings.shipping-zones.index.form.rate')
                                    </label>

                                    <input
                                        type="text"
                                        v-model="method.rate"
                                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                    />
                                </div>

                                <div class="flex-1">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                                        @lang('admin::app.settings.shipping-zones.index.form.calculation-type')
                                    </label>

                                    <select
                                        v-model="method.calculation_type"
                                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                    >
                                        <option value="per_order">@lang('admin::app.settings.shipping-zones.index.form.per-order')</option>
                                        <option value="per_unit">@lang('admin::app.settings.shipping-zones.index.form.per-unit')</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center gap-2.5">
                                <input
                                    type="checkbox"
                                    :id="'method_status_' + index"
                                    v-model="method.status"
                                />

                                <label
                                    :for="'method_status_' + index"
                                    class="text-sm text-gray-800 dark:text-white"
                                >
                                    @lang('admin::app.settings.shipping-zones.index.form.method-status')
                                </label>
                            </div>
                        </div>

                        <p
                            class="text-sm text-gray-500 dark:text-gray-300"
                            v-if="! zone.methods.length"
                        >
                            @lang('admin::app.settings.shipping-zones.index.form.add-method-btn')
                        </p>

                        <p class="mt-2 text-sm text-red-600" v-if="errors.methods" v-text="errors.methods[0]"></p>
                    </div>
                </div>

                <!-- Right column -->
                <div>
                    <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.shipping-zones.index.form.sort-order')
                            </p>

                            <div class="mb-4">
                                <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.shipping-zones.index.form.sort-order')
                                </label>

                                <input
                                    type="number"
                                    v-model.number="zone.sort_order"
                                    class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                />

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                                    @lang('admin::app.settings.shipping-zones.index.form.sort-order-info')
                                </p>
                            </div>

                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.shipping-zones.index.form.status')
                                </label>

                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input
                                        type="checkbox"
                                        class="peer sr-only"
                                        v-model="zone.status"
                                    />

                                    <span class="peer h-5 w-9 cursor-pointer rounded-full bg-gray-200 after:absolute after:top-0.5 after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:border-white dark:bg-gray-800 dark:after:border-white dark:after:bg-white dark:peer-checked:bg-gray-950 after:ltr:left-0.5 peer-checked:after:ltr:translate-x-full after:rtl:right-0.5 peer-checked:after:rtl:-translate-x-full"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </script>

    <script type="module">
        app.component('v-shipping-zone-form', {
            template: '#v-shipping-zone-form-template',

            data() {
                return {
                    isEdit: {{ $zone ? 'true' : 'false' }},

                    isSaving: false,

                    errors: {},

                    countries: @json(core()->countries()->map(fn ($country) => ['code' => $country->code, 'name' => $country->name])),

                    zone: @json($zone ? [
                        'name'             => $zone->name,
                        'is_rest_of_world' => (bool) $zone->is_rest_of_world,
                        'status'           => (bool) $zone->status,
                        'sort_order'       => $zone->sort_order,
                        'locations'        => $zone->locations->map(fn ($location) => [
                            'country_code' => $location->country_code,
                            'postcode'     => $location->postcode,
                        ])->values(),
                        'methods' => $zone->methods->map(fn ($method) => [
                            'type'             => $method->type,
                            'title'            => $method->title,
                            'rate'             => $method->rate,
                            'calculation_type' => $method->calculation_type,
                            'status'           => (bool) $method->status,
                        ])->values(),
                    ] : [
                        'name'             => '',
                        'is_rest_of_world' => false,
                        'status'           => true,
                        'sort_order'       => 0,
                        'locations'        => [],
                        'methods'          => [],
                    ]),
                };
            },

            methods: {
                addLocation() {
                    this.zone.locations.push({ country_code: '', postcode: '' });
                },

                removeLocation(index) {
                    this.zone.locations.splice(index, 1);
                },

                addMethod() {
                    this.zone.methods.push({
                        type: 'flat_rate',
                        title: '',
                        rate: 0,
                        calculation_type: 'per_order',
                        status: true,
                    });
                },

                removeMethod(index) {
                    this.zone.methods.splice(index, 1);
                },

                save() {
                    this.isSaving = true;
                    this.errors = {};

                    const request = this.isEdit
                        ? this.$axios.put("{{ $zone ? route('admin.settings.shipping_zones.update', $zone->id) : '' }}", this.zone)
                        : this.$axios.post("{{ route('admin.settings.shipping_zones.store') }}", this.zone);

                    request
                        .then((response) => {
                            this.isSaving = false;

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            window.location.href = response.data.redirect_url;
                        })
                        .catch((error) => {
                            this.isSaving = false;

                            if (error.response?.status === 422) {
                                this.errors = error.response.data.errors ?? {};
                            }
                        });
                },
            },
        });
    </script>
@endPushOnce
