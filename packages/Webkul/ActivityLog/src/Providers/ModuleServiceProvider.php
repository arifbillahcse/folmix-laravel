<?php

namespace Webkul\ActivityLog\Providers;

use Webkul\ActivityLog\Models\ActivityLog;
use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models registered with Concord for this module.
     *
     * @var class-string[]
     */
    protected $models = [
        ActivityLog::class,
    ];
}
