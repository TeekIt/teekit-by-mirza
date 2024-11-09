<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

final class ImageServices
{
    public static function createUploadLog(string $diskName, string $fileName)
    {
        if (Storage::disk($diskName)->exists($fileName)) {
            info("File is stored in '$diskName' successfully: " . $fileName);
        } else {
            info("File not found in '$diskName': " . $fileName);
        }
    }
    /**
     * @author Muhammad Abdullah Mirza
     * @return fileName|false 
     */
    public static function uploadImg(object $request, string $imgKeyName, int $id)
    {
        $file = $request->file($imgKeyName);

        /* Creating a unique file name */
        $fileName = uniqid($id . '_') . "." . $file->getClientOriginalExtension();

        Storage::disk('spaces')->put($fileName, File::get($file));

        self::createUploadLog('spaces', $fileName);

        return (Storage::disk('spaces')->exists($fileName)) ? $fileName : false;
    }

    public static function uploadLivewireImg(object $img, int $id)
    {
        /* Creating a unique file name */
        $fileName = uniqid($id . '_') . "." . $img->getClientOriginalExtension();

        Storage::disk('spaces')->put($fileName, $img->get());

        self::createUploadLog('spaces', $fileName);

        return (Storage::disk('spaces')->exists($fileName)) ? $fileName : false;
    }
}
