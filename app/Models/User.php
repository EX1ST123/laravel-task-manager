<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'main_goal',
        'work_rhythm',
        'ai_enabled',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'ai_enabled' => 'boolean',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}