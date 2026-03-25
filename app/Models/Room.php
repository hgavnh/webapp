<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;

class Room extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }
}
