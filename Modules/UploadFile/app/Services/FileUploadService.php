<?php

namespace Modules\UploadFile\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\UploadFile\Enums\DiskType;
use Modules\UploadFile\Models\File;

class FileUploadService
{
    public function __construct(protected DiskType $disk) {}

    public static function make(DiskType $disk): FileUploadService
    {
        return new self($disk);
    }

    public function upload(
        UploadedFile $file,
        string $directory,
        ?string $filename = null,
        ?Model $relatedModel = null,
        bool $replaceExists = true): File
    {
        [$filename,$path,$extension] = $this->prepareFile($file, $directory, $filename, $replaceExists);

        $builder = $this->resolveModelBuilder($relatedModel);

        return $builder->create([
            'original_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'name' => pathinfo($path, PATHINFO_BASENAME),
            'extension' => $file->getClientOriginalExtension(),
            'disk' => $this->disk,
        ]);
    }

    protected function prepareFile(UploadedFile $file, string $directory, ?string $filename, bool $replaceExists)
    {
        $extension = $file->getClientOriginalExtension();

        if (empty($filename)) {
            $path = $file->store($directory, ['disk' => $this->disk->value]);

            return [pathinfo($path, PATHINFO_FILENAME), $path, $extension];
        }

        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $filename = "{$basename}.{$extension}";
        $path = "{$directory}/$filename";

        if (Storage::disk($this->disk)->exists($path)) {
            if ($replaceExists) {
                $this->delete($path);
            } else {
                [$filename,$path] = $this->generateUniqueFileName($directory, $basename, $extension);
            }
        }

        Storage::disk($this->disk)->putFileAs($directory, $file, $filename);

        return [$filename, $path, $extension];
    }

    protected function resolveModelBuilder(?Model $relatedModel = null)
    {
        if ($relatedModel && ! method_exists($relatedModel, 'files')) {
            throw new Exception('please add has file tarit to '.$relatedModel::class);
        }

        return $relatedModel ? $relatedModel->files() : File::query();
    }

    public function delete(string|array $paths)
    {

        $files = File::where('disk', $this->disk)->whereIn('path', (array) $paths)->each(function (File $file) {
            $file->delete();
        });

        return true;
    }

    protected function generateUniqueFileName(string $directory, string $basename, string $extension)
    {

        $filename = "{$basename}-".str()->random(6).".{$extension}";
        $path = "{$directory}/$filename";

        return [$filename, $path];
    }
}
