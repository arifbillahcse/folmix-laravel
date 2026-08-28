<v-promo-banner :errors="errors">
    <x-admin::shimmer.settings.themes.image-carousel />
</v-promo-banner>

<!-- Promo Banner Vue Component -->
@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-promo-banner-template"
    >
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <!-- Heading Block -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-2.5 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.themes.edit.promo-banner-heading-block')
                </p>

                <x-admin::form.control-group class="mb-2.5 pt-2">
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.settings.themes.edit.promo-banner-heading')
                    </x-admin::form.control-group.label>

                    <v-field
                        type="text"
                        name="options[heading]"
                        value="{{ $promoBanner['heading'] ?? '' }}"
                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        rules="required"
                        label="@lang('admin::app.settings.themes.edit.promo-banner-heading')"
                        placeholder="@lang('admin::app.settings.themes.edit.promo-banner-heading')"
                    >
                    </v-field>
                </x-admin::form.control-group>

                <x-admin::form.control-group class="mb-2.5">
                    <x-admin::form.control-group.label>
                        @lang('admin::app.settings.themes.edit.promo-banner-text')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="textarea"
                        name="options[text]"
                        value="{{ $promoBanner['text'] ?? '' }}"
                        :label="trans('admin::app.settings.themes.edit.promo-banner-text')"
                    />
                </x-admin::form.control-group>

                <div class="grid grid-cols-2 gap-2.5">
                    <x-admin::form.control-group class="mb-0">
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.themes.edit.promo-banner-button-text')
                        </x-admin::form.control-group.label>

                        <v-field
                            type="text"
                            name="options[button_text]"
                            value="{{ $promoBanner['button_text'] ?? '' }}"
                            class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                            label="@lang('admin::app.settings.themes.edit.promo-banner-button-text')"
                            placeholder="@lang('admin::app.settings.themes.edit.promo-banner-button-text')"
                        >
                        </v-field>
                    </x-admin::form.control-group>

                    <x-admin::form.control-group class="mb-0">
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.themes.edit.link')
                        </x-admin::form.control-group.label>

                        <v-field
                            type="text"
                            name="options[button_link]"
                            value="{{ $promoBanner['button_link'] ?? '' }}"
                            class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                            label="@lang('admin::app.settings.themes.edit.link')"
                            placeholder="@lang('admin::app.settings.themes.edit.link')"
                        >
                        </v-field>
                    </x-admin::form.control-group>
                </div>
            </div>

            <!-- Cards -->
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-2.5 flex items-center justify-between gap-x-2.5">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.promo-banner-cards')
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.promo-banner-cards-description')
                        </p>
                    </div>

                    <div
                        class="secondary-button"
                        @click="create"
                    >
                        @lang('admin::app.settings.themes.edit.promo-banner-add-card-btn')
                    </div>
                </div>

                <!-- Cards List -->
                <div
                    class="grid"
                    v-if="cards.length"
                >
                    <draggable
                        ghost-class="draggable-ghost"
                        handle=".icon-drag"
                        v-bind="{animation: 200}"
                        :list="cards"
                        item-key="_key"
                    >
                        <template #item="{ element, index }">
                            <div class="flex justify-between gap-2.5 border-b border-slate-300 p-4 last:border-b-0 dark:border-gray-800">
                                <!-- Hidden Inputs -->
                                <input type="hidden" :name="'options[cards][' + index + '][title]'" :value="element.title" />
                                <input type="hidden" :name="'options[cards][' + index + '][text]'" :value="element.text" />
                                <input type="hidden" :name="'options[cards][' + index + '][button_text]'" :value="element.button_text" />
                                <input type="hidden" :name="'options[cards][' + index + '][button_link]'" :value="element.button_link" />
                                <input type="hidden" :name="'options[cards][' + index + '][existing_image]'" :value="element.image" v-if="! element.file" />
                                <input
                                    type="file"
                                    class="hidden"
                                    :name="'options[cards][' + index + '][image]'"
                                    :ref="'imageInput_' + element._key"
                                />

                                <div class="flex gap-2.5">
                                    <i class="icon-drag cursor-grab text-xl text-gray-600 transition-all dark:text-gray-300"></i>

                                    <div
                                        class="relative h-[60px] max-h-[60px] w-full max-w-[60px] overflow-hidden rounded"
                                        :class="{'border border-dashed border-gray-300 dark:border-gray-800': ! element.previewUrl}"
                                    >
                                        <img v-if="element.previewUrl" :src="element.previewUrl" />
                                    </div>

                                    <div class="grid place-content-start gap-1.5">
                                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                                            @{{ element.title || '—' }}
                                        </p>

                                        <p class="text-gray-600 dark:text-gray-300" v-if="element.text">
                                            @{{ element.text }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid place-content-start gap-1 ltr:text-right rtl:text-left">
                                    <p
                                        class="cursor-pointer text-blue-600 transition-all hover:underline"
                                        @click="edit(element, index)"
                                    >
                                        @lang('admin::app.settings.themes.edit.edit')
                                    </p>

                                    <p
                                        class="cursor-pointer text-red-600 transition-all hover:underline"
                                        @click="remove(index)"
                                    >
                                        @lang('admin::app.settings.themes.edit.delete')
                                    </p>
                                </div>
                            </div>
                        </template>
                    </draggable>
                </div>

                <div
                    class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                    v-else
                >
                    <img
                        class="h-[120px] w-[120px] p-2 dark:mix-blend-exclusion dark:invert"
                        src="{{ bagisto_asset('images/empty-placeholders/default.svg') }}"
                        alt="@lang('admin::app.settings.themes.edit.promo-banner-cards')"
                    >

                    <div class="flex flex-col items-center gap-1.5">
                        <p class="text-base font-semibold text-gray-400">
                            @lang('admin::app.settings.themes.edit.promo-banner-cards')
                        </p>

                        <p class="text-gray-400">
                            @lang('admin::app.settings.themes.edit.promo-banner-cards-description')
                        </p>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Card Modal -->
            <x-admin::modal ref="cardModal">
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        <template v-if="! isUpdating">
                            @lang('admin::app.settings.themes.edit.promo-banner-add-card-btn')
                        </template>

                        <template v-else>
                            @lang('admin::app.settings.themes.edit.edit')
                        </template>
                    </p>
                </x-slot>

                <x-slot:content>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.themes.edit.promo-banner-card-title')
                        </x-admin::form.control-group.label>

                        <input
                            type="text"
                            v-model="selectedCard.title"
                            class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            placeholder="@lang('admin::app.settings.themes.edit.promo-banner-card-title')"
                        />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.themes.edit.promo-banner-text')
                        </x-admin::form.control-group.label>

                        <textarea
                            v-model="selectedCard.text"
                            class="flex min-h-[70px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            placeholder="@lang('admin::app.settings.themes.edit.promo-banner-text')"
                        ></textarea>
                    </x-admin::form.control-group>

                    <div class="grid grid-cols-2 gap-2.5">
                        <x-admin::form.control-group class="mb-0">
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.themes.edit.promo-banner-button-text')
                            </x-admin::form.control-group.label>

                            <input
                                type="text"
                                v-model="selectedCard.button_text"
                                class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                placeholder="@lang('admin::app.settings.themes.edit.promo-banner-button-text')"
                            />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group class="mb-0">
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.themes.edit.link')
                            </x-admin::form.control-group.label>

                            <input
                                type="text"
                                v-model="selectedCard.button_link"
                                class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                placeholder="@lang('admin::app.settings.themes.edit.link')"
                            />
                        </x-admin::form.control-group>
                    </div>

                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.themes.edit.promo-banner-card-image')
                        </x-admin::form.control-group.label>

                        <input
                            type="file"
                            accept="image/*"
                            ref="modalImageInput"
                            @change="onImageSelected"
                        />

                        <img
                            v-if="selectedCard.previewUrl"
                            :src="selectedCard.previewUrl"
                            class="mt-2.5 h-[90px] w-[90px] rounded object-cover"
                        />
                    </x-admin::form.control-group>
                </x-slot>

                <x-slot:footer>
                    <button
                        type="button"
                        class="primary-button justify-center"
                        @click="save"
                    >
                        @lang('admin::app.settings.themes.edit.save-btn')
                    </button>
                </x-slot>
            </x-admin::modal>
        </div>
    </script>

    <script type="module">
        app.component('v-promo-banner', {
            template: '#v-promo-banner-template',

            props: ['errors'],

            data() {
                return {
                    cards: (@json($promoBanner['cards'] ?? [])).map((card, index) => ({
                        ...card,
                        _key: 'card_' + index,
                        file: null,
                        previewUrl: card.image ? '{{ config('app.url') }}/' + card.image : null,
                    })),

                    selectedCard: {},

                    selectedCardIndex: null,

                    isUpdating: false,

                    cardSequence: 0,
                };
            },

            methods: {
                create() {
                    this.openCardModal();
                },

                edit(card, index) {
                    this.openCardModal(card, index);
                },

                openCardModal(card = null, index = null) {
                    if (card) {
                        this.isUpdating = true;
                        this.selectedCardIndex = index;
                        this.selectedCard = { ...card };
                    } else {
                        this.isUpdating = false;
                        this.selectedCardIndex = null;
                        this.selectedCard = { title: '', text: '', button_text: '', button_link: '', image: null, file: null, previewUrl: null };
                    }

                    this.$refs.cardModal.toggle();
                },

                onImageSelected() {
                    const file = this.$refs.modalImageInput.files[0];

                    if (! file) {
                        return;
                    }

                    this.selectedCard.file = file;

                    const reader = new FileReader();

                    reader.onload = (e) => {
                        this.selectedCard.previewUrl = e.target.result;
                    };

                    reader.readAsDataURL(file);
                },

                save() {
                    const card = {
                        ...this.selectedCard,
                        _key: this.isUpdating ? this.selectedCard._key : 'card_new_' + (this.cardSequence++),
                    };

                    if (this.isUpdating) {
                        this.cards[this.selectedCardIndex] = card;
                    } else {
                        this.cards.push(card);
                    }

                    this.$nextTick(() => {
                        if (card.file) {
                            this.setFile(card._key, card.file);
                        }
                    });

                    this.$refs.cardModal.toggle();
                },

                setFile(key, file) {
                    const input = this.$refs['imageInput_' + key];

                    if (! input) {
                        return;
                    }

                    const dataTransfer = new DataTransfer();

                    dataTransfer.items.add(file);

                    (Array.isArray(input) ? input[0] : input).files = dataTransfer.files;
                },

                remove(index) {
                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            this.cards.splice(index, 1);
                        },
                    });
                },
            },
        });
    </script>
@endPushOnce
