<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, softDeletes;

    protected $date = 'deleted_at';

    protected $fillable = ['role_name', 'created_by', 'updated_by'];
}
