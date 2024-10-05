<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $primaryKey = 'warehouse_id';
    protected $keyType = 'string';

    protected $fillable = ['warehouse_id', 'warehouse_name', 'phone', 'address', 'created_by', 'updated_by'];

    public function warehouseSubs()
    {
        return $this->hasMany(SubWarehouse::class, 'warehouse_id', 'warehouse_id');
    }
}
