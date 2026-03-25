<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Order extends Model
{
    use BelongsToTenant;
    
    public $timestamps = false; // Bảng cũ dùng created_at mặc định MySQL

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}
