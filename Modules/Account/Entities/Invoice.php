<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['invoice_id', 'invoice_number', 'client_id', 'date_invoiced', 'due_date', 'financial_year_id', 'customer_message', 'status', 'user_id', 'amount_due', 'posted', 'kra_number', 'destination_id', 'si_number', 'container_type', 'type'];

    protected $dates = ['deleted_at'];
    protected $primaryKey = 'invoice_id';
    protected $keyType = 'string';


    public static function newInvNumber()
    {
        $year = date('ymd');
        $prefix = 'INV';
        $newID = null;

        // Start a transaction
        DB::beginTransaction();

        try {
            // Get the maximum existing serialized number for the current year
            $lastID = self::withTrashed()->where('invoice_number', 'like', $prefix . $year . '%')
//                ->whereNull('deleted_at')
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

    public static function newCreditNote()
    {
        $year = date('ymd');
        $prefix = 'CRN';
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
        return \Modules\Accounts\Database\factories\InvoiceFactory::new();
    }


}
