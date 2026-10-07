<?php

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaLibrary
{
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    public function store(UploadedFile $file, User $user, array $meta = []): Media
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = (string) $file->getMimeType();

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true) || ! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw ValidationException::withMessages([
                'file' => 'Only JPEG, PNG, GIF, and WebP images can be uploaded.',
            ]);
        }

        $info = @getimagesize($file->getRealPath() ?: '');
        if ($info === false) {
            throw ValidationException::withMessages([
                'file' => 'The file is not a valid image.',
            ]);
        }

        $path = $file->store('media/'.now()->format('Y/m'), 'public');

        return Media::query()->create([
            'filename' => mb_substr($file->getClientOriginalName(), 0, 255),
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $mime,
            'width' => $info[0] ?? null,
            'height' => $info[1] ?? null,
            'alt_text' => $meta['alt_text'] ?? null,
            'caption' => $meta['caption'] ?? null,
            'credit' => $meta['credit'] ?? null,
            'uploaded_by' => $user->id,
        ]);
    }

    public function delete(Media $media): void
    {
        Storage::disk($media->disk ?: 'public')->delete($media->path);
        $media->delete();
    }
}
