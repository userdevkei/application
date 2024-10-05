<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockIn extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['stock_id', 'delivery_id', 'station_id', 'date_received', 'delivery_number', 'warehouse_bay', 'total_weight', 'total_pallets', 'user_id', 'pallet_weight', 'net_weight', 'package_tare', 'transporter_id', 'driver_id', 'registration', 'deleted_at'];

    protected $primaryKey = 'stock_id';

    protected $keyType = 'string';

    protected $date = 'deleted_at';

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_id', 'delivery_id');
    }

    public function internalTransfers()
    {
        return $this->hasMany(Transfers::class, 'stock_id', 'stock_id')
            ->whereColumn('delivery_id', 'delivery_id');
    }

    public function externalTransfers()
    {
        return $this->hasMany(ExternalTransfer::class, 'stock_id', 'stock_id')
            ->whereColumn('delivery_id', 'delivery_id');
    }

    public function blendProcessings()
    {
        return $this->hasMany(BlendTea::class, 'stock_id');
    }

    public function straightLineShippings()
    {
        return $this->hasMany(Shipment::class, 'stock_id');
    }
}
