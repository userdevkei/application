<?php

namespace Modules\Clerk\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WarehouseLocation extends Model
{
    use HasFactory;

    protected $fillable = ['location_id', 'location_name', 'location_address', 'status'];

    protected static function newFactory()
    {
        return \Modules\Stocks\Database\factories\WarehouseLocationFactory::new();
    }
}
