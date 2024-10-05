<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, softDeletes;

    protected $primaryKey = 'shipment_id';
    protected $keyType = 'string';
    protected $date = 'deleted_at';

    protected $fillable = ['shipment_id', 'shipping_id', 'stock_id', 'delivery_id', 'shipped_packages', 'shipped_weight', 'status'];

}
