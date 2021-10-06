<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'is_bot',
        'first_name',
        'last_name',
        'username',
        'language_code',
        'token',
    ];
}
