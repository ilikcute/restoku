<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Compress, crop to square, and resize an uploaded image
     *
     * @param  int  $size  (Width and Height for square)
     * @return string|false
     */
    public function compressAndStore(UploadedFile $file, string $directory = 'products', int $size = 500, int $quality = 80)
    {
        $filename = Str::random(40).'.jpg';
        $path = $directory.'/'.$filename;

        $imageInfo = getimagesize($file->getRealPath());
        if (! $imageInfo) {
            return $file->store($directory, 'public');
        }

        [$width, $height, $type] = $imageInfo;

        // Create image resource
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($file->getRealPath());
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($file->getRealPath());
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($file->getRealPath());
                break;
            default:
                return $file->store($directory, 'public');
        }

        // Calculate dimensions for square crop (Center Crop)
        $minSide = min($width, $height);
        $cropX = ($width - $minSide) / 2;
        $cropY = ($height - $minSide) / 2;

        // Create destination image (Square)
        $destination = imagecreatetruecolor($size, $size);

        // Handle transparency/background (fill with white since we output JPG)
        $white = imagecolorallocate($destination, 255, 255, 255);
        imagefill($destination, 0, 0, $white);

        // Perform crop and resize
        imagecopyresampled(
            $destination,
            $source,
            0, 0, // Destination offset
            $cropX, $cropY, // Source offset (centered)
            $size, $size, // Destination size
            $minSide, $minSide // Source size (square)
        );

        // Save to temporary path
        $tempPath = tempnam(sys_get_temp_dir(), 'img');
        imagejpeg($destination, $tempPath, $quality);

        // Store to disk
        Storage::disk('public')->put($path, file_get_contents($tempPath));

        // Cleanup
        imagedestroy($source);
        imagedestroy($destination);
        unlink($tempPath);

        return $path;
    }
}
