<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChannelStream extends Model
{
    protected $fillable = [
        'name',
        'source',
        'slug',
        'logo',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ChannelStream $model) {
            $model->slug = Str::slug($model->name);
        });
    }

    public function getStreamUrlAttribute(): string
    {
        return rtrim((string) config('services.stream_server.public_url'), '/')."/streams/{$this->slug}/stream.m3u8";
    }

    public function getLogoUrlAttribute(): string
    {
        return rtrim((string) config('services.stream_server.public_url'), '/')."/logos/{$this->slug}.png";
    }
}
