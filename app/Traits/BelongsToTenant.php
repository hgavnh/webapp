<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        // Khi tạo mới Data, tự động gán tenant_id của hệ thống
        static::creating(function ($model) {
            $tenantId = session('tenant_id') ?? auth()->user()?->tenant_id;
            if ($tenantId && empty($model->tenant_id)) {
                $model->tenant_id = $tenantId;
            }
        });

        // Khi truy vấn Data, luôn luôn gắn WHERE tenant_id = ...
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = session('tenant_id') ?? auth()->user()?->tenant_id;
            if ($tenantId) {
                $builder->where('tenant_id', $tenantId);
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }
}
