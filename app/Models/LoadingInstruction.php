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
        $year = date('y'); // Get the last two digits of the current year
        $prefix = 'TCI' . $year;
        $newID = null;

        // Start a transaction to ensure atomicity
        DB::beginTransaction();

        try {
            // Get the maximum numeric part of the loading_number for the current year
            $lastID = self::withTrashed()
                ->selectRaw('MAX(CAST(SUBSTRING(loading_number, LENGTH(?) + 1) AS UNSIGNED)) as max_serial', [$prefix]) // Extract numeric part and convert to unsigned integer
                ->where('loading_number', 'like', $prefix . '%') // Match only those that start with the year prefix
                ->first();

            // Get the last serial number (or start at 1 if no records found)
            $lastSerialNumber = $lastID && $lastID->max_serial ? $lastID->max_serial : 0;
            // return $lastSerialNumber;

            // Increment the serial number by 1
            $serialNumber = $lastSerialNumber + 1;

            // Generate the new serialized ID with leading zeros to ensure 5 digits
            $newID = $prefix . str_pad($serialNumber, 5, '0', STR_PAD_LEFT);

            DB::commit();
        } catch (\Exception $e) {
            // Rollback if an error occurs
            DB::rollback();
            \Log::error('Error generating new TCI:', ['error' => $e->getMessage()]);
            return null;
        }

        return $newID;
    }
}
