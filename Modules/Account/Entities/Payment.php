<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use HasFactory, softDeletes;
    protected $primaryKey = 'payment_id';
    protected $keyType = 'string';
    protected $date = 'deleted_at';
    protected $fillable = ['payment_id', 'invoice_number', 'client_id', 'financial_year_id', 'account_id', 'date_received', 'amount_received', 'transaction_code', 'description', 'user_id'];
    public static function newPayInvNumber()
    {
        $year = date('ymd');
        $prefix = 'P';
        $newID = null;

        // Start a transaction
        DB::beginTransaction();

        try {
            // Get the maximum existing serialized number for the current year
            $lastID = self::where('invoice_number', 'like', $prefix . $year . '%')
                ->whereNull('deleted_at')
                ->orderBy('invoice_number', 'desc')
                ->lockForUpdate() // Lock the rows to prevent concurrent access
                ->first();

            $lastSerialNumber = $lastID ? intval(substr($lastID->invoice_number, strlen($prefix . $year))) : 0;

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
    protected static function newFactory()
    {
        return \Modules\Account\Database\factories\PaymentFactory::new();
    }
}
