<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrder extends Model
{
    use HasFactory, softDeletes;

    protected $primaryKey = 'delivery_id';
    protected $keyType = 'string';
    protected $date = 'deleted_at';

    protected $fillable = ['delivery_id', 'tea_id', 'garden_id', 'grade_id', 'packet', 'package', 'weight', 'warehouse_id', 'broker_id', 'sale_number', 'invoice_number', 'lot_number', 'sale_date', 'prompt_date', 'sub_warehouse_id', 'locality', 'created_by', 'status', 'client_id', 'order_number', 'delivery_type'];

    public function stockIns() {
        return $this->hasMany(StockIn::class, 'delivery_id', 'delivery_id');
    }
    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function client() {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function garden() {
        return $this->belongsTo(Garden::class, 'garden_id', 'garden_id');
    }

    public function grade() {
        return $this->belongsTo(Grade::class, 'grade_id', 'grade_id');
    }

    public function broker() {
        return $this->belongsTo(Broker::class, 'broker_id', 'broker_id');
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'warehouse_id');
    }

    public function subWarehouse() {
        return $this->belongsTo(SubWarehouse::class, 'sub_warehouse_id', 'sub_warehouse_id');
    }

    public function loadingInstructions() {
        return $this->hasMany(LoadingInstruction::class, 'delivery_id', 'delivery_id');
    }

}
