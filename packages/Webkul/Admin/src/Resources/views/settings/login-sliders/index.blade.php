<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.login-sliders.index.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.settings.login_sliders.create.before') !!}

    <v-login-sliders>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.settings.login-sliders.index.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                @if (bouncer()->hasPermission('settings.login_sliders.create'))
                    <button
                        type="button"
                        class="primary-button"
                    >
                        @lang('admin::app.settings.login-sliders.index.create-btn')
                    </button>
                @endif
            </div>
        </div>

        <!-- DataGrid Shimmer -->
        <x-admin::shimmer.datagrid />
    </v-login-sliders>

    {!! view_render_event('bagisto.admin.settings.login_sliders.create.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-login-sliders-template"
        >
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.login-sliders.index.title')
                </p>

                <div class="flex items-center gap-x-2.5">
                    <!-- Slider Create Button -->
                    @if (bouncer()->hasPermission('settings.login_sliders.create'))
                        <button
                            type="button"
                            class="primary-button"
                            @click="selectedSlider=0;resetForm();$refs.sliderUpdateOrCreateModal.toggle()"
                        >
                            @lang('admin::app.settings.login-sliders.index.create-btn')
                        </button>
                    @endif
                </div>
            </div>

            <x-admin::datagrid
                :src="route('admin.settings.login_sliders.index')"
                ref="datagrid"
            >
                <!-- DataGrid Body -->
                <template #body="{
                    isLoading,
                    available,
                    applied,
                    selectAll,
                    sort,
                    performAction
                }">
                    <template v-if="isLoading">
                        <x-admin::shimmer.datagrid.table.body />
                    </template>

                    <template v-else>
                        <div
                            v-for="record in available.records"
                            class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"
                            :style="`grid-template-columns: repeat(${gridsCount}, minmax(0, 1fr))`"
                        >
                            <!-- ID -->
                            <p>@{{ record.id }}</p>

                            <!-- Title -->
                            <p>@{{ record.title }}</p>

                            <!-- Link -->
                            <p class="truncate">@{{ record.link }}</p>

                            <!-- Sort Order -->
                            <p>@{{ record.sort_order }}</p>

                            <!-- Status -->
                            <p>@{{ record.status }}</p>

                            <!-- Actions -->
                            <div class="flex justify-end">
                                @if (bouncer()->hasPermission('settings.login_sliders.edit'))
                                    <a @click="selectedSlider=1; editModal(record.actions.find(action => action.index === 'edit')?.url)">
                                        <span
                                            :class="record.actions.find(action => action.index === 'edit')?.icon"
                                            class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                        >
                                        </span>
                                    </a>
                                @endif

                                @if (bouncer()->hasPermission('settings.login_sliders.delete'))
                                    <a @click="performAction(record.actions.find(action => action.index === 'delete'))">
                                        <span
                                            :class="record.actions.find(action => action.index === 'delete')?.icon"
                                            class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                        >
                                        </span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </template>
                </template>
            </x-admin::datagrid>

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form
                    @submit="handleSubmit($event, updateOrCreate)"
                    ref="createSliderForm"
                >
                    {!! view_render_event('bagisto.admin.settings.login_sliders.create_form_controls.before') !!}

                    <x-admin::modal ref="sliderUpdateOrCreateModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                <span v-if="selectedSlider">
                                    @lang('admin::app.settings.login-sliders.index.edit.title')
                                </span>

                                <span v-else>
                                    @lang('admin::app.settings.login-sliders.index.create.title')
                                </span>
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>
                            {!! view_render_event('bagisto.admin.settings.login_sliders.create.before') !!}

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="id"
                                v-model="slider.id"
                            />

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.login-sliders.index.create.title-field')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="title"
                                    name="title"
                                    v-model="slider.title"
                                    :label="trans('admin::app.settings.login-sliders.index.create.title-field')"
                                    :placeholder="trans('admin::app.settings.login-sliders.index.create.title-field')"
                                />

                                <x-admin::form.control-group.error control-name="title" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.login-sliders.index.create.link')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="link"
                                    name="link"
                                    rules="url"
                                    v-model="slider.link"
                                    :label="trans('admin::app.settings.login-sliders.index.create.link')"
                                    placeholder="https://custom.folmix.com/customer/register"
                                />

                                <x-admin::form.control-group.error control-name="link" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.login-sliders.index.create.sort-order')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="sort_order"
                                    name="sort_order"
                                    rules="numeric"
                                    v-model="slider.sort_order"
                                    :label="trans('admin::app.settings.login-sliders.index.create.sort-order')"
                                    placeholder="0"
                                />

                                <x-admin::form.control-group.error control-name="sort_order" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.login-sliders.index.create.image')
                                </x-admin::form.control-group.label>

                                <div class="hidden">
                                    <x-admin::media.images
                                        name="image"
                                        ::uploaded-images='slider.image'
                                    />
                                </div>

                                <v-media-images
                                    name="image"
                                    :uploaded-images='slider.image'
                                >
                                </v-media-images>

                                <x-admin::form.control-group.error control-name="image" />
                            </x-admin::form.control-group>

                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.login-sliders.index.create.image-size')
                            </p>

                            <!-- Status -->
                            <x-admin::form.control-group class="!mb-0 mt-5">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.login-sliders.index.create.status')
                                </x-admin::form.control-group.label>

                                <label class="relative inline-flex cursor-pointer items-center">
                                    <v-field
                                        type="checkbox"
                                        name="status"
                                        class="hidden"
                                        v-slot="{ field }"
                                        v-model="slider.status"
                                    >
                                        <input
                                            type="checkbox"
                                            name="status"
                                            id="status"
                                            class="peer sr-only"
                                            v-bind="field"
                                            :checked="slider.status"
                                        />
                                    </v-field>

                                    <label
                                        class="peer h-5 w-9 cursor-pointer rounded-full bg-gray-200 after:absolute after:top-0.5 after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-blue-300 dark:bg-gray-800 dark:after:border-white dark:after:bg-white dark:peer-checked:bg-gray-950 after:ltr:left-0.5 peer-checked:after:ltr:translate-x-full after:rtl:right-0.5 peer-checked:after:rtl:-translate-x-full"
                                        for="status"
                                    ></label>
                                </label>

                                <x-admin::form.control-group.error control-name="status" />
                            </x-admin::form.control-group>

                            {!! view_render_event('bagisto.admin.settings.login_sliders.create.after') !!}
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <!-- Save Button -->
                            <x-admin::button
                                button-type="button"
                                class="primary-button"
                                :title="trans('admin::app.settings.login-sliders.index.create.save-btn')"
                                ::loading="isLoading"
                                ::disabled="isLoading"
                            />
                        </x-slot>
                    </x-admin::modal>

                    {!! view_render_event('bagisto.admin.settings.login_sliders.create_form_controls.after') !!}
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-login-sliders', {
                template: '#v-login-sliders-template',

                data() {
                    return {
                        slider: {
                            image: [],
                            status: true,
                        },

                        isLoading: false,

                        selectedSlider: 0,
                    }
                },

                computed: {
                    gridsCount() {
                        let count = this.$refs.datagrid.available.columns.length;

                        if (this.$refs.datagrid.available.actions.length) {
                            ++count;
                        }

                        if (this.$refs.datagrid.available.massActions.length) {
                            ++count;
                        }

                        return count;
                    },
                },

                methods: {
                    updateOrCreate(params, { resetForm, setErrors }) {
                        this.isLoading = true;

                        let formData = new FormData(this.$refs.createSliderForm);

                        if (params.id) {
                            formData.append('_method', 'put');
                        }

                        this.$axios.post(params.id ? "{{ route('admin.settings.login_sliders.update') }}" : "{{ route('admin.settings.login_sliders.store') }}", formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        })
                        .then((response) => {
                            this.isLoading = false;

                            this.$refs.sliderUpdateOrCreateModal.close();

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$refs.datagrid.get();

                            resetForm();
                        })
                        .catch(error => {
                            this.isLoading = false;

                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                    },

                    editModal(url) {
                        this.$axios.get(url)
                            .then((response) => {
                                this.slider = {
                                    ...response.data.data,
                                    image: response.data.data.image
                                        ? [{ id: 'image_url', url: response.data.data.image_url }]
                                        : [],
                                };

                                this.$refs.sliderUpdateOrCreateModal.toggle();
                            })
                    },

                    resetForm() {
                        this.slider = {
                            image: [],
                            status: true,
                        };
                    }
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
