<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;
    protected $primaryKey = 'user_id';
    protected $keyType = 'string';
    protected $fillable = ['user_id', 'first_name', 'middle_name', 'surname', 'email_address', 'phone_number', 'id_number', 'gender'];
}
