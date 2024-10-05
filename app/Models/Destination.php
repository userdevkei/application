<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $primaryKey = 'destination_id';
    protected $keyType = 'string';
    use HasFactory;

    protected $fillable = ['destination_id', 'country_name', 'port_name', 'status', 'created_by'];
}
