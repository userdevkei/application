<?php

namespace Modules\Clerk\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeaSamples extends Model
{
    use HasFactory, softDeletes;

    protected $primaryKey = 'sample_id';
    protected $keyType = 'string';
    protected $fillable = ['sample_id', 'delivery_id', 'stock_id', 'sample_weight', 'package_weight', 'sample_palletes', 'status', 'user_id'];

    protected static function newFactory()
    {
        return \Modules\Clerk\Database\factories\TeaSamplesFactory::new();
    }
}
