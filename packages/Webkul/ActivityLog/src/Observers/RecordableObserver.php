<?php

namespace Webkul\ActivityLog\Observers;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Webkul\ActivityLog\Models\ActivityLog;
use Webkul\ActivityLog\Repositories\ActivityLogRepository;

/**
 * A single reusable observer, instantiated once per tracked model
 * (`SomeModel::observe(new RecordableObserver('Some Label', fn ($m) => ...))`)
 * rather than hand-writing a near-identical observer class for every entity
 * this feature watches.
 */
class RecordableObserver
{
    /**
     * Attribute names never surfaced in a change diff, regardless of the
     * model being watched - internal bookkeeping, not something an admin
     * changed.
     *
     * @var string[]
     */
    protected const ALWAYS_IGNORED = [
        'updated_at',
        'created_at',
        'password',
        'remember_token',
        'api_token',
        'two_factor_secret',
        'two_factor_backup_codes',
    ];

    /**
     * @param  string  $subjectLabel  Human label for the model, e.g. "Product".
     * @param  Closure  $nameResolver  Resolves a display name/identifier from the model instance.
     * @param  string[]  $watchedAttributes  If given, an "updated" event is only logged when one of these attributes actually changed (e.g. Order: only status) - keeps noisy background saves out of the log.
     */
    public function __construct(
        protected string $subjectLabel,
        protected Closure $nameResolver,
        protected array $watchedAttributes = [],
    ) {}

    public function created(Model $model): void
    {
        $this->log(ActivityLog::CREATED, $model);
    }

    public function updated(Model $model): void
    {
        $changes = $this->relevantChanges($model);

        if (empty($changes)) {
            return;
        }

        $this->log(ActivityLog::UPDATED, $model, ['changes' => $changes]);
    }

    public function deleted(Model $model): void
    {
        $this->log(ActivityLog::DELETED, $model);
    }

    /**
     * The attributes that changed on this save, filtered down to what's
     * worth recording.
     */
    protected function relevantChanges(Model $model): array
    {
        $changed = array_diff(array_keys($model->getChanges()), self::ALWAYS_IGNORED);

        if ($this->watchedAttributes) {
            $changed = array_intersect($changed, $this->watchedAttributes);
        }

        $changes = [];

        foreach ($changed as $attribute) {
            $changes[$attribute] = [
                'old' => $model->getOriginal($attribute),
                'new' => $model->getAttribute($attribute),
            ];
        }

        return $changes;
    }

    protected function log(string $event, Model $model, array $properties = []): void
    {
        $name = ($this->nameResolver)($model);

        app(ActivityLogRepository::class)->record([
            'event' => $event,
            'subject_type' => $this->subjectLabel,
            'subject_id' => $model->getKey(),
            'subject_name' => $name,
            'description' => $this->describe($event, $name, $properties['changes'] ?? []),
            'properties' => $properties ?: null,
        ]);
    }

    protected function describe(string $event, ?string $name, array $changes = []): string
    {
        $label = $this->subjectLabel;

        $name = $name ?: ('#'.$label);

        if ($event === ActivityLog::UPDATED && $changes) {
            $summary = collect($changes)
                ->map(fn ($change, $attribute) => "{$attribute}: {$this->stringifyValue($change['old'])} \u{2192} {$this->stringifyValue($change['new'])}")
                ->implode(', ');

            return "Updated {$label} \"{$name}\" ({$summary}).";
        }

        return match ($event) {
            ActivityLog::CREATED => "Created {$label} \"{$name}\".",
            ActivityLog::UPDATED => "Updated {$label} \"{$name}\".",
            ActivityLog::DELETED => "Deleted {$label} \"{$name}\".",
            default => "{$event} on {$label} \"{$name}\".",
        };
    }

    protected function stringifyValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null || $value === '') {
            return 'empty';
        }

        return (string) $value;
    }
}
