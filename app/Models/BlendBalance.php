<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlendBalance extends Model
{
    use HasFactory, softDeletes;

    protected $date = 'deleted_at';
    protected $primaryKey = 'blend_balance_id';
    protected $keyType = 'string';

    protected $fillable = ['blend_balance_id', 'blend_id', 'ex_packages', 'unit_weight' ,'net_weight', 'gross_weight', 'station_id', 'status', 'type'];
}
