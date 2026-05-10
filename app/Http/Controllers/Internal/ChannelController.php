<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\ChannelStream;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChannelController extends Controller
{
    public function index(): JsonResponse
    {
        $channels = ChannelStream::query()
            ->where('is_active', true)
            ->get(['slug', 'name', 'source', 'logo', 'is_active'])
            ->map(fn (ChannelStream $channel) => [
                'slug' => $channel->slug,
                'name' => $channel->name,
                'source' => $channel->source,
                'logo_url' => $channel->logo_url,
                'is_active' => (bool) $channel->is_active,
            ])
            ->values();

        return response()->json($channels);
    }

    public function logo(string $slug): StreamedResponse
    {
        $channel = ChannelStream::query()->where('slug', $slug)->firstOrFail();

        $disk = Storage::disk('public');

        if (empty($channel->logo) || ! $disk->exists($channel->logo)) {
            abort(404);
        }

        return $disk->response($channel->logo);
    }
}
