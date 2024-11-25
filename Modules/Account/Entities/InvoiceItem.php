<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{
    use HasFactory, softDeletes;

    protected $primaryKey = 'invoice_item_id';
    protected $keyType = 'string';
    protected $dates = ['deleted_at'];

    protected $fillable = ['invoice_item_id', 'invoice_id', 'ledger_id', 'quantity', 'unit_price', 'tax_id', 'description', 'status', 'updated_at'];

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\InvoiceItemFactory::new();
    }
}
