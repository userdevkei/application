<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['transaction_item_id', 'transaction_id', 'invoice_id', 'amount_settled'];
    protected $primaryKey = 'invoice_payment_id';
    protected $keyType = 'string';
    protected $date = 'deleted_at';
    protected static function newFactory()
    {
        return \Modules\Account\Database\factories\TransactionItemFactory::new();
    }
}
