<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Transfers extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['stock_id', 'transfer_id', 'delivery_id', 'station_id', 'destination', 'driver_id', 'transporter_id', 'registration', 'requested_palettes', 'requested_weight', 'created_by', 'status', 'delivery_number'];

    protected $primaryKey = 'transfer_id';

    protected $keyType = 'string';

    protected $date = 'deleted_at';

    public static function newDelivery()
    {
        $year = date('y');
        $prefix = 'ID';
        $newID = null;

        // Start a transaction
        DB::beginTransaction();

        try {
            // Get the maximum existing serialized number for the current year
            $lastID = self::withTrashed()->where('delivery_number', 'like', $prefix . $year . '%')
//                ->whereNull('deleted_at')
                ->orderBy('delivery_number', 'desc')
                ->lockForUpdate() // Lock the rows to prevent concurrent access
                ->first();

            $lastSerialNumber = $lastID ? intval(substr($lastID->delivery_number, strlen($prefix . $year))) : 0;

            // Increment the serialized number
            $serialNumber = $lastSerialNumber + 1;

            // Generate the full identifier with leading zeros
            $newID = $prefix . $year . str_pad($serialNumber, 4, '0', STR_PAD_LEFT);

            // Commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();
            // Handle or log the exception
        }

        return $newID;
    }


    public function stockIn()
    {
        return $this->belongsTo(StockIn::class, 'stock_id', 'stock_id');
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_id', 'delivery_id');
    }

}
