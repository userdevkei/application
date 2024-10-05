<?php

namespace Modules\Clerk\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ReportRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'request_id';
    protected $keyType = 'string';
    protected $date = 'deleted_at';

    protected $fillable = ['request_id', 'service_number', 'request_type', 'client_id', 'request_number', 'date_from', 'date_to', 'priority', 'status', 'user_id', 'approved_by'];

    public static function serviceId()
    {
        $year = date('y');
        $prefix = 'RR';
        $newID = null;

        // Start a transaction
        DB::beginTransaction();

        try {
            // Get the maximum existing serialized number for the current year
           $lastID = self::withTrashed()
                ->where('service_number', 'like', $prefix . $year . '%')
//                ->whereNull('deleted_at')
                ->orderBy('service_number', 'desc')
                ->lockForUpdate() // Lock the rows to prevent concurrent access
                ->first();

            $lastSerialNumber = $lastID ? intval(substr($lastID->service_number, strlen($prefix . $year))) : 0;

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

    protected static function newFactory()
    {
        return \Modules\Stocks\Database\factories\ReportRequestFactory::new();
    }
}
