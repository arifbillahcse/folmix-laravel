<?php

namespace Webkul\Product\Helpers;

use Illuminate\Support\Collection;
use Webkul\Attribute\Contracts\Attribute;
use Webkul\Product\Contracts\Product;
use Webkul\Product\Facades\ProductImage;
use Webkul\Product\Facades\ProductVideo;

class ConfigurableOption
{
    /**
     * Allowed Products.
     *
     * @return array
     */
    protected $allowedVariants = [];

    /**
     * Super Attributes
     *
     * @return array
     */
    protected $superAttributes = [];

    /**
     * Returns the allowed variants.
     *
     * @param  Product  $product
     * @return array
     */
    public function getAllowedVariants($product)
    {
        if (count($this->allowedVariants)) {
            return $this->allowedVariants;
        }

        $variantCollection = $product->variants()
            ->with([
                'parent',
                'attribute_values',
                'price_indices',
                'inventory_indices',
                'images',
                'videos',
            ])
            ->get();

        /**
         * Out-of-stock variants are kept in the option list (with an "in_stock"
         * flag the frontend uses to badge/disable them) rather than being
         * filtered out entirely - a shopper should be able to see that a
         * color/size exists even when it's currently unavailable. Only an
         * inactive (disabled) variant is excluded outright.
         */
        foreach ($variantCollection as $variant) {
            if ($variant->status) {
                $this->allowedVariants[] = $variant;
            }
        }

        return $this->allowedVariants;
    }

    /**
     * Returns true if at least one of the given variant product ids is
     * currently saleable (active and in stock).
     *
     * @param  array  $productIds
     * @return bool
     */
    protected function isAnySaleable(array $productIds)
    {
        foreach ($this->allowedVariants as $variant) {
            if (
                in_array($variant->id, $productIds)
                && $variant->isSaleable()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the allowed variants JSON.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    public function getConfigurationConfig($product)
    {
        $options = $this->getOptions($product, $this->getAllowedVariants($product));

        $config = [
            'attributes' => $this->getAttributesData($product, $options),
            'index' => $options['index'] ?? [],
            'variant_prices' => $this->getVariantPrices($product),
            'variant_images' => $this->getVariantImages($product),
            'variant_videos' => $this->getVariantVideos($product),
        ];

        return array_merge($config, $product->getTypeInstance()->getProductPrices());
    }

    /**
     * Get allowed attributes.
     *
     * @param  Product  $product
     * @return Collection
     */
    public function getAllowAttributes($product)
    {
        if (isset($this->superAttributes[$product->id])) {
            return $this->superAttributes[$product->id];
        }

        return $this->superAttributes[$product->id] = $product->super_attributes()
            ->with(['translations', 'options', 'options.translations'])
            ->get();
    }

    /**
     * Get configurable product options.
     *
     * @param  Product  $currentProduct
     * @param  array  $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];

        $allowAttributes = $this->getAllowAttributes($currentProduct);

        foreach ($allowedProducts as $product) {
            foreach ($allowAttributes as $productAttribute) {
                $productAttributeId = $productAttribute->id;

                $attributeValue = $product->{$productAttribute->code};

                $options[$productAttributeId][$attributeValue][] = $product->id;

                $options['index'][$product->id][$productAttributeId] = $attributeValue;
            }
        }

        return $options;
    }

    /**
     * Get product attributes.
     *
     * @param  Product  $product
     * @return array
     */
    public function getAttributesData($product, array $options = [])
    {
        $attributes = [];

        $allowAttributes = $this->getAllowAttributes($product);

        foreach ($allowAttributes as $attribute) {
            $attributes[] = [
                'id' => $attribute->id,
                'code' => $attribute->code,
                'label' => $attribute->name ? $attribute->name : $attribute->admin_name,
                'swatch_type' => $attribute->swatch_type,
                'options' => $this->getAttributeOptionsData($attribute, $options),
            ];
        }

        return $attributes;
    }

    /**
     * Get attribute options data.
     *
     * @param  Attribute  $attribute
     * @param  array  $options
     * @return array
     */
    protected function getAttributeOptionsData($attribute, $options)
    {
        $attributeOptionsData = [];

        foreach ($attribute->options->sortBy('sort_order') as $attributeOption) {
            $optionId = $attributeOption->id;

            if (! isset($options[$attribute->id][$optionId])) {
                continue;
            }

            $attributeOptionsData[] = [
                'id' => $optionId,
                'label' => $attributeOption->label ? $attributeOption->label : $attributeOption->admin_name,
                'swatch_value' => $attribute->swatch_type == 'image' ? $attributeOption->swatch_value_url : $attributeOption->swatch_value,
                'products' => $options[$attribute->id][$optionId],
                'in_stock' => $this->isAnySaleable($options[$attribute->id][$optionId]),
            ];
        }

        return $attributeOptionsData;
    }

    /**
     * Get product prices for configurable variations.
     *
     * @param  Product  $product
     * @return array
     */
    protected function getVariantPrices($product)
    {
        $prices = [];

        foreach ($this->getAllowedVariants($product) as $variant) {
            $prices[$variant->id] = $variant->getTypeInstance()->getProductPrices();
        }

        return $prices;
    }

    /**
     * Get product images for configurable variations.
     *
     * @param  Product  $product
     * @return array
     */
    protected function getVariantImages($product)
    {
        $images = [];

        foreach ($this->getAllowedVariants($product) as $variant) {
            $images[$variant->id] = ProductImage::getGalleryImages($variant);
        }

        return $images;
    }

    /**
     * Get product videos for configurable variations.
     *
     * @param  Product  $product
     * @return array
     */
    protected function getVariantVideos($product)
    {
        $videos = [];

        foreach ($this->getAllowedVariants($product) as $variant) {
            $videos[$variant->id] = ProductVideo::getVideos($variant);
        }

        return $videos;
    }
}
