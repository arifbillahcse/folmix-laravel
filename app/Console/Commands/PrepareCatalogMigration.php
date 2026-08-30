<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Core\Repositories\CoreConfigRepository;

/**
 * One-off command that prepares this store for the WooCommerce catalog
 * migration: creates the category tree and the configurable-product
 * attributes referenced by storage/app/import/bagisto_products.csv, before
 * that CSV is run through Admin > Settings > Data Transfer > Import.
 *
 * Idempotent: safe to run more than once, existing categories/attributes
 * are left untouched and only missing ones are created.
 */
class PrepareCatalogMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:prepare-migration
        {--categories= : Path to categories_tree.json (default: storage/app/import/categories_tree.json)}
        {--attributes= : Path to configurable_attributes.json (default: storage/app/import/configurable_attributes.json)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the category tree and configurable attributes needed before importing the migrated product catalog.';

    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected AttributeRepository $attributeRepository,
        protected AttributeFamilyRepository $attributeFamilyRepository,
        protected CoreConfigRepository $coreConfigRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $categoriesPath = $this->option('categories') ?: storage_path('app/import/categories_tree.json');
        $attributesPath = $this->option('attributes') ?: storage_path('app/import/configurable_attributes.json');

        if (! file_exists($categoriesPath)) {
            $this->error("Categories file not found: {$categoriesPath}");

            return self::FAILURE;
        }

        if (! file_exists($attributesPath)) {
            $this->error("Attributes file not found: {$attributesPath}");

            return self::FAILURE;
        }

        $this->createCategories(json_decode(file_get_contents($categoriesPath), true));

        $this->createConfigurableAttributes(json_decode(file_get_contents($attributesPath), true));

        $this->info('Done. You can now run the product import (Admin > Settings > Data Transfer > Import) using the "configurable" attribute family for the configurable products.');

        return self::SUCCESS;
    }

    /**
     * Create the top-level categories and their subcategories, skipping any
     * that already exist under the same parent.
     */
    protected function createCategories(array $tree): void
    {
        $root = $this->categoryRepository->findOneWhere(['parent_id' => null]);

        if (! $root) {
            $this->error('No root category found (category with parent_id = null). Aborting category creation.');

            return;
        }

        $position = 1;

        foreach ($tree['roots'] ?? [] as $topName) {
            $top = $this->firstOrCreateCategory($topName, $root->id, $position++);

            $childPosition = 1;

            foreach ($tree['children'][$topName] ?? [] as $childName) {
                $this->firstOrCreateCategory($childName, $top->id, $childPosition++);
            }
        }
    }

    protected function firstOrCreateCategory(string $name, int $parentId, int $position)
    {
        $existing = $this->categoryRepository
            ->whereTranslation('name', $name)
            ->where('parent_id', $parentId)
            ->first();

        if ($existing) {
            $this->line("  category already exists, skipping: {$name}");

            return $existing;
        }

        $slug = $this->slugify($name);

        $category = $this->categoryRepository->create([
            'name' => $name,
            'slug' => $slug,
            'parent_id' => $parentId,
            'position' => $position,
            'status' => 1,
            'display_mode' => 'products_only',
        ]);

        $this->info("  created category: {$name} (id {$category->id})");

        return $category;
    }

    /**
     * Create the select attributes discovered from the WooCommerce export,
     * mark them configurable, seed their options, and attach them to a new
     * attribute group on the "default" attribute family so they can be used
     * by configurable products without needing a whole new family.
     */
    protected function createConfigurableAttributes(array $attributeDefs): void
    {
        $family = $this->attributeFamilyRepository->findOneByField('code', 'default');

        if (! $family) {
            $this->error('Attribute family with code "default" not found. Aborting attribute creation.');

            return;
        }

        $attributeGroup = $family->attribute_groups()
            ->where('code', 'configurable-options')
            ->first();

        if (! $attributeGroup) {
            $attributeGroup = $family->attribute_groups()->create([
                'code' => 'configurable-options',
                'name' => 'Configurable Options',
                'column' => 1,
                'position' => $family->attribute_groups()->max('position') + 1,
                'is_user_defined' => 1,
            ]);

            $this->info('  created attribute group "Configurable Options" on the default family');
        }

        $position = ($attributeGroup->custom_attributes()->max('attribute_group_mappings.position') ?? 0) + 1;

        foreach ($attributeDefs as $def) {
            $code = $def['code'];

            $attribute = $this->attributeRepository->findOneByField('code', $code);

            if (! $attribute) {
                $attribute = $this->attributeRepository->create([
                    'code' => $code,
                    'admin_name' => $def['name'],
                    'type' => 'select',
                    'position' => 0,
                    'is_required' => 0,
                    'is_unique' => 0,
                    'value_per_locale' => 0,
                    'value_per_channel' => 0,
                    'is_filterable' => 1,
                    'is_configurable' => 1,
                    'is_visible_on_front' => 1,
                    'is_user_defined' => 1,
                    'is_comparable' => 0,
                    'options' => array_map(fn ($label, $i) => [
                        'admin_name' => $label,
                        'label' => $label,
                        'sort_order' => $i + 1,
                    ], $def['options'], array_keys($def['options'])),
                ]);

                $this->info("  created attribute: {$def['name']} ({$code}) with ".count($def['options']).' options');
            } else {
                $this->line("  attribute already exists, skipping: {$def['name']} ({$code})");
            }

            /**
             * Query the pivot table directly rather than through the
             * custom_attributes() relation: that relation has an
             * orderBy('pivot_position') baked in, which breaks exists()
             * queries (the pivot alias isn't available in that stripped-down
             * SQL, causing "Unknown column 'pivot_position'").
             */
            $alreadyInGroup = DB::table('attribute_group_mappings')
                ->where('attribute_group_id', $attributeGroup->id)
                ->where('attribute_id', $attribute->id)
                ->exists();

            if (! $alreadyInGroup) {
                $attributeGroup->custom_attributes()->save($attribute, ['position' => $position++]);
            }
        }
    }

    protected function slugify(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/[^\p{L}\p{N}]+/u', '-', $value);
        $value = trim($value, '-');

        return $value !== '' ? $value : 'category';
    }
}
