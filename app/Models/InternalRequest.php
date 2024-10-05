<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalRequest extends Model
{
    use HasFactory;

    protected $fillable = ['status', 'request_id', 'requester_id', 'requester_user_id', 'requested_user_id', 'requested_id', 'garden', 'invoice', 'package', 'initiation', 'termination', 'grade', 'reference'];
}
