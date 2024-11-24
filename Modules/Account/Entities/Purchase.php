<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['purchase_id', 'invoice_number', 'voucher_number', 'client_id', 'tax_id', 'date_invoiced', 'due_date', 'financial_year_id', 'customer_message', 'status', 'user_id', 'amount_due', 'kra_number', 'posted', 'type'];
    protected $date = 'deleted_at';
    protected $primaryKey = 'purchase_id';
    protected $keyType = 'string';

    public static function newPINumber()
    {
        $year = date('ym');
        $newID = null;

        // Start a transaction
        DB::beginTransaction();

        try {
            // Get the maximum existing serialized number for the current year
            $lastID = self::withTrashed()->where('voucher_number', 'like', $year . '%')
//                ->whereNull('deleted_at')
                ->orderBy('voucher_number', 'desc')
                ->lockForUpdate() // Lock the rows to prevent concurrent access
                ->first();

            $lastSerialNumber = $lastID ? intval(substr($lastID->voucher_number, strlen($year))) : 0;

            // Increment the serialized number
            $serialNumber = $lastSerialNumber + 1;

            // Generate the full identifier with leading zeros
            $newID = $year . str_pad($serialNumber, 3, '0', STR_PAD_LEFT);

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
        return \Modules\Accounts\Database\factories\PurchaseFactory::new();
    }
}
