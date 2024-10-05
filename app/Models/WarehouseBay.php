<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseBay extends Model
{
    use HasFactory;

    protected $fillable = ['bay_id', 'station_id', 'bay_name', 'created_by'];
}
