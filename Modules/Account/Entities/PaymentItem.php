<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentItem extends Model
{
    use HasFactory, softDeletes;

    protected $primaryKey = 'payment_item_id';
    protected $keyType = 'string';
    protected $date = 'deleted_at';
    protected $fillable = ['payment_item_id', 'payment_id', 'purchase_id', 'amount_settled'];

    protected static function newFactory()
    {
        return \Modules\Account\Database\factories\PaymentItemFactory::new();
    }
}
