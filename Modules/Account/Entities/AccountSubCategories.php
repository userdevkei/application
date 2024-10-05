<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountSubCategories extends Model
{
    use HasFactory, softDeletes;

    protected $date = 'deleted_at';
    protected $fillable = ['sub_account_id', 'sub_category_number', 'account_id', 'sub_account_name', 'description', 'status'];

    protected static function newFactory()
    {
        return \Modules\Accounts\Database\factories\AccountSubCategoriesFactory::new();
    }
}
