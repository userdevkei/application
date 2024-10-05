<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxBrackets extends Model
{
    use HasFactory, softDeletes;

    protected $date = 'deleted_at';
    protected $primaryKey = 'tax_bracket_id';
    protected  $keyType = 'string';
    protected $fillable = ['tax_bracket_id', 'tax_rate', 'tax_id', 'status'];

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\TaxBracketsFactory::new();
    }
}
