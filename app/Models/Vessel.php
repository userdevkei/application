<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vessel extends Model
{
    protected $primaryKey = 'vessel_id';
    protected $keyType = 'string';
    use HasFactory;

    protected $fillable = ['vessel_id', 'company_name', 'vessel_name', 'status', 'created_by'];
}
