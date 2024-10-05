<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['chart_id', 'chart_name', 'chart_number', 'sub_account_id', 'description', 'status'];

    protected $date = 'deleted_at';

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\ChartOfAccountFactory::new();
    }
}
