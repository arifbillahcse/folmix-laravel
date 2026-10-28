<?php

namespace Webkul\Product\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Repositories\ProductAttributeValueRepository;
use Webkul\Product\Repositories\ProductRepository;

class BackfillProductLocales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:backfill-locales {--channel=default} {--source-locale=en}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy translatable attribute values (name, description, url_key, meta fields, etc.) from --source-locale to every other locale the channel supports, for products that currently only have a product_flat row in that one locale. Meant for a bulk import (e.g. CSV) that only populated a single locale.';

    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductAttributeValueRepository $productAttributeValueRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $channelCode = $this->option('channel');
        $sourceLocale = $this->option('source-locale');

        /**
         * Checked at the EAV (product_attribute_values) level, not
         * product_flat: the flat indexer creates an empty flat row for every
         * locale regardless of whether real translated values exist, so flat
         * row presence can't tell "translated" from "untranslated". A
         * product needs backfilling when it has a url_key value in the
         * source locale but none in any other locale - i.e. it was never
         * actually translated, only flat-indexed. Products with real
         * content in other locales are left alone.
         */
        $urlKeyAttributeId = DB::table('attributes')->where('code', 'url_key')->value('id');

        if (! $urlKeyAttributeId) {
            $this->components->error("Could not find an attribute with code 'url_key'.");

            return self::FAILURE;
        }

        $productIds = DB::table('product_attribute_values')
            ->where('attribute_id', $urlKeyAttributeId)
            ->where('locale', $sourceLocale)
            ->whereNotIn('product_id', function ($query) use ($urlKeyAttributeId, $sourceLocale) {
                $query->select('product_id')
                    ->from('product_attribute_values')
                    ->where('attribute_id', $urlKeyAttributeId)
                    ->where('locale', '!=', $sourceLocale);
            })
            ->pluck('product_id');

        if ($productIds->isEmpty()) {
            $this->components->info('No products found that are missing other-locale data. Nothing to do.');

            return self::SUCCESS;
        }

        $this->components->info("Found {$productIds->count()} products with only '{$sourceLocale}' data. Copying to all locales...");

        $bar = $this->output->createProgressBar($productIds->count());
        $bar->start();

        $familyAttributesCache = [];

        foreach ($productIds->chunk(200) as $chunk) {
            $products = $this->productRepository
                ->with(['attribute_family', 'attribute_values'])
                ->findWhereIn('id', $chunk->all());

            foreach ($products as $product) {
                if (! array_key_exists($product->attribute_family_id, $familyAttributesCache)) {
                    $familyAttributesCache[$product->attribute_family_id] = $product->attribute_family->custom_attributes;
                }

                $familyAttributes = $familyAttributesCache[$product->attribute_family_id];

                $localeAttributes = $familyAttributes->filter(
                    fn ($attribute) => $attribute->value_per_locale && ! in_array($attribute->type, ['image', 'file'])
                );

                /**
                 * copyValuesToAllLocales() reuses whatever values are already
                 * in $data (it was written for the admin edit form, where
                 * $data is the just-submitted request) - it does not fetch
                 * the source locale's existing values itself. So the actual
                 * en value for each translatable attribute has to be looked
                 * up here and put into $data before calling it, or every
                 * field is silently skipped.
                 */
                $data = ['locale' => $sourceLocale, 'channel' => $channelCode];

                foreach ($localeAttributes as $attribute) {
                    $existingValue = $product->attribute_values
                        ->where('attribute_id', $attribute->id)
                        ->where('locale', $sourceLocale)
                        ->first();

                    if ($existingValue) {
                        $data[$attribute->code] = $existingValue->{$attribute->column_name};
                    }
                }

                $this->productAttributeValueRepository->copyValuesToAllLocales(
                    $data,
                    $product,
                    $familyAttributes
                );

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->components->info('Done. Now run: php artisan indexer:index --mode=full');

        return self::SUCCESS;
    }
}
