<?php

declare(strict_types=1);

namespace App\Http\Service\Image;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait SaveImage
{
    public function saveImageBase64(?string $base64Image, string $folder = 'products'): ?string
    {
        if(empty($base64Image)){
            return null;
        }
        $image = base64_decode(explode(',',$base64Image)[1]);
        $fileExtension = $this->getFileExtension($base64Image);
        $filename = Str::uuid() . $fileExtension;
        $path = $folder . '/' . $filename;
        Storage::disk('public')->put($path, $image);
        return url('storage/'.$path);
    }
    public function getFileExtension(string $base64Image): string
    {
        $matches = [];
        if(preg_match('/data:image\/(?<type>.+);base64,/',$base64Image, $matches)){
            $mineType = $matches['type'];
            switch($mineType){
                case 'jpg':
                    return '.jpg';
                case 'png':
                    return '.png';
                case 'webp':
                    return '.webp';
                case 'gif':
                    return '.gif';
                default:
                    throw new Exception('Not extension');
            }
        }
        throw new Exception('Invalid format');
    }
}