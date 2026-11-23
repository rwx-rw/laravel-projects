<?php

namespace Modules\UploadFile\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Modules\UploadFile\Enums\DiskType;
use Override;

#[Fillable([
    'original_name',
    'path',
    'mime_type',
    'size',
    'name',
    'extension',
    'disk',
    'fileable_type',
    'fileable_id',
])]
class File extends Model
{
    use HasUlids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    #[Override]
    protected function casts()
    {
        return [
            'disk' => DiskType::class
        ];
    }

    #[Override]
    protected static function boot()
    {
        parent::boot();

        static::deleting(function(File $file) : bool{
            if(Storage::disk($file->disk->value)->exists($file->path)){
                $deleted = Storage::disk($file->disk->value)->delete($file->path);
                if(!$deleted){
                    return false;
                }
                return true;
            }
            return true;
        });
    }
}
