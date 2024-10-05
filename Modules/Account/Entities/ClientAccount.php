<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientAccount extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['client_account_id', 'client_account_number', 'client_account_name', 'currency_id', 'chart_id', 'opening_date', 'closing_date', 'status', 'description', 'type', 'kra_pin', 'client_address'];
    protected $date = 'deleted_at';
    protected $primaryKey = 'client_account_id';
    protected $keyType = 'string';
    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\ClientAccountFactory::new();
    }
}
