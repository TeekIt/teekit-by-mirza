<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

final class ImageServices
{
    public static function uploadImg(object $request, string $img_key_name, string $id)
    {
        $file = $request->file($img_key_name);
        // Creating a unique file name
        $filename = uniqid($id . '_') . "." . $file->getClientOriginalExtension();
        Storage::disk('spaces')->put($filename, File::get($file));
        return (Storage::disk('spaces')->exists($filename)) ? $filename : false;
    }
}
