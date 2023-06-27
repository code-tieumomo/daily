<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'lms_id',
        'username',
        'user_id',
        'firebase_id',
        'full_name',
        'code',
        'phone_number',
        'email',
        'gender',
        'dob',
        'image_url',
        'address',
        'facebook',
        'notes',
        'is_active',
        'created_at',
        'created_by',
        'updated_at',
        'last_updated_by',
    ];
}
