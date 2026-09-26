<?php

namespace Webkul\ActivityLog\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\ActivityLog\Contracts\ActivityLog as ActivityLogContract;

class ActivityLog extends Model implements ActivityLogContract
{
    /**
     * Login/logout/session events.
     */
    public const LOGIN = 'login';

    public const LOGOUT = 'logout';

    public const LOGIN_FAILED = 'login_failed';

    /**
     * Record change events.
     */
    public const CREATED = 'created';

    public const UPDATED = 'updated';

    public const DELETED = 'deleted';

    /**
     * Causer types.
     */
    public const CAUSER_ADMIN = 'admin';

    public const CAUSER_CUSTOMER = 'customer';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_logs';

    /**
     * Logs are write-once records — nothing ever updates them, so there is
     * no `updated_at` column to maintain.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'causer_type',
        'causer_id',
        'causer_name',
        'event',
        'subject_type',
        'subject_id',
        'subject_name',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];
}
