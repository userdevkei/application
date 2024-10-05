<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['currency_id', 'currency_name', 'currency_symbol', 'priority'];
    protected  $date = 'deleted_at';
    protected $primaryKey = 'currency_id';
    protected $keyType = 'string';

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\CurrencyFactory::new();
    }
}
