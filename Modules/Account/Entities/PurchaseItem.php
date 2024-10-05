<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = ['purchase_item_id', 'purchase_id', 'tax_id', 'ledger_id', 'quantity', 'unit_price', 'tax_id', 'description', 'status'];

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\PurchaseItemFactory::new();
    }
}
