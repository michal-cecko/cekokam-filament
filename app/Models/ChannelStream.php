<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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

        static::deleted(function (ChannelStream $model) {
            Storage::disk('public')->deleteDirectory($model->stream_directory_path);
            if (! empty($model->logo)) {
                Storage::disk('public')->delete($model->logo);
            }
        });
    }

    public function getStreamDirectoryPathAttribute(): string
    {
        return "streams/{$this->slug}";
    }

    public function getStreamUrlAttribute(): string
    {
        return Storage::disk('public')->url("{$this->stream_directory_path}/stream.m3u8");
    }

    public function getLogoUrlAttribute(): string
    {
        return Storage::disk('public')->url("logos/{$this->slug}.png");
    }
}
