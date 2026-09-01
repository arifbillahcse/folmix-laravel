@props([
    'name' => 'query',
    'class' => '',
    'placeholder' => '',
    'ariaLabel' => '',
    'toolparamdescription' => '',
])

@push('styles')
    <style>
        /*
         * Plain CSS (not new Tailwind utilities) because this theme's
         * compiled CSS isn't rebuilt from source on deploy - classes like
         * top-full, z-30 and the arbitrary max-h-[70vh]/shadow-[...] never
         * existed in the shipped stylesheet, so the dropdown had no top
         * offset or stacking order and visually swallowed the input.
         */
        .folmix-search-suggestions-results {
            position: absolute;
            top: 100%;
            inset-inline-start: 0;
            z-index: 30;
            max-height: 70vh;
            box-shadow: 0px 10px 84px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush

<v-search-suggestions
    name="{{ $name }}"
    input-class="{{ $class }}"
    placeholder="{{ $placeholder }}"
    aria-label="{{ $ariaLabel ?: $placeholder }}"
    toolparamdescription="{{ $toolparamdescription }}"
    minlength="{{ core()->getConfigData('catalog.products.search.min_query_length') }}"
    maxlength="{{ core()->getConfigData('catalog.products.search.max_query_length') }}"
    initial-query="{{ request('query') }}"
    search-url="{{ route('shop.api.products.index') }}"
    results-url="{{ route('shop.search.index') }}"
></v-search-suggestions>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-search-suggestions-template"
    >
        <input
            type="text"
            ref="input"
            :name="name"
            v-model="query"
            @input="onInput"
            @focus="onFocus"
            @keydown.down.prevent="highlightNext"
            @keydown.up.prevent="highlightPrev"
            @keydown.enter="onEnter"
            @keydown.esc="close"
            :class="inputClass"
            :minlength="minlength"
            :maxlength="maxlength"
            :placeholder="placeholder"
            :aria-label="ariaLabel"
            :toolparamdescription="toolparamdescription"
            aria-required="true"
            autocomplete="off"
            pattern="[^\\]+"
            required
        >

        <div
            v-if="isOpen && (isLoading || suggestions.length || hasSearched)"
            class="folmix-search-suggestions-results w-full overflow-y-auto rounded-lg border border-zinc-200 bg-white"
        >
            <template v-if="isLoading">
                <p class="p-4 text-sm text-zinc-500">
                    @lang('shop::app.components.layouts.search-suggestions.searching')
                </p>
            </template>

            <template v-else-if="! suggestions.length">
                <p class="p-4 text-sm text-zinc-500">
                    @lang('shop::app.components.layouts.search-suggestions.no-results')
                </p>
            </template>

            <template v-else>
                <a
                    v-for="(product, index) in suggestions"
                    :key="product.id"
                    :href="productUrl(product)"
                    class="flex items-center gap-3 p-3 hover:bg-zinc-50"
                    :class="{ 'bg-zinc-50': index === highlightedIndex }"
                >
                    <img
                        :src="product.base_image.small_image_url"
                        :alt="product.name"
                        class="h-12 w-12 shrink-0 rounded object-cover"
                    >

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm">@{{ product.name }}</p>

                        <p
                            class="text-sm font-semibold text-navyBlue"
                            v-html="product.price_html"
                        ></p>
                    </div>
                </a>

                <a
                    :href="viewAllUrl"
                    class="block border-t p-3 text-center text-sm font-medium text-navyBlue hover:bg-zinc-50"
                >
                    @lang('shop::app.components.layouts.search-suggestions.view-all-results-for')

                    "@{{ query }}"
                </a>
            </template>
        </div>
    </script>

    <script type="module">
        app.component('v-search-suggestions', {
            template: '#v-search-suggestions-template',

            props: {
                name: String,
                inputClass: String,
                placeholder: String,
                ariaLabel: String,
                toolparamdescription: String,
                minlength: [String, Number],
                maxlength: [String, Number],
                initialQuery: String,
                searchUrl: String,
                resultsUrl: String,
            },

            data() {
                return {
                    query: this.initialQuery || '',
                    suggestions: [],
                    isLoading: false,
                    isOpen: false,
                    hasSearched: false,
                    highlightedIndex: -1,
                    debounceTimer: null,
                };
            },

            computed: {
                viewAllUrl() {
                    return `${this.resultsUrl}?query=${encodeURIComponent(this.query)}`;
                },
            },

            created() {
                window.addEventListener('click', this.handleClickOutside);
            },

            beforeUnmount() {
                window.removeEventListener('click', this.handleClickOutside);
            },

            methods: {
                onInput() {
                    clearTimeout(this.debounceTimer);

                    const minLength = parseInt(this.minlength, 10) || 2;

                    if (this.query.trim().length < minLength) {
                        this.suggestions = [];
                        this.isOpen = false;
                        this.hasSearched = false;

                        return;
                    }

                    this.debounceTimer = setTimeout(() => this.fetchSuggestions(), 300);
                },

                fetchSuggestions() {
                    this.isLoading = true;
                    this.isOpen = true;
                    this.highlightedIndex = -1;

                    const requestedQuery = this.query;

                    this.$axios.get(this.searchUrl, {
                        params: {
                            query: requestedQuery,
                            limit: 6,
                            suggest: 0,
                        },
                    }).then((response) => {
                        /**
                         * Ignore stale responses from a previous, slower
                         * request if the query has since changed.
                         */
                        if (requestedQuery !== this.query) {
                            return;
                        }

                        this.suggestions = response.data.data ?? [];
                        this.isLoading = false;
                        this.hasSearched = true;
                    }).catch(() => {
                        if (requestedQuery !== this.query) {
                            return;
                        }

                        this.suggestions = [];
                        this.isLoading = false;
                        this.hasSearched = true;
                    });
                },

                onFocus() {
                    if (this.suggestions.length || this.hasSearched) {
                        this.isOpen = true;
                    }
                },

                close() {
                    this.isOpen = false;
                },

                handleClickOutside(event) {
                    if (! this.$refs.input.parentElement.contains(event.target)) {
                        this.isOpen = false;
                    }
                },

                highlightNext() {
                    if (this.highlightedIndex < this.suggestions.length - 1) {
                        this.highlightedIndex++;
                    }
                },

                highlightPrev() {
                    if (this.highlightedIndex > -1) {
                        this.highlightedIndex--;
                    }
                },

                onEnter(event) {
                    if (this.highlightedIndex > -1 && this.suggestions[this.highlightedIndex]) {
                        event.preventDefault();

                        window.location.href = this.productUrl(this.suggestions[this.highlightedIndex]);
                    }
                },

                productUrl(product) {
                    return `{{ route('shop.product_or_category.index', ':slug') }}`.replace(':slug', product.url_key);
                },
            },
        });
    </script>
@endPushOnce
