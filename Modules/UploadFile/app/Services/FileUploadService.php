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
    public function __construct(protected DiskType $disk, protected string $directory) {}

    public static function make(DiskType $disk, string $directory): FileUploadService
    {
        return new self($disk, $directory);
    }

    public function upload(
        UploadedFile $file,
        ?string $filename = null,
        ?Model $relatedModel = null,
        bool $replaceExists = true): File
    {
        [$filename,$path,$extension] = $this->prepareFile($file, $filename, $replaceExists);

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

    protected function prepareFile(UploadedFile $file, ?string $filename, bool $replaceExists)
    {
        $extension = $file->getClientOriginalExtension();

        if (empty($filename)) {
            $path = $file->store($this->directory, ['disk' => $this->disk->value]);

            return [pathinfo($path, PATHINFO_FILENAME), $path, $extension];
        }

        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $filename = "{$basename}.{$extension}";
        $path = "{$this->directory}/$filename";

        if (Storage::disk($this->disk)->exists($path)) {
            if ($replaceExists) {
                $this->delete($path);
            } else {
                [$filename,$path] = $this->generateUniqueFileName($basename, $extension);
            }
        }

        Storage::disk($this->disk)->putFileAs($this->directory, $file, $filename);

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
        $deleted = Storage::disk($this->disk)->delete($paths);
        if ($deleted) {
            File::where('disk', $this->disk)->whereIn('path', (array) $paths);

            return true;
        }

        return false;
    }

    protected function generateUniqueFileName(string $basename, string $extension)
    {

        $filename = "{$basename}-".str()->random(6).".{$extension}";
        $path = "{$this->directory}/$filename";

        return [$filename, $path];
    }
}
