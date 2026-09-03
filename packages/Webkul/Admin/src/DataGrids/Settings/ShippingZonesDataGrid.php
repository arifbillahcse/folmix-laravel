<?php

namespace Webkul\Admin\DataGrids\Settings;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ShippingZonesDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('shipping_zones')
            ->leftJoin('shipping_zone_locations', 'shipping_zones.id', '=', 'shipping_zone_locations.shipping_zone_id')
            ->leftJoin('shipping_zone_methods', 'shipping_zones.id', '=', 'shipping_zone_methods.shipping_zone_id')
            ->addSelect('shipping_zones.id', 'shipping_zones.name', 'shipping_zones.is_rest_of_world', 'shipping_zones.sort_order', 'shipping_zones.status')
            ->selectRaw('count(distinct shipping_zone_locations.id) as regions_count')
            ->selectRaw('count(distinct shipping_zone_methods.id) as methods_count')
            ->groupBy('shipping_zones.id', 'shipping_zones.name', 'shipping_zones.is_rest_of_world', 'shipping_zones.sort_order', 'shipping_zones.status');
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.settings.shipping-zones.index.datagrid.id'),
            'type' => 'integer',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.settings.shipping-zones.index.datagrid.name'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->is_rest_of_world
                    ? $row->name.' ('.trans('admin::app.settings.shipping-zones.index.datagrid.rest-of-world').')'
                    : $row->name;
            },
        ]);

        $this->addColumn([
            'index' => 'regions_count',
            'label' => trans('admin::app.settings.shipping-zones.index.datagrid.regions'),
            'type' => 'integer',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'methods_count',
            'label' => trans('admin::app.settings.shipping-zones.index.datagrid.methods'),
            'type' => 'integer',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'sort_order',
            'label' => trans('admin::app.settings.shipping-zones.index.datagrid.sort-order'),
            'type' => 'integer',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('admin::app.settings.shipping-zones.index.datagrid.status'),
            'type' => 'boolean',
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('admin::app.settings.shipping-zones.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('admin::app.settings.shipping-zones.index.datagrid.inactive'),
                    'value' => 0,
                ],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                return $row->status
                    ? trans('admin::app.settings.shipping-zones.index.datagrid.active')
                    : trans('admin::app.settings.shipping-zones.index.datagrid.inactive');
            },
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('settings.shipping_zones.edit')) {
            $this->addAction([
                'index' => 'edit',
                'icon' => 'icon-edit',
                'title' => trans('admin::app.settings.shipping-zones.index.datagrid.edit'),
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.settings.shipping_zones.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('settings.shipping_zones.delete')) {
            $this->addAction([
                'index' => 'delete',
                'icon' => 'icon-delete',
                'title' => trans('admin::app.settings.shipping-zones.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.settings.shipping_zones.delete', $row->id);
                },
            ]);
        }
    }
}
