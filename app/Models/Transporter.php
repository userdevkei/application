<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transporter extends Model
{
    use HasFactory;

    protected $fillable = ['transporter_id', 'transporter_name', 'transporter_type', 'description', 'created_by', 'updated_by'];
}
