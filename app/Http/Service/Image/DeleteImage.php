<?php

declare(strict_types=1);

namespace App\Http\Service\Image;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait DeleteImage
{
    public function deleteImage(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }
        $relativePath = str_replace(asset('storage/') . '/', '', $path);
        if (Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->delete($relativePath);
        }
        return false;
    }

    public function deleteImageForUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }
    
       // Extrae sólo la parte de path 
        $pathWithStorage = parse_url($url, PHP_URL_PATH);

        // Quita el prefijo 
        $relativePath = Str::replaceFirst('/storage/', '', $pathWithStorage);

        // Log::info('Intentando borrar fichero en disco:', [
        //     'relativePath' => $relativePath,
        //     'exists'       => Storage::disk('public')->exists($relativePath),
        // ]);

        // borramos
        if (Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->delete($relativePath);
        }

        return false;
    }
}