<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClearingAgent extends Model
{
    protected $primaryKey = 'agent_id';
    protected $keyType = 'string';

    use HasFactory;

    protected $fillable = ['agent_id', 'agent_name', 'agent_type', 'status', 'created_by' ];
    
}
