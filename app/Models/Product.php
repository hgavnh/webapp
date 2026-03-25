<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Product extends Model
{
    use BelongsToTenant;
    
    // Nếu bảng cũ không có created_at, updated_at
    public $timestamps = false; 

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
