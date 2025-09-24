<?php

namespace App\Traits;

use App\Services\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;

trait HasAuditLogs
{
    public static function bootHasAuditLogs(): void
    {
        static::created(function (Model $model) {
            app(AuditLogger::class)->log('created', $model, [], $model->getAttributes());
        });

        static::updated(function (Model $model) {
            app(AuditLogger::class)->log('updated', $model, $model->getOriginal(), $model->getAttributes());
        });

        static::deleted(function (Model $model) {
            app(AuditLogger::class)->log('deleted', $model, $model->getOriginal(), []);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                app(AuditLogger::class)->log('restored', $model, [], $model->getAttributes());
            });
        }
    }
}
