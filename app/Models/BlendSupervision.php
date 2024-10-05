<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlendSupervision extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = ['supervision_id', 'blend_id', 'supervisor_type', 'supervisor_name', 'compiled_by' ];
}
