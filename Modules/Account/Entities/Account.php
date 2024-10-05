<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['account_id', 'account_number', 'account_name', 'account_type', 'description'];
    protected $date = 'deleted_at';

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\AccountFactory::new();
    }
}
