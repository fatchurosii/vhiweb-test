<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use softDeletes;

    protected $table = 'vendors';

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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


}
