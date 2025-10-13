<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the trait - auto-generate UUID on model creating.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the route key name for Laravel route model binding.
     * Routes use uuid instead of id.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
