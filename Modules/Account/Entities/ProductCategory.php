<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use HasFactory, softDeletes;

    protected $date = 'deleted_at';
    protected $primaryKey = 'category_id';
    protected $keyType = 'string';
    protected $fillable = ['category_id', 'category_name', 'category_type', 'category_description', 'status'];

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\ProductCategoryFactory::new();
    }
}
