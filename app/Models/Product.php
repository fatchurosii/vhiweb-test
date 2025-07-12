<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use softDeletes;

    protected $guarded = [
        'id'
    ];

    protected $table = 'products';

    protected function casts(): array
    {
        return [
            'created_at' => 'string',
            'updated_at' => 'string',
            'deleted_at' => 'string'
        ];
    }

    public function vendor(){
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function product_files(){
        return $this->hasMany(ProductFile::class, 'product_id', 'id');
    }
}
