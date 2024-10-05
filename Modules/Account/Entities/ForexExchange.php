<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForexExchange extends Model
{
    use HasFactory, softDeletes;

    protected $primaryKey = 'forex_id';
    protected $keyType = 'string';
    protected $fillable = ['forex_id', 'currency_id', 'exchange_id', 'exchange_rate', 'date_active'];

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\ForexExchangeFactory::new();
    }
}
