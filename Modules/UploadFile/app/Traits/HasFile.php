<?php

namespace Modules\UploadFile\Traits;

use Modules\UploadFile\Models\File;

trait HasFile {
    public function files()
    {
        return $this->morphMany(File::class,'fileable');
    }
}
