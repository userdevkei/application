<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlendSheet extends Model
{
    use HasFactory, softDeletes;

    protected $primaryKey = 'blend_id';
    protected $keyType = 'string';

    protected $date = 'delete_at';

    protected $fillable = ['blend_id', 'client_id', 'vessel_id', 'blend_number', 'shipment_order', 'contract', 'destination_id', 'garden', 'grade', 'blend_date', 'package_type', 'loading_type', 'container_size', 'consignee', 'shipping_mark', 'standard_details', 'status', 'shipment_order', 'driver_id', 'agent_id', 'transporter_id', 'registration', 'seal_number', 'escort', 'sweepings', 'container_tare', 'output_packages', 'output_weight', 'packet_tare', 'station_id', 'vessel_name', 'b_dust', 'c_dust', 'fibre', 'user_id'];
}
