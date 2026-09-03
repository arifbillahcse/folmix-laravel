<?php

namespace Webkul\Shipping\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Shipping\Contracts\ShippingZone;

class ShippingZoneRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return ShippingZone::class;
    }

    /**
     * Create a zone along with its locations and methods.
     *
     * @return \Webkul\Shipping\Models\ShippingZone
     */
    public function create(array $data)
    {
        $zone = parent::create([
            'name'             => $data['name'],
            'is_rest_of_world' => ! empty($data['is_rest_of_world']),
            'sort_order'       => $data['sort_order'] ?? 0,
            'status'           => ! empty($data['status']),
        ]);

        $this->syncLocations($zone, $data['locations'] ?? []);

        $this->syncMethods($zone, $data['methods'] ?? []);

        return $zone;
    }

    /**
     * Update a zone along with its locations and methods.
     *
     * @param  int  $id
     * @return \Webkul\Shipping\Models\ShippingZone
     */
    public function update(array $data, $id)
    {
        $zone = parent::update([
            'name'             => $data['name'],
            'is_rest_of_world' => ! empty($data['is_rest_of_world']),
            'sort_order'       => $data['sort_order'] ?? 0,
            'status'           => ! empty($data['status']),
        ], $id);

        $zone->locations()->delete();

        $this->syncLocations($zone, $data['locations'] ?? []);

        $zone->methods()->delete();

        $this->syncMethods($zone, $data['methods'] ?? []);

        return $zone;
    }

    /**
     * Replace a zone's locations.
     *
     * @return void
     */
    protected function syncLocations($zone, array $locations)
    {
        foreach ($locations as $location) {
            if (empty($location['country_code'])) {
                continue;
            }

            $zone->locations()->create([
                'country_code' => $location['country_code'],
                'postcode'     => $location['postcode'] ?? null,
            ]);
        }
    }

    /**
     * Replace a zone's shipping methods.
     *
     * @return void
     */
    protected function syncMethods($zone, array $methods)
    {
        foreach ($methods as $index => $method) {
            if (empty($method['title']) || empty($method['type'])) {
                continue;
            }

            $zone->methods()->create([
                'type'             => $method['type'],
                'title'            => $method['title'],
                'rate'             => $method['type'] === 'flat_rate' ? ($method['rate'] ?? 0) : null,
                'calculation_type' => $method['calculation_type'] ?? 'per_order',
                'sort_order'       => $index,
                'status'           => ! empty($method['status']),
            ]);
        }
    }

    /**
     * Find the first active zone whose locations match the given
     * country/postcode, evaluated in sort_order (lowest first).
     * The rest-of-world zone (if any) is always checked last.
     *
     * @param  string  $countryCode
     * @param  string|null  $postcode
     * @return \Webkul\Shipping\Models\ShippingZone|null
     */
    public function findMatchingZone($countryCode, $postcode = null)
    {
        if (! $countryCode) {
            return null;
        }

        $zones = $this->model
            ->with('locations')
            ->where('status', 1)
            ->orderByRaw('is_rest_of_world asc')
            ->orderBy('sort_order')
            ->get();

        foreach ($zones as $zone) {
            if ($zone->is_rest_of_world) {
                return $zone;
            }

            foreach ($zone->locations as $location) {
                if (strcasecmp($location->country_code, $countryCode) !== 0) {
                    continue;
                }

                if (
                    ! empty($location->postcode)
                    && strcasecmp(trim($location->postcode), trim((string) $postcode)) !== 0
                ) {
                    continue;
                }

                return $zone;
            }
        }

        return null;
    }
}
