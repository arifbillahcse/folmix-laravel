<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Settings\ShippingZonesDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Shipping\Repositories\ShippingZoneRepository;

class ShippingZoneController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected ShippingZoneRepository $shippingZoneRepository) {}

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ShippingZonesDataGrid::class)->process();
        }

        return view('admin::settings.shipping-zones.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        return view('admin::settings.shipping-zones.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $this->sanitizeNumericFields();

        $this->validate(request(), [
            'name'                     => 'required|string',
            'locations'                => 'nullable|array',
            'locations.*.country_code' => 'required|string',
            'methods'                  => 'required|array|min:1',
            'methods.*.type'           => 'required|in:flat_rate,free_shipping',
            'methods.*.title'          => 'required|string',
            'methods.*.rate'           => 'nullable|numeric',
            'methods.*.min_fee'        => 'nullable|numeric',
            'methods.*.calculation_type' => 'nullable|in:per_order,per_unit,percent_of_cart',
        ]);

        $zone = $this->shippingZoneRepository->create(request()->all());

        return new JsonResponse([
            'message'      => trans('admin::app.settings.shipping-zones.index.create-success'),
            'redirect_url' => route('admin.settings.shipping_zones.index'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return View
     */
    public function edit(int $id)
    {
        $zone = $this->shippingZoneRepository->with(['locations', 'methods'])->findOrFail($id);

        return view('admin::settings.shipping-zones.edit', compact('zone'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     */
    public function update($id): JsonResponse
    {
        $this->sanitizeNumericFields();

        $this->validate(request(), [
            'name'                     => 'required|string',
            'locations'                => 'nullable|array',
            'locations.*.country_code' => 'required|string',
            'methods'                  => 'required|array|min:1',
            'methods.*.type'           => 'required|in:flat_rate,free_shipping',
            'methods.*.title'          => 'required|string',
            'methods.*.rate'           => 'nullable|numeric',
            'methods.*.min_fee'        => 'nullable|numeric',
            'methods.*.calculation_type' => 'nullable|in:per_order,per_unit,percent_of_cart',
        ]);

        $this->shippingZoneRepository->update(request()->all(), $id);

        return new JsonResponse([
            'message'      => trans('admin::app.settings.shipping-zones.index.update-success'),
            'redirect_url' => route('admin.settings.shipping_zones.index'),
        ]);
    }

    /**
     * Strip stray characters (%, currency symbols, thousand separators)
     * admins commonly type into rate/fee fields before validating them
     * as plain numbers.
     *
     * @return void
     */
    protected function sanitizeNumericFields()
    {
        $methods = request()->input('methods', []);

        foreach ($methods as $index => $method) {
            foreach (['rate', 'min_fee'] as $field) {
                if (isset($method[$field]) && is_string($method[$field])) {
                    $methods[$index][$field] = trim(str_replace(['%', ',', '€', '$'], '', $method[$field]));
                }
            }
        }

        request()->merge(['methods' => $methods]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->shippingZoneRepository->delete($id);

            return new JsonResponse([
                'message' => trans('admin::app.settings.shipping-zones.index.delete-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.shipping-zones.index.delete-failed'),
            ], 500);
        }
    }
}
