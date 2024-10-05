<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlendShipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $date = 'deleted_at';
    protected $primaryKey = 'blend_shipment_id';
    protected $keyType = 'string';
    protected $fillable = ['blend_shipment_id', 'blend_id', 'unit_weight', 'weight_variance' ,'blended_packages', 'net_weight', 'package_tare', 'gross_weight', 'station_id'];
}
