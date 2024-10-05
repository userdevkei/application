<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubWarehouse extends Model
{
    use HasFactory;

    protected $fillable = ['sub_warehouse_id', 'warehouse_id', 'sub_warehouse_name', 'created_by'];
}
