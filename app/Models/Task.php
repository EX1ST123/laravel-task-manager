<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'priority',
        'status',
        'category',
    ];

    const CATEGORY_DISPLAY_NAMES = [
        'STUDIES'  => 'Études',
        'WORK'     => 'Travail',
        'PERSONAL' => 'Personnel',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryDisplayNameAttribute(): string
    {
        return self::CATEGORY_DISPLAY_NAMES[$this->category] ?? $this->category;
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'DONE']);
    }

    public function markAsInProgress(): void
    {
        $this->update(['status' => 'IN_PROGRESS']);
    }
}