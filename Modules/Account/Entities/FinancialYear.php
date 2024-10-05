<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialYear extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['financial_year_id' , 'year_starting', 'year_ending', 'status'];
    protected $date = 'deleted_at';
    protected $primaryKey = 'financial_year_id';
    protected $keyType = 'string';

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\FinancialYearFactory::new();
    }
}
