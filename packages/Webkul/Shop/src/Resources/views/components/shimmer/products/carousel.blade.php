<div class="container mt-20 max-lg:px-8 max-md:mt-8 max-sm:mt-7 max-sm:!px-4">
    <div class="flex items-center justify-between">
        <h3 class="shimmer h-8 w-[200px] max-sm:h-7"></h3>

        <div class="flex items-center justify-between gap-8 max-lg:hidden">
            <span
                class="shimmer inline-block h-6 w-6"
                role="presentation"
            ></span>

            <span
                class="shimmer inline-block h-6 w-6 max-sm:hidden"
                role="presentation"
            ></span>
        </div>

        <div class="shimmer h-7 w-24 max-sm:h-5 max-sm:w-[68px] lg:hidden"></div>
    </div>

    <div class="folmix-product-carousel-scroller scrollbar-hide">
        <x-shop::shimmer.products.cards.grid
            class="folmix-product-carousel-item"
            :count="5"
        />
    </div>

    @if ($navigationLink)
        <a
            class="shimmer mx-auto mt-16 block h-12 w-[150.172px] rounded-2xl max-md:hidden"
            role="button"
            aria-label="Show more products"
        ></a>
    @endif
</div>
