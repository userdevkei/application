<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use HasFactory, softDeletes;

    protected $primaryKey = 'tax_id';
    protected $keyType = 'string';
    protected $date = ['deleted_at'];
    protected $fillable = ['tax_id', 'tax_name', 'effect', 'status'];

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\TaxFactory::new();
    }
}
