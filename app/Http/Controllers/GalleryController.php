<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Services\ImageModerationService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    /**
     * Display the horizontal gallery feed.
     */
    public function index(): \Illuminate\View\View
    {
        $photos = Photo::with('user')->latest()->get();

        return view('gallery', compact('photos'));
    }

    /**
     * Handle photo upload to local public disk with Sightengine moderation.
     */
    public function store(
        Request $request,
        ImageModerationService $moderation,
        TelegramService $telegram,
    ): \Illuminate\Http\JsonResponse {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $path = $request->file('photo')->store('photos', 'public');

        $photo = Photo::create([
            'user_id' => auth()->id(),
            'file_path' => $path,
            'is_moderated' => true,
        ]);

        $isSafe = $moderation->moderate($photo);

        if (!$isSafe) {
            $photo->update(['is_moderated' => false, 'is_flagged' => true]);

            $telegram->notifyAdmin(
                "⚠️ *Photo Pending Review*\n\nPhoto #{$photo->id} uploaded by " . auth()->user()->name
                . " needs manual review.\nReason: Flagged by moderation or API unavailable."
            );
        }

        return response()->json([
            'success' => true,
            'path' => $path,
        ]);
    }

    /**
     * Delete a photo (admin only).
     */
    public function destroy(Photo $photo): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('delete', $photo);

        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();

        return response()->json(['success' => true]);
    }
}
