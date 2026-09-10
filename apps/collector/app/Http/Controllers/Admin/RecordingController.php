<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recording;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecordingController extends Controller
{
    public function show(Recording $recording): StreamedResponse
    {
        $this->authorize('view', $recording->contribution);
        abort_unless(Storage::disk($recording->disk)->exists($recording->path), 404);

        return Storage::disk($recording->disk)->response($recording->path, null, [
            'Content-Type' => $recording->mime_type ?: 'audio/webm',
        ]);
    }
}
