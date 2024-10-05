<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlendTea extends Model
{
    use HasFactory, softDeletes;

    protected $primaryKey = 'blended_id';
    protected $keyType = 'string';
    protected $date = 'deleted_at';
    protected $fillable = ['blended_id', 'blend_id', 'stock_id', 'delivery_id', 'blended_packages', 'blended_weight', 'status'];
}
