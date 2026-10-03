<?php

namespace Webkul\ActivityLog\Repositories;

use Webkul\ActivityLog\Contracts\ActivityLog;
use Webkul\ActivityLog\Models\ActivityLog as ActivityLogModel;
use Webkul\Core\Eloquent\Repository;

class ActivityLogRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return ActivityLog::class;
    }

    /**
     * Record an activity log entry.
     *
     * The causer (who did it) defaults to whichever guard - admin or
     * customer - is currently authenticated. Pass an explicit `causer` to
     * override this, which is needed for a failed login: at that point
     * nobody is authenticated yet, so the attempted email has to be passed
     * in by hand.
     */
    public function record(array $data): ActivityLogModel
    {
        $causer = $data['causer'] ?? $this->resolveCurrentCauser();

        return $this->create([
            'causer_type' => $causer['type'] ?? null,
            'causer_id' => $causer['id'] ?? null,
            'causer_name' => $causer['name'] ?? null,
            'event' => $data['event'],
            'subject_type' => $data['subject_type'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'subject_name' => $data['subject_name'] ?? null,
            'description' => $data['description'] ?? null,
            'properties' => $data['properties'] ?? null,
            'ip_address' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 500),
            'created_at' => now(),
        ]);
    }

    /**
     * Resolve the currently authenticated admin or customer as a causer
     * array, or null when the request is unauthenticated (e.g. a guest, or
     * a login attempt that hasn't succeeded yet).
     */
    protected function resolveCurrentCauser(): ?array
    {
        if ($admin = auth()->guard('admin')->user()) {
            return [
                'type' => ActivityLogModel::CAUSER_ADMIN,
                'id' => $admin->id,
                'name' => $admin->name ?? $admin->email,
            ];
        }

        if ($customer = auth()->guard('customer')->user()) {
            return [
                'type' => ActivityLogModel::CAUSER_CUSTOMER,
                'id' => $customer->id,
                'name' => trim(($customer->first_name ?? '').' '.($customer->last_name ?? '')) ?: $customer->email,
            ];
        }

        return null;
    }

    /**
     * Delete several log entries at once (DataGrid mass delete).
     */
    public function massDestroy(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }
}
