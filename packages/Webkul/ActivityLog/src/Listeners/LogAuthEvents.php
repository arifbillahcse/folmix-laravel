<?php

namespace Webkul\ActivityLog\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Webkul\ActivityLog\Models\ActivityLog;
use Webkul\ActivityLog\Repositories\ActivityLogRepository;

class LogAuthEvents
{
    /**
     * Guards this listener records activity for. Any other guard (e.g. one
     * added by a third-party package) is ignored.
     *
     * @var string[]
     */
    protected array $trackedGuards = ['admin', 'customer'];

    public function __construct(protected ActivityLogRepository $activityLogRepository) {}

    public function handleLogin(Login $event): void
    {
        if (! in_array($event->guard, $this->trackedGuards)) {
            return;
        }

        $causer = $this->causerFromUser($event->guard, $event->user);

        $this->activityLogRepository->record([
            'causer' => $causer,
            'event' => ActivityLog::LOGIN,
            'description' => ($causer['name'] ?? 'Someone').' logged in.',
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        if (
            ! in_array($event->guard, $this->trackedGuards)
            || ! $event->user
        ) {
            return;
        }

        $causer = $this->causerFromUser($event->guard, $event->user);

        $this->activityLogRepository->record([
            'causer' => $causer,
            'event' => ActivityLog::LOGOUT,
            'description' => ($causer['name'] ?? 'Someone').' logged out.',
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        if (! in_array($event->guard, $this->trackedGuards)) {
            return;
        }

        $email = $event->credentials['email'] ?? null;

        $this->activityLogRepository->record([
            'causer' => [
                'type' => $event->guard === 'admin' ? ActivityLog::CAUSER_ADMIN : ActivityLog::CAUSER_CUSTOMER,
                'id' => null,
                'name' => $email,
            ],
            'event' => ActivityLog::LOGIN_FAILED,
            'description' => 'Failed login attempt for '.($email ?? 'an unknown email').'.',
        ]);
    }

    /**
     * Build a causer array from an authenticatable user for the given
     * guard, matching the shape ActivityLogRepository::record() expects.
     */
    protected function causerFromUser(string $guard, $user): array
    {
        if ($guard === 'admin') {
            return [
                'type' => ActivityLog::CAUSER_ADMIN,
                'id' => $user->id,
                'name' => $user->name ?? $user->email,
            ];
        }

        return [
            'type' => ActivityLog::CAUSER_CUSTOMER,
            'id' => $user->id,
            'name' => trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->email,
        ];
    }
}
