<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlendMaterial extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['material_id', 'blend_id', 'material_type', 'total', 'condition'];
}
