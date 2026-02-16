<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    protected $fillable = [
        'user_id',
        'file_path',
        'is_flagged',
        'is_moderated',
    ];

    protected function casts(): array
    {
        return [
            'is_flagged' => 'boolean',
            'is_moderated' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the photo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
