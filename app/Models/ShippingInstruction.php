<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingInstruction extends Model
{
    use HasFactory, softDeletes;

   protected $fillable = ['shipping_id', 'client_id', 'vessel_id', 'destination_id', 'load_type', 'container_size', 'consignee', 'shipping_mark', 'shipping_instructions', 'shipping_number','station_id' , 'status', 'user_id'];

    protected $primaryKey = 'shipping_id';
    protected $keyType = 'string';
//
    protected $date = 'deleted_at';
}
