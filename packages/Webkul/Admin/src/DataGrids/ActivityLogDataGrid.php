<?php

namespace Webkul\Admin\DataGrids;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\ActivityLog\Models\ActivityLog;
use Webkul\DataGrid\DataGrid;

class ActivityLogDataGrid extends DataGrid
{
    /**
     * Module labels a log entry's subject_type can be. Kept in sync with
     * the RecordableObserver registrations in ActivityLogServiceProvider.
     *
     * @var string[]
     */
    protected const MODULES = [
        'Admin User',
        'Role',
        'Product',
        'Category',
        'Customer',
        'CMS Page',
        'Theme Customization',
        'Order',
        'Setting',
    ];

    /**
     * Build the base query.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('activity_logs')
            ->select(
                'id',
                'causer_type',
                'causer_name',
                'event',
                'subject_type',
                'subject_name',
                'description',
                'ip_address',
                'created_at'
            );

        $this->addFilter('id', 'activity_logs.id');
        $this->addFilter('causer_type', 'activity_logs.causer_type');
        $this->addFilter('causer_name', 'activity_logs.causer_name');
        $this->addFilter('event', 'activity_logs.event');
        $this->addFilter('subject_type', 'activity_logs.subject_type');
        $this->addFilter('description', 'activity_logs.description');
        $this->addFilter('ip_address', 'activity_logs.ip_address');
        $this->addFilter('created_at', 'activity_logs.created_at');

        return $queryBuilder;
    }

    /**
     * Add columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.activity-log.index.datagrid.date-time'),
            'type' => 'datetime',
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
            'closure' => function ($row) {
                return $row->created_at
                    ? Carbon::parse($row->created_at)->format('d M Y, H:i:s')
                    : '';
            },
        ]);

        $this->addColumn([
            'index' => 'causer_name',
            'label' => trans('admin::app.activity-log.index.datagrid.user'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $name = e($row->causer_name ?: trans('admin::app.activity-log.index.datagrid.system'));

                $typeLabel = match ($row->causer_type) {
                    ActivityLog::CAUSER_ADMIN => trans('admin::app.activity-log.index.datagrid.admin'),
                    ActivityLog::CAUSER_CUSTOMER => trans('admin::app.activity-log.index.datagrid.customer'),
                    default => null,
                };

                return $typeLabel
                    ? $name.' <span class="text-xs text-gray-400">('.$typeLabel.')</span>'
                    : $name;
            },
        ]);

        $this->addColumn([
            'index' => 'event',
            'label' => trans('admin::app.activity-log.index.datagrid.event'),
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => trans('admin::app.activity-log.index.datagrid.event-login'), 'value' => ActivityLog::LOGIN],
                ['label' => trans('admin::app.activity-log.index.datagrid.event-logout'), 'value' => ActivityLog::LOGOUT],
                ['label' => trans('admin::app.activity-log.index.datagrid.event-login-failed'), 'value' => ActivityLog::LOGIN_FAILED],
                ['label' => trans('admin::app.activity-log.index.datagrid.event-created'), 'value' => ActivityLog::CREATED],
                ['label' => trans('admin::app.activity-log.index.datagrid.event-updated'), 'value' => ActivityLog::UPDATED],
                ['label' => trans('admin::app.activity-log.index.datagrid.event-deleted'), 'value' => ActivityLog::DELETED],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $label = e($this->eventLabel($row->event));

                return match ($row->event) {
                    ActivityLog::LOGIN, ActivityLog::CREATED => '<p class="label-active">'.$label.'</p>',
                    ActivityLog::LOGIN_FAILED, ActivityLog::DELETED => '<p class="label-canceled">'.$label.'</p>',
                    default => '<p class="label-pending">'.$label.'</p>',
                };
            },
        ]);

        $this->addColumn([
            'index' => 'subject_type',
            'label' => trans('admin::app.activity-log.index.datagrid.module'),
            'type' => 'string',
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => collect(self::MODULES)
                ->map(fn ($label) => ['label' => $label, 'value' => $label])
                ->values()
                ->toArray(),
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'description',
            'label' => trans('admin::app.activity-log.index.datagrid.description'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'ip_address',
            'label' => trans('admin::app.activity-log.index.datagrid.ip-address'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
        ]);

        /**
         * There is no server-side IP-to-country lookup here (this host's
         * outbound internet access can't be relied on), so this column is
         * just a placeholder resolved client-side - see the lookup script
         * in activity-log/index.blade.php, which fills in each span once
         * the page loads using a free public geolocation service.
         */
        $this->addColumn([
            'index' => 'country',
            'label' => trans('admin::app.activity-log.index.datagrid.country'),
            'type' => 'string',
            'closure' => function ($row) {
                if (empty($row->ip_address)) {
                    return '';
                }

                return '<span class="activity-log-country" data-ip="'.e($row->ip_address).'">&mdash;</span>';
            },
        ]);
    }

    /**
     * No row-level actions - the log is a read-only audit trail.
     */
    public function prepareActions(): void {}

    protected function eventLabel(string $event): string
    {
        return match ($event) {
            ActivityLog::LOGIN => trans('admin::app.activity-log.index.datagrid.event-login'),
            ActivityLog::LOGOUT => trans('admin::app.activity-log.index.datagrid.event-logout'),
            ActivityLog::LOGIN_FAILED => trans('admin::app.activity-log.index.datagrid.event-login-failed'),
            ActivityLog::CREATED => trans('admin::app.activity-log.index.datagrid.event-created'),
            ActivityLog::UPDATED => trans('admin::app.activity-log.index.datagrid.event-updated'),
            ActivityLog::DELETED => trans('admin::app.activity-log.index.datagrid.event-deleted'),
            default => $event,
        };
    }
}
