<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $primaryKey = 'client_id';
    protected $keyType = 'string';

    protected $fillable = ['client_id', 'client_name', 'phone', 'address', 'email', 'client_type', 'created_by', 'updated_by'];
}
