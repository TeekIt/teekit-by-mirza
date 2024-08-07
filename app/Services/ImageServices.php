<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

final class ImageServices
{
    public static function createUploadLog (string $diskName, string $filename) {
        if (Storage::disk($diskName)->exists($filename)) {
            info("File is stored in '$diskName' successfully: " . $filename);
        } else {
            info("File not found in '$diskName': " . $filename);
        }
    }

    public static function uploadImg(object $request, string $img_key_name, int $id)
    {
        $file = $request->file($img_key_name);
        /* Creating a unique file name */
        $filename = uniqid($id . '_') . "." . $file->getClientOriginalExtension();
        Storage::disk('spaces')->put($filename, File::get($file));
        self::createUploadLog('spaces', $filename);
        return (Storage::disk('spaces')->exists($filename)) ? $filename : false;
    }

    public static function uploadLivewireImg(object $img, int $id)
    {
        /* Creating a unique file name */
        $filename = uniqid($id . '_') . "." . $img->getClientOriginalExtension();
        Storage::disk('spaces')->put($filename, $img->get());
        self::createUploadLog('spaces', $filename);
        return (Storage::disk('spaces')->exists($filename)) ? $filename : false;
    }
}
