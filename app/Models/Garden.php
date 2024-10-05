<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Garden extends Model
{
    use HasFactory;

    protected $fillable = ['garden_id', 'garden_name', 'garden_type', 'created_by', 'description', 'updated_at'];
}
