<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductFile extends Model
{
    use SoftDeletes;

    protected $table = 'product_files';

    protected $guarded = [
        'id'
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'string',
            'updated_at' => 'string',
            'deleted_at' => 'string'
        ];
    }

    public function product(){
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

}
