<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class FunctionHelper
{
    public static function upload(string $dir, string $format, $image = null)
    {
        try {
            $uploadPath = public_path(trim($dir, '/'));
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }
            if ($image != null) {
                $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
                $image->move($uploadPath, $imageName);
            } else {
                $imageName = 'def.png';
            }
           
            return $imageName;

        } catch (\Exception $e) {
            \Log::error("Image upload failed: " . $e->getMessage());
        }
    }

    public static function update(string $dir, $old_image, string $format, $image = null)
    {
        if ($image == null) {
            return $old_image;
        }
        try {
            $uploadPath = public_path(trim($dir, '/'));
            $oldPath = $uploadPath . '/' . $old_image;
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
            $imageName = self::upload($dir, $format, $image);
            return $imageName;
        } catch (\Exception $e) {
            \Log::error("Image update failed: " . $e->getMessage());
            return $old_image;
        }
    }

    public static function check_and_delete(string $dir, $old_image)
    {

        try {
            $path = public_path(trim($dir, '/') . '/' . $old_image);
            if (File::exists($path)) {
                File::delete($path);
            }
            return true;
        } catch (\Exception $e) {
            \Log::error("Image delete failed: " . $e->getMessage());
            return false;
        }
    }
}