<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BibleMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'title',
        'verse',
        'message',
        'author',
        'publish_date',
        'published_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}