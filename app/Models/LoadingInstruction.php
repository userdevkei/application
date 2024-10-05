<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class LoadingInstruction extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['loading_id', 'loading_number', 'transporter_id', 'delivery_id', 'registration', 'driver_id', 'status', 'station_id', 'created_by'];

    protected $dates = 'deleted_at';
    protected $primaryKey = 'loading_id';

    protected $keyType = 'string';

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'driver_id');
    }

    public function transporter()
    {
        return $this->belongsTo(Transporter::class, 'transporter_id', 'transporter_id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id', 'station_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }


    public static function newTCI()
    {
        $year = date('y');
        $prefix = 'TCI';
        $newID = null;

        // Start a transaction
        DB::beginTransaction();

        try {
            // Get the maximum existing serialized number for the current year
           $lastID = self::withTrashed()->where('loading_number', 'like', $prefix . $year . '%')
//                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->orderBy('loading_number', 'desc')
                ->lockForUpdate() // Lock the rows to prevent concurrent access
                ->first();

            $lastSerialNumber = $lastID ? intval(substr($lastID->loading_number, strlen($prefix . $year))) : 0;

            // Increment the serialized number
            $serialNumber = $lastSerialNumber + 1;

            // Generate the full identifier with leading zeros
            $newID = $prefix . $year . str_pad($serialNumber, 3, '0', STR_PAD_LEFT);

            // Commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            DB::rollback();
            // Handle or log the exception
        }

        return $newID;
    }

}
