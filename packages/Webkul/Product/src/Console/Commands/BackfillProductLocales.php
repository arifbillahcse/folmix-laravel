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
         * Only products that have a flat row in the source locale and NO flat
         * row in any other locale - i.e. exactly the "only translated into
         * one language" set a single-locale import leaves behind. Products
         * that already have real content in other locales are left alone.
         */
        $productIds = DB::table('products')
            ->whereIn('id', function ($query) use ($sourceLocale) {
                $query->select('product_id')
                    ->from('product_flat')
                    ->where('locale', $sourceLocale);
            })
            ->whereNotIn('id', function ($query) use ($sourceLocale) {
                $query->select('product_id')
                    ->from('product_flat')
                    ->where('locale', '!=', $sourceLocale);
            })
            ->pluck('id');

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
                ->with('attribute_family')
                ->findWhereIn('id', $chunk->all());

            foreach ($products as $product) {
                if (! array_key_exists($product->attribute_family_id, $familyAttributesCache)) {
                    $familyAttributesCache[$product->attribute_family_id] = $product->attribute_family->custom_attributes;
                }

                $this->productAttributeValueRepository->copyValuesToAllLocales(
                    ['locale' => $sourceLocale, 'channel' => $channelCode],
                    $product,
                    $familyAttributesCache[$product->attribute_family_id]
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
