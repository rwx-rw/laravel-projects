<?php

namespace Modules\UploadFile\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadFileController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function upload(Request $request)
    {
        $file = $request->file('file');

        $validated = $request->validate([
            'file' => ['required', 'file'],
            'chunk_number' => ['required', 'integer', 'min:1'],
            'total_chunks' => ['required', 'integer', 'min:1'],
            'file_identifier' => ['required', 'string'],
            'original_name' => ['required', 'string'],
        ]);

        $chunk = $validated['file'];
        $chunk_number = $validated['chunk_number'];
        $total_chunk = $validated['total_chunks'];
        $file_identifier = $validated['file_identifier'];
        $original_name = $validated['original_name'];

        $tempDisk = 'local';
        $tempDirectory = "chunk/{$file_identifier}";

        Storage::disk($tempDisk)->putFileAs($tempDirectory, $chunk, "{$chunk_number}.part");

        $storedChunks = collect(Storage::disk($tempDisk)->files($tempDirectory))->filter(fn ($path) => str_ends_with($path, '.part'));

        if ($storedChunks->count() < $total_chunk) {
            return response()->json([
                'status' => true,
            ]);
        }

        $fileName = str()->ulid()."-$original_name";
        $final_path = "uploads/$fileName";

        $fullPath = Storage::disk('public')->path($final_path);
        $outputStream = fopen($fullPath, 'ab');

        $storedChunks = $storedChunks->sortBy(fn ($path) => (int) pathinfo($path, PATHINFO_FILENAME))->values();

        foreach ($storedChunks as $path) {
            $chunkStream = Storage::disk($tempDisk)->readStream($path);
            stream_copy_to_stream($chunkStream, $outputStream);
            fclose($chunkStream);
        }

        fclose($outputStream);

        Storage::disk($tempDisk)->deleteDirectory($tempDirectory);

        return response()->json([
            'status' => true,
        ]);
    }
}
