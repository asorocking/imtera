<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YandexReview extends Model
{
    protected $fillable = [
        'yandex_setting_id',
        'author_name',
        'author_phone',
        'branch_name',
        'review_date',
        'text',
        'rating',
        'external_id',
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'datetime',
        ];
    }

    public function yandexSetting(): BelongsTo
    {
        return $this->belongsTo(YandexSetting::class);
    }
}
